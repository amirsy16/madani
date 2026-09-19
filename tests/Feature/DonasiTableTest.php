<?php

namespace Tests\Feature;

use App\Filament\Resources\DonasiResource\Pages\ListDonasis;
use App\Models\Donasi;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tabel DonasiResource: kolom status_konfirmasi bukan lagi badge teks
 * ("Terverifikasi") dan action "Generate Invoice PDF" dihapus.
 */
class DonasiTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    public function test_badge_status_dan_invoice_dihapus_dari_tabel(): void
    {
        Donasi::factory()->create(['status_konfirmasi' => 'verified']);

        Livewire::test(ListDonasis::class)
            ->assertTableColumnExists('status_konfirmasi')
            ->assertDontSee('Generate Invoice PDF');
    }
}
