<?php

namespace Tests\Feature;

use App\Filament\Pages\LogAktivitas;
use App\Models\Activity;
use App\Models\Donasi;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Log aktivitas input data: pencatatan otomatis via spatie/laravel-activitylog
 * dan halaman LogAktivitas yang hanya boleh diakses super_admin.
 */
class LogAktivitasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    public function test_membuat_donasi_mencatat_aktivitas_dengan_pelaku_dan_waktu(): void
    {
        $donasi = Donasi::factory()->create(['status_konfirmasi' => 'pending']);

        $log = Activity::query()
            ->where('subject_type', Donasi::class)
            ->where('subject_id', $donasi->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('created', $log->event);
        $this->assertSame('donasi', $log->log_name);
        $this->assertSame('menambahkan', $log->description);
        $this->assertSame(auth()->id(), $log->causer_id);
        $this->assertNotNull($log->created_at);
    }

    public function test_halaman_log_merender_baris_dengan_rincian_perubahan(): void
    {
        $donasi = Donasi::factory()->create(['status_konfirmasi' => 'pending']);
        $donasi->update(['status_konfirmasi' => 'verified']);

        $logTerbaru = Activity::query()->where('subject_type', Donasi::class)->where('subject_id', $donasi->id)->latest('id')->first();

        Livewire::test(LogAktivitas::class)
            ->assertSuccessful()
            ->assertSeeText('Total Aktivitas')
            ->assertSeeText('IP Address')
            ->assertTableBulkActionExists('delete')
            ->assertTableActionExists('detail')
            ->assertCanSeeTableRecords([$logTerbaru])
            ->assertSeeText('Memperbarui')
            ->assertSeeText('Donasi — Rp')
            ->assertDontSeeText('kosong →')
            ->assertDontSeeText('donatur_id');

        $aktivitas = Activity::query()
            ->where('subject_type', Donasi::class)
            ->where('subject_id', $donasi->id)
            ->where('event', 'updated')
            ->first();

        Livewire::test(LogAktivitas::class)
            ->mountTableAction('detail', $aktivitas)
            ->assertSuccessful()
            ->assertSeeText('Status Konfirmasi')
            ->assertSeeText('Terverifikasi')
            ->assertDontSeeText('Nomor Transaksi');
    }

    public function test_halaman_log_ditolak_untuk_user_bukan_super_admin(): void
    {
        Role::findOrCreate('Fundraiser');
        $user = User::factory()->create();
        $user->assignRole('Fundraiser');

        $this->actingAs($user)
            ->get(LogAktivitas::getUrl())
            ->assertForbidden();
    }
}
