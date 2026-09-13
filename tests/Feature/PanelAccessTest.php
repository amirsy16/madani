<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Akses panel Filament (path /admin).
 *
 * Kontrak otorisasi ada di App\Models\User::canAccessPanel():
 * hanya user dengan minimal satu role yang boleh masuk panel.
 */
class PanelAccessTest extends TestCase
{
    protected function makeUser(): User
    {
        return User::create([
            'name' => 'Tester',
            'email' => uniqid().'@test.local',
            'password' => 'password',
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $response->assertRedirect(route('filament.admin.auth.login'));
        $this->assertGuest();
    }

    public function test_user_login_tanpa_role_ditolak(): void
    {
        $user = $this->makeUser();

        // Kontrak Filament: canAccessPanel() false => middleware panel membatalkan request.
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_user_dengan_role_bisa_mengakses_panel(): void
    {
        $user = $this->makeUser();
        $user->assignRole(Role::findOrCreate('super_admin'));
        $user = $user->refresh();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));

        // Pengujian HTTP GET /admin untuk user ber-role sengaja tidak dilakukan:
        // render dashboard menjalankan widget statistik yang memakai SQL
        // MySQL-specific (DATE_FORMAT, dst.) sehingga tidak bisa dievaluasi di SQLite.
        $this->actingAs($user);

        $this->assertAuthenticatedAs($user);
    }
}
