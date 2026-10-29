<?php

namespace Tests\Feature;

use App\Filament\Resources\DonasiResource\Pages\CreateDonasi;
use App\Filament\Resources\DonasiResource\Pages\EditDonasi;
use App\Filament\Resources\DonasiResource\Pages\ListDonasis;
use App\Models\Donasi;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Kontrak baru permintaan pengelola: donasi terverifikasi dapat diedit
 * bebas dan dapat dihapus (per baris maupun massal), serta bawaan formulir
 * pembuatan donasi adalah Terverifikasi.
 */
class DonasiVerifiedEditableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    private function donasiTerverifikasi(): Donasi
    {
        $donasi = Donasi::factory()->create(['status_konfirmasi' => 'verified']);
        $donasi->update([
            'dikofirmasi_oleh_user_id' => 1,
            'dikonfirmasi_pada' => now(),
        ]);

        return $donasi->refresh();
    }

    public function test_halaman_edit_donasi_terverifikasi_dapat_dibuka(): void
    {
        // Regresi: import HtmlString salah namespace membuat halaman edit
        // donasi terverifikasi error 500 di produksi (30 Oktober 2026).
        $donasi = $this->donasiTerverifikasi();

        Livewire::test(EditDonasi::class, ['record' => $donasi->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_field_tetap_aktif_pada_donasi_verified(): void
    {
        $donasi = $this->donasiTerverifikasi();

        Livewire::test(EditDonasi::class, ['record' => $donasi->getRouteKey()])
            ->assertFormFieldIsEnabled('jumlah')
            ->assertFormFieldIsEnabled('tanggal_donasi')
            ->assertFormFieldIsEnabled('status_konfirmasi');
    }

    public function test_donasi_terverifikasi_bisa_dihapus_per_baris(): void
    {
        $donasi = $this->donasiTerverifikasi();

        Livewire::test(ListDonasis::class)
            ->assertTableActionVisible('delete', $donasi);

        $donasi->delete();

        $this->assertDatabaseMissing('donasis', ['id' => $donasi->id]);
    }

    public function test_hapus_massal_menghapus_donasi_terverifikasi(): void
    {
        $verified = $this->donasiTerverifikasi();
        $pending = Donasi::factory()->create(['status_konfirmasi' => 'pending']);

        Livewire::test(ListDonasis::class)
            ->callTableBulkAction('delete', [$verified->id, $pending->id]);

        $this->assertDatabaseMissing('donasis', ['id' => $verified->id]);
        $this->assertDatabaseMissing('donasis', ['id' => $pending->id]);
    }

    public function test_bawaan_formulir_donasi_adalah_terverifikasi(): void
    {
        $donatur = \App\Models\Donatur::create(['nama' => 'Donatur Bawaan Verified']);
        $jenis = \App\Models\JenisDonasi::factory()->create(['apakah_barang' => false]);
        $metode = \App\Models\MetodePembayaran::create(['nama' => 'Tunai Bawaan Verified']);

        Livewire::test(CreateDonasi::class)
            ->fillForm([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'metode_pembayaran_id' => $metode->id,
                'tanggal_donasi' => '2026-10-30',
                'jumlah' => '250000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $donasi = Donasi::where('donatur_id', $donatur->id)->firstOrFail();
        $this->assertSame('verified', $donasi->status_konfirmasi);
        $this->assertSame(auth()->id(), $donasi->dikofirmasi_oleh_user_id);
        $this->assertNotNull($donasi->dikonfirmasi_pada);
    }
}
