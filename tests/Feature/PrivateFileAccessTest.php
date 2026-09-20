<?php

namespace Tests\Feature;

use App\Models\BidangProgram;
use App\Models\Donasi;
use App\Models\ProgramPenyaluran;
use App\Models\SumberDanaPenyaluran;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Otorisasi per-record pada /private-file: file bukti pembayaran/penyaluran
 * hanya boleh diakses oleh user yang berhak melihat record pemiliknya —
 * bukan sekadar user yang sudah login.
 */
class PrivateFileAccessTest extends TestCase
{
    protected const FILE_DONASI = 'bukti-pembayaran/uji-pembayaran.png';

    protected const FILE_PENYALURAN = 'bukti-penyaluran/uji-penyaluran.png';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::disk('private')->put(self::FILE_DONASI, 'isi-bukti');
        Storage::disk('private')->put(self::FILE_PENYALURAN, 'isi-bukti');
    }

    public function test_super_admin_dapat_mengakses_bukti_donasi(): void
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        Donasi::factory()->create(['bukti_pembayaran' => self::FILE_DONASI]);

        $this->get('/private-file/'.self::FILE_DONASI)->assertOk();
    }

    public function test_user_tanpa_izin_view_donasi_ditolak(): void
    {
        Role::findOrCreate('tanpa_izin');
        $user = User::factory()->create();
        $user->assignRole('tanpa_izin');
        $this->actingAs($user);

        Donasi::factory()->create(['bukti_pembayaran' => self::FILE_DONASI]);

        // 404, bukan 403 — jangan bocorkan keberadaan file/record.
        $this->get('/private-file/'.self::FILE_DONASI)->assertNotFound();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        Donasi::factory()->create(['bukti_pembayaran' => self::FILE_DONASI]);

        $this->get('/private-file/'.self::FILE_DONASI)->assertRedirect();
    }

    public function test_path_traversal_ditolak(): void
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $this->get('/private-file/bukti-pembayaran/..%2F..%2F.env')->assertNotFound();
        $this->get('/private-file/prefix-palsu/'.self::FILE_DONASI)->assertNotFound();
    }

    public function test_bukti_penyaluran_dilindungi_per_record(): void
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $sumber = SumberDanaPenyaluran::create(['nama_sumber_dana' => 'Dana Uji', 'aktif' => true]);
        $bidang = BidangProgram::create(['nama_bidang' => 'Bidang Uji']);
        ProgramPenyaluran::create([
            'kode_program_penyaluran' => 'PPUJI001',
            'nama_program' => 'Program Uji',
            'tanggal_penyaluran' => now()->toDateString(),
            'jumlah_dana' => 1000,
            'sumber_dana_penyaluran_id' => $sumber->id,
            'bidang_program_id' => $bidang->id,
            'bukti_penyaluran' => self::FILE_PENYALURAN,
            'dicatat_oleh_id' => $user->id,
        ]);

        $this->get('/private-file/'.self::FILE_PENYALURAN)->assertOk();
    }
}
