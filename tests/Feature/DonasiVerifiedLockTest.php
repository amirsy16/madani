<?php

namespace Tests\Feature;

use App\Filament\Resources\DonasiResource\Pages\EditDonasi;
use App\Filament\Resources\DonasiResource\Pages\ListDonasis;
use App\Models\Donasi;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Donasi terverifikasi adalah keadaan final: field finansial & status
 * terkunci, dan penghapusan (per-baris maupun massal) ditolak.
 */
class DonasiVerifiedLockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    protected function buatDonasi(string $status): Donasi
    {
        return Donasi::factory()->create(['status_konfirmasi' => $status]);
    }

    public function test_field_finansial_terkunci_pada_donasi_verified(): void
    {
        $donasi = $this->buatDonasi('verified');

        Livewire::test(EditDonasi::class, ['record' => $donasi->getRouteKey()])
            ->assertFormFieldIsDisabled('jumlah')
            ->assertFormFieldIsDisabled('status_konfirmasi');
    }

    public function test_field_tetap_aktif_pada_donasi_pending(): void
    {
        $donasi = $this->buatDonasi('pending');

        Livewire::test(EditDonasi::class, ['record' => $donasi->getRouteKey()])
            ->assertFormFieldIsEnabled('jumlah')
            ->assertFormFieldIsEnabled('status_konfirmasi');
    }

    public function test_aksi_hapus_tersembunyikan_pada_donasi_verified(): void
    {
        $donasi = $this->buatDonasi('verified');

        Livewire::test(EditDonasi::class, ['record' => $donasi->getRouteKey()])
            ->assertActionHidden('delete');

        $this->assertDatabaseHas('donasis', ['id' => $donasi->id]);
    }

    public function test_hapus_massal_menolak_donasi_verified(): void
    {
        $verified = $this->buatDonasi('verified');
        $pending = $this->buatDonasi('pending');

        Livewire::test(ListDonasis::class)
            ->callTableBulkAction('delete', [$verified->id, $pending->id]);

        $this->assertDatabaseHas('donasis', ['id' => $verified->id]);
        $this->assertDatabaseHas('donasis', ['id' => $pending->id]);
    }
}
