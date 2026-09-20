<?php

namespace Tests\Feature;

use App\Filament\Resources\DonasiResource\Pages\CreateDonasi;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\JenisDonasi;
use App\Models\MetodePembayaran;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresi temuan blackbox testing manual pada field nominal donasi:
 * - "0" dulu lolos required, menjadi null saat dehydrasi, lalu INSERT
 *   gagal dengan error SQL (column jumlah cannot be null).
 * - "-1" dulu tersimpan karena formatter client-side hanya mengubah
 *   tampilan dan tidak ada rule numeric/min di server.
 */
class DonasiJumlahValidationTest extends TestCase
{
    protected Donatur $donatur;

    protected JenisDonasi $jenis;

    protected MetodePembayaran $metode;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $this->donatur = Donatur::create(['nama' => 'Donatur Uji Nominal']);
        $this->jenis = JenisDonasi::factory()->create(['apakah_barang' => false]);
        $this->metode = MetodePembayaran::create(['nama' => 'TF Uji Nominal']);
    }

    /**
     * Isi form donasi dengan nominal tertentu dan jalankan aksi create.
     */
    protected function submitDenganNominal(string $jumlah): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(CreateDonasi::class)
            ->fillForm([
                'donatur_id' => $this->donatur->id,
                'jenis_donasi_id' => $this->jenis->id,
                'metode_pembayaran_id' => $this->metode->id,
                'nomor_transaksi_unik' => 'TRXUJI-'.uniqid(),
                'jumlah' => $jumlah,
            ])
            ->call('create');
    }

    public function test_nominal_nol_ditolak_dengan_validasi(): void
    {
        $this->submitDenganNominal('0')
            ->assertHasFormErrors(['jumlah']);

        $this->assertDatabaseMissing('donasis', ['donatur_id' => $this->donatur->id]);
    }

    public function test_nominal_negatif_ditolak_dengan_validasi(): void
    {
        $this->submitDenganNominal('-1')
            ->assertHasFormErrors(['jumlah']);

        $this->assertDatabaseMissing('donasis', ['donatur_id' => $this->donatur->id]);
    }

    public function test_nominal_valid_tetap_tersimpan_sebagai_angka(): void
    {
        $this->submitDenganNominal('100000')
            ->assertHasNoFormErrors();

        $donasi = Donasi::where('donatur_id', $this->donatur->id)->first();
        $this->assertNotNull($donasi);
        $this->assertSame(100000.0, (float) $donasi->jumlah);
    }
}
