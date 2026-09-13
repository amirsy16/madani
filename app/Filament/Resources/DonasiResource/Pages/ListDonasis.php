<?php

namespace App\Filament\Resources\DonasiResource\Pages;

use App\Filament\Resources\DonasiResource;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\JenisDonasi;
use App\Models\MetodePembayaran;
use App\Models\Fundraiser;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ListDonasis extends ListRecords
{
    protected static string $resource = DonasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->label('Import Donasi')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->slideOver()
                ->validateUsing([
                    'kode_donatur' => 'required|string',
                    'tanggal_donasi' => 'required',
                    'jenis_donasi' => 'required|string',
                    'jumlah' => 'nullable|numeric|min:0',
                    'metode_pembayaran' => 'nullable|string',
                    'fundraiser' => 'nullable|string',
                    'keterangan_infak_khusus' => 'nullable|string',
                    'deskripsi_barang' => 'nullable|string',
                    'perkiraan_nilai_barang' => 'nullable|numeric|min:0',
                    'catatan_donatur' => 'nullable|string',
                    'atas_nama_hamba_allah' => 'nullable',
                ])
                ->mutateAfterValidationUsing(function (array $data): array {
                    // Resolve donatur (wajib)
                    $donatur = Donatur::where('kode_donatur', trim($data['kode_donatur']))->first();
                    if (!$donatur) {
                        throw new \Exception("Donatur dengan kode '{$data['kode_donatur']}' tidak ditemukan");
                    }
                    $data['donatur_id'] = $donatur->id;
                    unset($data['kode_donatur']);

                    // Resolve jenis donasi (wajib)
                    $jenisDonasi = JenisDonasi::where('nama', 'LIKE', '%' . trim($data['jenis_donasi']) . '%')
                        ->where('aktif', true)->first();
                    if (!$jenisDonasi) {
                        throw new \Exception("Jenis donasi '{$data['jenis_donasi']}' tidak ditemukan");
                    }
                    $data['jenis_donasi_id'] = $jenisDonasi->id;
                    unset($data['jenis_donasi']);

                    // Resolve metode pembayaran (optional)
                    if (!empty($data['metode_pembayaran'])) {
                        $metode = MetodePembayaran::where('nama', 'LIKE', '%' . trim($data['metode_pembayaran']) . '%')
                            ->where('aktif', true)->first();
                        $data['metode_pembayaran_id'] = $metode?->id;
                    }
                    unset($data['metode_pembayaran']);

                    // Resolve fundraiser (optional)
                    if (!empty($data['fundraiser'])) {
                        $fundraiser = Fundraiser::where('nama_fundraiser', 'LIKE', '%' . trim($data['fundraiser']) . '%')
                            ->where('aktif', true)->first();
                        $data['fundraiser_id'] = $fundraiser?->id;
                    }
                    unset($data['fundraiser']);

                    // Parse tanggal
                    $data['tanggal_donasi'] = $this->parseTanggal($data['tanggal_donasi']);

                    // Parse atas_nama_hamba_allah
                    $data['atas_nama_hamba_allah'] = $this->parseBoolean($data['atas_nama_hamba_allah'] ?? false);

                    // Set default jumlah to 0 if empty (untuk donasi barang)
                    $data['jumlah'] = !empty($data['jumlah']) ? (float) $data['jumlah'] : 0;
                    
                    // Set default perkiraan_nilai_barang to 0 if empty
                    $data['perkiraan_nilai_barang'] = !empty($data['perkiraan_nilai_barang']) ? (float) $data['perkiraan_nilai_barang'] : null;

                    // Generate nomor transaksi
                    $data['nomor_transaksi_unik'] = $this->generateNomorTransaksi();

                    // Set defaults
                    $data['status_konfirmasi'] = 'pending';
                    $data['dicatat_oleh_user_id'] = Auth::id();

                    return $data;
                })
                ->sampleExcel(
                    sampleData: [
                        [
                            'kode_donatur' => 'DN250001',
                            'tanggal_donasi' => '15/01/2025',
                            'jenis_donasi' => 'Infaq Tidak Terikat',
                            'jumlah' => 500000,
                            'metode_pembayaran' => 'TF BSI 1630',
                            'fundraiser' => 'ZULI',
                            'keterangan_infak_khusus' => '',
                            'deskripsi_barang' => '',
                            'perkiraan_nilai_barang' => '',
                            'catatan_donatur' => 'Semoga bermanfaat untuk umat',
                            'atas_nama_hamba_allah' => 'false',
                        ],
                        [
                            'kode_donatur' => 'DN250002',
                            'tanggal_donasi' => '20/02/2025',
                            'jenis_donasi' => 'Zakat Maal',
                            'jumlah' => 2500000,
                            'metode_pembayaran' => 'Tunai',
                            'fundraiser' => '',
                            'keterangan_infak_khusus' => '',
                            'deskripsi_barang' => '',
                            'perkiraan_nilai_barang' => '',
                            'catatan_donatur' => '',
                            'atas_nama_hamba_allah' => 'true',
                        ],
                        [
                            'kode_donatur' => 'DN250001',
                            'tanggal_donasi' => '10/03/2025',
                            'jenis_donasi' => 'Donasi Logistik/Barang',
                            'jumlah' => 0,
                            'metode_pembayaran' => 'Donasi Logistik/Barang',
                            'fundraiser' => '',
                            'keterangan_infak_khusus' => '',
                            'deskripsi_barang' => 'Beras 25 kg merk Ramos',
                            'perkiraan_nilai_barang' => 250000,
                            'catatan_donatur' => 'Donasi untuk masjid Al-Falah',
                            'atas_nama_hamba_allah' => 'false',
                        ],
                    ],
                    fileName: 'template_import_donasi.xlsx',
                    sampleButtonLabel: 'Download Template',
                    customiseActionUsing: fn(Action $action) => $action
                        ->color('gray')
                        ->icon('heroicon-o-document-arrow-down'),
                ),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DonasiResource\Widgets\DonasiOverviewStats::class,
        ];
    }

    private function parseTanggal($tanggal): string
    {
        // Log input untuk debugging

        if (empty($tanggal)) {
            return now()->toDateString();
        }

        // 1. Handle DateTimeInterface (DateTime, DateTimeImmutable, Carbon)
        if ($tanggal instanceof \DateTimeInterface) {
            $result = Carbon::instance($tanggal)->toDateString();
            return $result;
        }

        // 2. Handle Excel serial date number
        if (is_numeric($tanggal) && !is_string($tanggal)) {
            $numValue = (float) $tanggal;
            if ($numValue > 1 && $numValue < 100000) {
                try {
                    // Gunakan PhpSpreadsheet untuk konversi yang akurat
                    $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numValue);
                    $result = Carbon::instance($dateTime)->toDateString();
                    return $result;
                } catch (\Exception $e) {
                    \Log::warning('parseTanggal: Excel serial failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // 3. Handle string - bersihkan dan parse
        $tanggalStr = trim((string) $tanggal);
        
        // Hapus karakter non-printable/invisible
        $tanggalStr = preg_replace('/[^\d\/\-\.\s]/', '', $tanggalStr);
        $tanggalStr = trim($tanggalStr);
        

        // Pattern: YYYY-MM-DD (ISO format - paling aman)
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $tanggalStr, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                $result = sprintf('%04d-%02d-%02d', $year, $month, $day);
                return $result;
            }
        }

        // Pattern: DD/MM/YYYY atau D/M/YYYY (Indonesia)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $tanggalStr, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                $result = sprintf('%04d-%02d-%02d', $year, $month, $day);
                return $result;
            }
        }

        // Pattern: DD-MM-YYYY atau D-M-YYYY
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $tanggalStr, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                $result = sprintf('%04d-%02d-%02d', $year, $month, $day);
                return $result;
            }
        }

        // Pattern: DD.MM.YYYY (format Eropa)
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $tanggalStr, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                $result = sprintf('%04d-%02d-%02d', $year, $month, $day);
                return $result;
            }
        }

        // Last resort: coba Carbon::parse()
        try {
            $date = Carbon::parse($tanggalStr);
            if ($date->year >= 2020 && $date->year <= 2035) {
                $result = $date->toDateString();
                return $result;
            }
        } catch (\Exception $e) {
            \Log::warning('parseTanggal: Carbon::parse failed', ['error' => $e->getMessage()]);
        }

        // Fallback ke hari ini
        $result = now()->toDateString();
        \Log::warning('parseTanggal: FALLBACK to today', ['original' => $tanggal, 'result' => $result]);
        return $result;
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return (bool) $value;
        $value = strtolower(trim((string) $value));
        return in_array($value, ['true', 'yes', 'ya', '1', 'y']);
    }

    private function generateNomorTransaksi(): string
    {
        $prefix = 'TRX';
        $date = now()->format('ymd');
        $uniqueId = substr((string)(microtime(true) * 10000), -6);
        $nomor = $prefix . $date . $uniqueId;

        $attempts = 0;
        while (Donasi::where('nomor_transaksi_unik', $nomor)->exists() && $attempts < 10) {
            $uniqueId = mt_rand(100000, 999999);
            $nomor = $prefix . $date . $uniqueId;
            $attempts++;
        }

        return $nomor;
    }
}

