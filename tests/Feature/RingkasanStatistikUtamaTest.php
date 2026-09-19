<?php

namespace Tests\Feature;

use App\Filament\Widgets\RingkasanStatistikUtama;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Modal "Lihat Selengkapnya" pada widget Ringkasan Statistik Utama.
 *
 * Kontrak interaksi: kartu statistik (Dana Zakat, Infaq Terikat, Donasi
 * Barang) membuka modal breakdown; drill-down daftar transaksi terbuka
 * DI ATAS breakdown sehingga menutup detail kembali ke breakdown.
 */
class RingkasanStatistikUtamaTest extends TestCase
{
    public function test_modal_breakdown_terbuka_per_kartu_dan_bisa_ditutup(): void
    {
        Livewire::test(RingkasanStatistikUtama::class)
            ->assertSet('showBreakdownModal', null)
            ->call('openZakatModal')
            ->assertSet('showBreakdownModal', 'zakat')
            ->call('openBarangModal')
            ->assertSet('showBreakdownModal', 'barang')
            ->call('openInfaqTerikatModal')
            ->assertSet('showBreakdownModal', 'infaq_terikat')
            ->call('closeBreakdownModal')
            ->assertSet('showBreakdownModal', null);
    }

    public function test_menutup_detail_donasi_kembali_ke_modal_breakdown(): void
    {
        Livewire::test(RingkasanStatistikUtama::class)
            ->call('openZakatModal')
            ->call('showZakatDonasi', 'Zakat Maal')
            ->assertSet('showDetailDonasiModal', true)
            ->assertSet('selectedJenisZakat', 'Zakat Maal')
            ->assertSet('detailDonasiType', 'zakat')
            ->call('closeDetailDonasiModal')
            ->assertSet('showDetailDonasiModal', false)
            ->assertSet('selectedJenisZakat', null)
            ->assertSet('showBreakdownModal', 'zakat');
    }

    public function test_drill_down_infaq_terikat_menyimpan_kategori_terpilih(): void
    {
        Livewire::test(RingkasanStatistikUtama::class)
            ->call('openInfaqTerikatModal')
            ->call('showInfaqTerikatDonasi', 'Pembangunan Masjid')
            ->assertSet('showDetailDonasiModal', true)
            ->assertSet('selectedKategori', 'Pembangunan Masjid')
            ->assertSet('detailDonasiType', 'infaq');
    }

    public function test_close_breakdown_modal_membersihkan_detail_donasi_juga(): void
    {
        Livewire::test(RingkasanStatistikUtama::class)
            ->call('openZakatModal')
            ->call('showZakatDonasi', 'Zakat Fitrah')
            ->call('closeBreakdownModal')
            ->assertSet('showBreakdownModal', null)
            ->assertSet('showDetailDonasiModal', false)
            ->assertSet('selectedJenisZakat', null);
    }
}
