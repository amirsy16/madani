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
        ];
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
