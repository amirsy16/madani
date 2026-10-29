<?php

namespace App\Services;

use App\Models\Donasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Unified PDF Service - Menggabungkan semua fungsi PDF
 * Menggantikan: InvoicePdfService, HybridPdfService, ExternalPdfService
 */
class PdfService
{
    /**
     * Generate nomor invoice dari nomor transaksi unik donasi
     */
    protected function generateInvoiceNumber(Donasi $donasi): string
    {
        return 'INV-'.$donasi->nomor_transaksi_unik;
    }

    /**
     * Stream PDF invoice
     */
    public function streamInvoicePDF(Donasi $donasi): \Illuminate\Http\Response
    {
        try {
            // Ensure relationships are loaded
            $donasi->load(['donatur', 'jenisDonasi', 'metodePembayaran']);

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($donasi);

            // Prepare data untuk PDF
            $data = $this->prepareInvoiceData($donasi, $invoiceNumber);

            // Generate PDF using donation form template
            $pdf = Pdf::loadView('invoices.donation-form-invoice', $data)
                ->setPaper('a5', 'landscape')
                ->setOptions($this->getDefaultPdfOptions());

            $filename = 'invoice-'.$donasi->nomor_transaksi_unik.'.pdf';

            return $pdf->stream($filename);

        } catch (Exception $e) {
            Log::error('Invoice PDF streaming failed', [
                'donasi_id' => $donasi->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Prepare data untuk invoice PDF
     */
    private function prepareInvoiceData(Donasi $donasi, string $invoiceNumber): array
    {
        return [
            'invoice_number' => $invoiceNumber,
            'donasi' => $donasi,
            'donatur' => $donasi->donatur,
            'jenis_donasi' => $donasi->jenisDonasi?->nama ?? 'Donasi Umum',
            'catatan' => $donasi->keterangan_infak_khusus ?? $donasi->catatan_donatur ?? '',

            // Data untuk kompatibilitas template lama
            'nama_donatur' => $donasi->donatur->nama ?? 'N/A',
            'telepon_donatur' => $donasi->donatur->nomor_hp ?? '',
            'alamat_donatur' => $donasi->donatur->alamat_lengkap ?? '',
            'email_donatur' => $donasi->donatur->email ?? '',
            'jumlah' => $donasi->jumlah,
            'nomor_transaksi' => $donasi->nomor_transaksi_unik,

            // Data template formal: tanggal Indonesia, nilai efektif
            // (barang = perkiraan nilai), deskripsi item, rekening tujuan,
            // penanda tangan, identitas lembaga, dan watermark samar.
            'tanggal_id' => \Carbon\Carbon::parse($donasi->tanggal_donasi)->locale('id')->translatedFormat('d F Y'),
            'nilai_efektif' => (float) (($donasi->jenisDonasi->apakah_barang ?? false)
                ? ($donasi->perkiraan_nilai_barang ?? $donasi->jumlah ?? 0)
                : ($donasi->jumlah ?? $donasi->perkiraan_nilai_barang ?? 0)),
            'jenis_nama' => $donasi->jenisDonasi->nama ?? 'Donasi',
            'deskripsi_item' => trim(($donasi->jenisDonasi->nama ?? 'Donasi').' '.($donasi->deskripsi_barang ?? '')),
            'bank' => $this->rekeningTujuan($donasi),
            'tanda_tangan' => $donasi->dicatatOleh?->name,
            'lembaga' => config('simadi.lembaga'),
            'logo_base64' => $this->gambarBase64(public_path('images/LOGOIM.png')),
            'watermark' => $this->gambarBase64($this->watermarkTersamar()),
        ];
    }

    /**
     * Rekening tujuan transfer: dari metode pembayaran donasi bila
     * menyimpan nomor rekening; selain itu rekening transfer aktif pertama.
     *
     * @return array{nama: string, rekening: string, atas_nama: string}|null
     */
    private function rekeningTujuan(Donasi $donasi): ?array
    {
        $m = $donasi->metodePembayaran;
        if ($m && filled($m->nomor_rekening)) {
            return [
                'nama' => $m->bank_name ?: $m->nama,
                'rekening' => (string) $m->nomor_rekening,
                'atas_nama' => $m->atas_nama_rekening ?: config('simadi.lembaga.nama_tampil'),
            ];
        }

        $fallback = \App\Models\MetodePembayaran::where('tipe', 'transfer_bank')
            ->where('aktif', true)->orderBy('id')->first();

        return $fallback ? [
            'nama' => $fallback->bank_name ?: $fallback->nama,
            'rekening' => (string) $fallback->nomor_rekening,
            'atas_nama' => $fallback->atas_nama_rekening ?: config('simadi.lembaga.nama_tampil'),
        ] : null;
    }

    /**
     * Baca berkas gambar sebagai data URI base64 — dompdf dengan
     * isRemoteEnabled false tetap dapat memuatnya (path Windows biasa
     * gagal dikenali sebagai sumber gambar).
     */
    private function gambarBase64(?string $path): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }

        try {
            return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Salinan logo yang dipudarkan (transparansi ditambah, warna
     * diangkat ke putih) untuk watermark kanan-bawah invoice. Hasil
     * di-cache; null bila GD tidak tersedia.
     */
    private function watermarkTersamar(): ?string
    {
        try {
            if (! function_exists('imagecreatetruecolor')) {
                return null;
            }

            $folder = storage_path('app/invoice');
            if (! is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $path = $folder.'/watermark.png';
            if (is_file($path)) {
                return $path;
            }

            $sumber = public_path('images/LOGOIM.png');
            if (! is_file($sumber)) {
                return null;
            }

            $img = @imagecreatefrompng($sumber);
            if (! $img) {
                return null;
            }

            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
            imagefilter($img, IMG_FILTER_COLORIZE, 0, 0, 0, 115); // samarkan
            imagefilter($img, IMG_FILTER_BRIGHTNESS, 85);         // pudar ke putih
            imagepng($img, $path, 6);
            imagedestroy($img);

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get default PDF options yang aman untuk shared hosting
     */
    private function getDefaultPdfOptions(): array
    {
        return [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,        // Disable remote content untuk avoid GD issues
            'isPhpEnabled' => false,           // Disable PHP untuk security
            'defaultFont' => 'DejaVu Sans',
            'enable_font_subsetting' => false,
            'disable_font_subsetting' => true,
            'dpi' => 96,
            'defaultPaperSize' => 'a4',
            'tempDir' => storage_path('app/temp/'),
            // Avoid GD extension requirements
            'logOutputFile' => storage_path('logs/dompdf.log'),
            'enable_remote' => false,
            'enable_html5_parser' => true,
        ];
    }
}
