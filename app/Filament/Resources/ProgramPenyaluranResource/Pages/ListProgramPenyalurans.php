<?php

namespace App\Filament\Resources\ProgramPenyaluranResource\Pages;

use App\Filament\Resources\ProgramPenyaluranResource;
use App\Models\Asnaf;
use App\Models\BidangProgram;
use App\Models\JenisDonasi;
use App\Models\ProgramPenyaluran;
use App\Models\SumberDanaPenyaluran;
use Carbon\Carbon;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProgramPenyalurans extends ListRecords
{
    protected static string $resource = ProgramPenyaluranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->label('Import Penyaluran')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->slideOver()
                ->validateUsing([
                    'tanggal_penyaluran' => 'required',
                    'nama_program' => 'required|string|max:255',
                    'jumlah_dana' => 'required|numeric|min:1',
                    'sumber_dana' => 'required|string',
                    'bidang_program' => 'required|string',
                    'lokasi_penyaluran' => 'required|string',
                    'asnaf' => 'nullable|string',
                    'jenis_donasi' => 'nullable|string',
                    'penerima_manfaat_individu' => 'nullable|string|max:255',
                    'penerima_manfaat_lembaga' => 'nullable|string|max:255',
                    'jumlah_penerima_manfaat' => 'nullable|integer|min:1',
                    'keterangan' => 'nullable|string',
                ])
                ->mutateAfterValidationUsing(function (array $data): array {
                    // Resolve sumber dana
                    $sumberDana = SumberDanaPenyaluran::where('nama_sumber_dana', 'LIKE', '%'.trim($data['sumber_dana']).'%')->first();
                    if (! $sumberDana) {
                        throw new \Exception("Sumber dana '{$data['sumber_dana']}' tidak ditemukan");
                    }
                    $data['sumber_dana_penyaluran_id'] = $sumberDana->id;
                    unset($data['sumber_dana']);

                    // Import tidak melewati guard saldo halaman create — cek
                    // saldo di sini agar baris import tidak membuat saldo negatif.
                    app(\App\Services\DanaService::class)
                        ->assertCukupSaldo($data['sumber_dana_penyaluran_id'], (float) $data['jumlah_dana']);

                    // Resolve bidang program
                    $bidangProgram = BidangProgram::where('nama_bidang', 'LIKE', '%'.trim($data['bidang_program']).'%')->first();
                    if (! $bidangProgram) {
                        throw new \Exception("Bidang program '{$data['bidang_program']}' tidak ditemukan");
                    }
                    $data['bidang_program_id'] = $bidangProgram->id;
                    unset($data['bidang_program']);

                    // Resolve asnaf jika ada
                    if (! empty($data['asnaf'])) {
                        $asnaf = Asnaf::where('nama_asnaf', 'LIKE', '%'.trim($data['asnaf']).'%')->first();
                        $data['asnaf_id'] = $asnaf?->id;
                    }
                    unset($data['asnaf']);

                    // Resolve jenis donasi jika ada
                    if (! empty($data['jenis_donasi'])) {
                        $jenisDonasi = JenisDonasi::where('nama', 'LIKE', '%'.trim($data['jenis_donasi']).'%')
                            ->where('aktif', true)->first();
                        $data['jenis_donasi_id'] = $jenisDonasi?->id;
                    }
                    unset($data['jenis_donasi']);

                    // Parse tanggal
                    $data['tanggal_penyaluran'] = $this->parseTanggal($data['tanggal_penyaluran']);

                    // Generate kode program
                    $data['kode_program_penyaluran'] = $this->generateKodeProgram($data['tanggal_penyaluran']);

                    // Set default values
                    $data['jumlah_penerima_manfaat'] = $data['jumlah_penerima_manfaat'] ?? 1;
                    $data['dicatat_oleh_id'] = Auth::id();

                    return $data;
                })
                ->sampleExcel(
                    sampleData: [
                        [
                            'tanggal_penyaluran' => '15/01/2025',
                            'nama_program' => 'Bantuan Sembako Untuk Fakir Miskin',
                            'jumlah_dana' => 1500000,
                            'sumber_dana' => 'Dana Zakat',
                            'asnaf' => 'Fakir',
                            'bidang_program' => 'Kemanusiaan',
                            'jenis_donasi' => '',
                            'penerima_manfaat_individu' => 'Pak Samsul',
                            'penerima_manfaat_lembaga' => '',
                            'jumlah_penerima_manfaat' => 1,
                            'lokasi_penyaluran' => 'Kelurahan Jelmu, Kec. Jelutung, Kota Jambi',
                            'keterangan' => 'Bantuan sembako bulanan untuk warga kurang mampu',
                        ],
                        [
                            'tanggal_penyaluran' => '20/02/2025',
                            'nama_program' => 'Santunan Pendidikan Anak Yatim',
                            'jumlah_dana' => 5000000,
                            'sumber_dana' => 'Dana Infaq/Sedekah',
                            'asnaf' => '',
                            'bidang_program' => 'Pendidikan',
                            'jenis_donasi' => '',
                            'penerima_manfaat_individu' => '',
                            'penerima_manfaat_lembaga' => 'Yayasan Nurul Harapan Jambi',
                            'jumlah_penerima_manfaat' => 25,
                            'lokasi_penyaluran' => 'Jl. Jenderal Sudirman No. 88, Telanaipura',
                            'keterangan' => 'Program beasiswa untuk 25 anak yatim tingkat SD-SMP',
                        ],
                        [
                            'tanggal_penyaluran' => '10/03/2025',
                            'nama_program' => 'Bantuan Modal Usaha Pedagang Kecil',
                            'jumlah_dana' => 3000000,
                            'sumber_dana' => 'Dana Zakat',
                            'asnaf' => 'Miskin',
                            'bidang_program' => 'Ekonomi',
                            'jenis_donasi' => '',
                            'penerima_manfaat_individu' => '',
                            'penerima_manfaat_lembaga' => 'Kelompok Usaha Mandiri Kenali Besar',
                            'jumlah_penerima_manfaat' => 10,
                            'lokasi_penyaluran' => 'Kelurahan Kenali Besar, Kec. Kota Baru, Jambi',
                            'keterangan' => 'Bantuan modal usaha untuk 10 KK pedagang kecil',
                        ],
                    ],
                    fileName: 'template_import_program_penyaluran.xlsx',
                    sampleButtonLabel: 'Download Template',
                    customiseActionUsing: fn (Action $action) => $action
                        ->color('gray')
                        ->icon('heroicon-o-document-arrow-down'),
                ),
            Actions\CreateAction::make(),
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
        if (is_numeric($tanggal) && ! is_string($tanggal)) {
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

    private function generateKodeProgram(string $tanggal): string
    {
        $date = Carbon::parse($tanggal);
        $prefix = 'PP';
        $yearMonth = $date->format('ym');
        $uniqueId = substr((string) (microtime(true) * 10000), -6);
        $kode = $prefix.$yearMonth.$uniqueId;

        $attempts = 0;
        while (ProgramPenyaluran::where('kode_program_penyaluran', $kode)->exists() && $attempts < 10) {
            $uniqueId = mt_rand(100000, 999999);
            $kode = $prefix.$yearMonth.$uniqueId;
            $attempts++;
        }

        return $kode;
    }
}
