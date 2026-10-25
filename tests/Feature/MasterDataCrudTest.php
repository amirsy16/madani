<?php

namespace Tests\Feature;

use App\Filament\Resources\AsnafResource\Pages\ManageAsnafs;
use App\Models\Asnaf;
use App\Models\User;
use App\Policies\AsnafPolicy;
use Filament\Actions\CreateAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Master data kini punya menu kelola sendiri (WP-D3): super_admin dapat
 * menambah asnaf lewat formulir, sementara peran tanpa izin master data
 * ditolak oleh policy.
 */
class MasterDataCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        Role::findOrCreate('Fundraiser');
        Permission::findOrCreate('view_asnaf');
        Permission::findOrCreate('create_asnaf');
    }

    public function test_super_admin_menambah_asnaf_lewat_formulir(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        Livewire::test(ManageAsnafs::class)
            ->callAction(CreateAction::class, [
                'nama_asnaf' => 'Fakir Miskin Uji',
                'aktif' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('asnafs', ['nama_asnaf' => 'Fakir Miskin Uji', 'aktif' => true]);
    }

    public function test_fundraiser_tanpa_izin_ditolak_policy(): void
    {
        $fundraiser = User::factory()->create();
        $fundraiser->assignRole('Fundraiser');

        $policy = new AsnafPolicy;
        $this->assertFalse($policy->create($fundraiser));
        $this->assertFalse($policy->viewAny($fundraiser));
    }

    public function test_peran_berizin_bisa_membuat_asnaf(): void
    {
        $pengelola = User::factory()->create();
        $pengelola->assignRole(Role::findOrCreate('Admin'));
        $pengelola->givePermissionTo(['view_asnaf', 'create_asnaf']);

        $this->assertTrue((new AsnafPolicy)->create($pengelola));
        $this->assertTrue((new AsnafPolicy)->viewAny($pengelola));
    }
}
