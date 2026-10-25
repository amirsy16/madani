<?php

namespace Tests\Feature;

use App\Filament\Resources\DonasiResource\Pages\CreateDonasi;
use App\Filament\Resources\DonasiResource\Pages\ListDonasis;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\Fundraiser;
use App\Models\JenisDonasi;
use App\Models\MetodePembayaran;
use App\Models\User;
use App\Policies\DonasiPolicy;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Batasan akses fundraiser (AK-01): hanya melihat dan membuka donasi yang
 * ia kumpulkan sendiri. Sekaligus kolom ketertelusuran server-side
 * (AK-02/AK-03) dan anti verifikasi mandiri (AK-07).
 */
class FundraiserScopingTest extends TestCase
{
    protected User $fundraiser;

    protected Fundraiser $profil;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        Role::findOrCreate('Fundraiser');
        // Permission mengikuti RoleSeeder: fundraiser boleh lihat + buat
        // donasi, tetapi TIDAK verifikasi (update_donasi).
        Permission::findOrCreate('view_donasi');
        Permission::findOrCreate('create_donasi');

        $this->fundraiser = User::factory()->create();
        $this->fundraiser->assignRole('Fundraiser');
        $this->fundraiser->givePermissionTo(['view_donasi', 'create_donasi']);

        $this->profil = Fundraiser::create([
            'nama_fundraiser' => 'Fundraiser Uji Scope',
            'user_id' => $this->fundraiser->id,
            'aktif' => true,
        ]);
    }

    public function test_fundraiser_hanya_melihat_donasi_miliknya_di_tabel(): void
    {
        $milik = Donasi::factory()->create(['fundraiser_id' => $this->profil->id, 'status_konfirmasi' => 'pending']);
        $orangLain = Donasi::factory()->create(['fundraiser_id' => null, 'status_konfirmasi' => 'pending']);

        $this->actingAs($this->fundraiser);

        Livewire::test(ListDonasis::class)
            ->assertCanSeeTableRecords([$milik])
            ->assertCanNotSeeTableRecords([$orangLain]);
    }

    public function test_fundraiser_tidak_bisa_membuka_donasi_orang_lain(): void
    {
        $milik = Donasi::factory()->create(['fundraiser_id' => $this->profil->id]);
        $orangLain = Donasi::factory()->create(['fundraiser_id' => null]);

        $policy = new DonasiPolicy;
        $this->assertTrue($policy->view($this->fundraiser, $milik));
        $this->assertFalse($policy->view($this->fundraiser, $orangLain));
    }

    public function test_peran_lengkap_tetap_melihat_semua_donasi(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $milik = Donasi::factory()->create(['fundraiser_id' => $this->profil->id, 'status_konfirmasi' => 'pending']);
        $lain = Donasi::factory()->create(['fundraiser_id' => null, 'status_konfirmasi' => 'pending']);

        $this->actingAs($admin);

        Livewire::test(ListDonasis::class)
            ->assertCanSeeTableRecords([$milik, $lain]);
    }

    public function test_donasi_buatan_fundraiser_selalu_menunggu_verifikasi(): void
    {
        $this->actingAs($this->fundraiser);
        $donatur = Donatur::create(['nama' => 'Donatur Uji Scope']);
        $jenis = JenisDonasi::factory()->create(['apakah_barang' => false]);
        $metode = MetodePembayaran::create(['nama' => 'Tunai Uji Scope']);

        Livewire::test(CreateDonasi::class)
            ->fillForm([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'metode_pembayaran_id' => $metode->id,
                'tanggal_donasi' => '2026-06-15',
                'jumlah' => '500000',
                'status_konfirmasi' => 'verified', // percobaan verifikasi mandiri
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $donasi = Donasi::where('donatur_id', $donatur->id)->firstOrFail();
        $this->assertSame('pending', $donasi->status_konfirmasi);
        $this->assertNull($donasi->dikofirmasi_oleh_user_id);
        $this->assertSame($this->fundraiser->id, $donasi->dicatat_oleh_user_id);
    }

    public function test_nomor_transaksi_dan_pencatat_ditimpa_di_server(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);
        $donatur = Donatur::create(['nama' => 'Donatur Uji Forge']);
        $jenis = JenisDonasi::factory()->create(['apakah_barang' => false]);
        $metode = MetodePembayaran::create(['nama' => 'Tunai Uji Forge']);

        Livewire::test(CreateDonasi::class)
            ->fillForm([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'metode_pembayaran_id' => $metode->id,
                'tanggal_donasi' => '2026-06-16',
                'jumlah' => '300000',
                'nomor_transaksi_unik' => 'TRXFORGED999',
                'dicatat_oleh_user_id' => 99999,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $donasi = Donasi::where('donatur_id', $donatur->id)->firstOrFail();
        $this->assertNotSame('TRXFORGED999', $donasi->nomor_transaksi_unik);
        $this->assertSame($admin->id, $donasi->dicatat_oleh_user_id);
    }
}
