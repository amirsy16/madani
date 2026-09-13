<?php

namespace Tests\Feature;

use App\Models\Donasi;
use App\Models\JenisDonasi;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Otorisasi download invoice: /invoice/download/{donasi}
 *
 * Route didefinisikan di routes/web.php dengan middleware
 * ['auth', 'can:view,donasi'] dan menolak donasi yang belum 'verified'.
 */
class InvoiceDownloadAuthorizationTest extends TestCase
{
    protected function makeUser(): User
    {
        return User::create([
            'name' => 'Tester',
            'email' => uniqid().'@test.local',
            'password' => 'password',
        ]);
    }

    /**
     * Catatan: config/filament-shield.php punya `define_via_gate => false`,
     * sehingga Shield TIDAK mendaftarkan Gate::before untuk super_admin.
     * Karena itu permission `view_donasi` diberikan secara eksplisit,
     * sesuai yang dicek DonasiPolicy::view().
     */
    protected function makeSuperAdmin(): User
    {
        $user = $this->makeUser();

        $role = Role::findOrCreate('super_admin');
        $permission = Permission::findOrCreate('view_donasi');

        $role->givePermissionTo($permission);
        $user->assignRole($role);

        return $user->refresh();
    }

    protected function makeDonasi(string $status): Donasi
    {
        $jenis = JenisDonasi::create([
            'nama' => 'Zakat Uji',
            'apakah_barang' => false,
            'aktif' => true,
        ]);

        return Donasi::create([
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => 100000,
            'status_konfirmasi' => $status,
            'tanggal_donasi' => now()->toDateString(),
            'nomor_transaksi_unik' => 'TRX-'.uniqid(),
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $donasi = $this->makeDonasi('pending');

        $response = $this->get("/invoice/download/{$donasi->id}");

        // Middleware auth standar redirect ke named route "login",
        // yang meneruskan ke halaman login Filament (/admin/login).
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_login_tanpa_permission_ditolak_403(): void
    {
        $user = $this->makeUser();
        $donasi = $this->makeDonasi('verified');

        $response = $this->actingAs($user)->get("/invoice/download/{$donasi->id}");

        // Gate 'can:view,donasi' gagal karena user tidak punya permission view_donasi.
        $response->assertStatus(403);
    }

    public function test_donasi_pending_ditolak_403(): void
    {
        $user = $this->makeSuperAdmin();
        $donasi = $this->makeDonasi('pending');

        $response = $this->actingAs($user)->get("/invoice/download/{$donasi->id}");

        // Gate lolos (punya view_donasi), tapi route menolak status pending.
        $response->assertStatus(403);
    }

    public function test_donasi_verified_dengan_permission_dapat_invoice(): void
    {
        $user = $this->makeSuperAdmin();
        $donasi = $this->makeDonasi('verified');

        $response = $this->actingAs($user)->get("/invoice/download/{$donasi->id}");

        // PdfService::streamInvoicePDF() mengembalikan stream PDF (binary).
        $response->assertSuccessful();
    }
}
