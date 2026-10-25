<?php

namespace Tests\Feature;

use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class LaporanPerubahanDanaPdfTest extends TestCase
{
    /**
     * Payload meniru data yang dikirim LaporanPerubahanDana::downloadPDF().
     *
     * @return array<string, mixed>
     */
    private function dataLaporan(bool $lengkap): array
    {
        if (! $lengkap) {
            return [
                'reportData' => [],
                'penerimaanHakAmilDetail' => [],
                'totalPenerimaanHakAmil' => 0,
                'penggunaanHakAmilDetail' => [],
                'totalPenggunaanHakAmil' => 0,
                'surplusDefisitHakAmil' => 0,
                'totalSisaSaldo' => 0,
                'startDate' => '2025-01-01',
                'endDate' => '2025-12-31',
                'logoBase64' => null,
            ];
        }

        return [
            'reportData' => [
                1 => [
                    'rincian_penerimaan' => ['Zakat Mal' => 900000, 'Zakat Profesi' => 600000],
                    'penerimaan' => 1500000,
                    'bagian_amil' => 187500,
                    'rincian_penyaluran_asnaf' => ['Fakir' => 400000, 'Miskin' => 0],
                    'rincian_penyaluran' => ['Pendidikan' => 500000],
                    'penyaluran' => 900000,
                    'surplus_defisit' => 412500,
                    'saldo_awal' => 0,
                    'saldo_akhir' => 412500,
                ],
                2 => [
                    'rincian_penerimaan' => ['Infaq Umum' => 500000],
                    'penerimaan' => 500000,
                    'bagian_amil' => 25000,
                    'rincian_penyaluran' => ['Sosial' => 700000],
                    'penyaluran' => 700000,
                    'surplus_defisit' => -275000,
                    'saldo_awal' => 0,
                    'saldo_akhir' => 275000,
                ],
                7 => [
                    'rincian_penerimaan' => ['Bunga Bank' => 100000],
                    'penerimaan' => 100000,
                    'bagian_amil' => 0,
                    'rincian_penyaluran' => [],
                    'penyaluran' => 0,
                    'surplus_defisit' => 100000,
                    'saldo_awal' => 0,
                    'saldo_akhir' => 100000,
                ],
            ],
            'penerimaanHakAmilDetail' => ['Zakat' => 187500],
            'totalPenerimaanHakAmil' => 187500.0,
            'penggunaanHakAmilDetail' => ['Belanja Pegawai' => 100000],
            'totalPenggunaanHakAmil' => 100000.0,
            'surplusDefisitHakAmil' => 87500.0,
            'totalSisaSaldo' => 787500.0,
            'startDate' => '2025-01-01',
            'endDate' => '2025-12-31',
            'logoBase64' => null,
        ];
    }

    public function test_merender_pdf_dengan_data_lengkap(): void
    {
        $pdf = Pdf::loadView('laporan.pdf.perubahan-dana-clean', $this->dataLaporan(lengkap: true))->output();

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_merender_tanpa_rincian_tanpa_angka_sintetis_dan_tanpa_nama_ttd_hardcode(): void
    {
        $html = view('laporan.pdf.perubahan-dana-clean', $this->dataLaporan(lengkap: false))->render();

        $this->assertStringContainsString('Tidak ada rincian transaksi pada periode ini', $html);
        $this->assertStringNotContainsString('Hibah', $html);
        $this->assertStringNotContainsString('FUJI LESTARI', $html);
    }

    public function test_merender_angka_dengan_format_indonesia(): void
    {
        $html = view('laporan.pdf.perubahan-dana-clean', $this->dataLaporan(lengkap: true))->render();

        $this->assertStringContainsString('1.500.000', $html);
        $this->assertStringContainsString('(275.000)', $html);
    }
}
