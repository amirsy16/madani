<?php

namespace Tests\Feature;

use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\JenisDonasi;
use App\Models\MetodePembayaran;
use App\Models\User;
use App\Services\NotifikasiWa;
use App\Support\ImporTransaksional;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Notifikasi WA via Fonnte (keputusan ops): donasi baru, login panel
 * (berhasil & gagal), penahanan pengiriman saat impor massal, dan no-op
 * bila token/target .env belum diisi.
 *
 * Catatan: Http::fake() sengaja dipanggil eksplisit per test (bukan di
 * setUp) — stub array yang dipanggil ulang digabung dengan stub lama dan
 * stub PERTAMA yang menang, sehingga stub pengganti tidak berlaku.
 */
class NotifikasiWaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.fonnte.token' => 'token-uji',
            'services.fonnte.target' => '628123456789',
        ]);
    }

    public function test_donasi_baru_mengirim_wa_ke_penerima(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);

        $jenis = JenisDonasi::factory()->create(['apakah_barang' => false]);
        $donatur = Donatur::create(['nama' => 'Donatur Uji Notif']);
        $metode = MetodePembayaran::create(['nama' => 'TF Uji Notif']);
        $pencatat = User::factory()->create(['name' => 'Amir Uji']);

        Donasi::factory()->create([
            'donatur_id' => $donatur->id,
            'jenis_donasi_id' => $jenis->id,
            'metode_pembayaran_id' => $metode->id,
            'dicatat_oleh_user_id' => $pencatat->id,
            'jumlah' => 500000,
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request['message'], '*Donasi Baru*')
                && str_contains($request['message'], 'Rp 500.000')
                && str_contains($request['message'], 'Donatur Uji Notif')
                && str_contains($request['message'], 'dicatat oleh Amir Uji')
                && $request['target'] === '628123456789';
        });
    }

    public function test_impor_massal_tidak_mengirim_wa(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);

        $jenis = JenisDonasi::factory()->create(['apakah_barang' => false]);
        $donatur = Donatur::create(['nama' => 'Donatur Impor Notif']);

        $baris = collect([
            collect([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'jumlah' => 25000,
                'tanggal_donasi' => '2026-10-01',
                'nomor_transaksi_unik' => 'TRXNOTIF1',
                'status_konfirmasi' => 'pending',
            ]),
            collect([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'jumlah' => 75000,
                'tanggal_donasi' => '2026-10-02',
                'nomor_transaksi_unik' => 'TRXNOTIF2',
                'status_konfirmasi' => 'pending',
            ]),
        ]);

        ImporTransaksional::proses(Donasi::class, $baris);

        $this->assertDatabaseHas('donasis', ['nomor_transaksi_unik' => 'TRXNOTIF1']);
        $this->assertDatabaseHas('donasis', ['nomor_transaksi_unik' => 'TRXNOTIF2']);
        Http::assertNothingSent();
    }

    public function test_login_berhasil_dan_gagal_mengirim_wa(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        event(new Login('web', $user, false));

        Http::assertSent(fn ($request) => str_contains($request['message'], '*Login Panel*')
            && str_contains($request['message'], $user->name)
            && str_contains($request['message'], 'super_admin'));

        event(new Failed('web', $user, ['email' => 'coba-masuk@contoh.id']));

        Http::assertSent(fn ($request) => str_contains($request['message'], '*Login Gagal*')
            && str_contains($request['message'], 'coba-masuk@contoh.id'));
    }

    public function test_kirim_diabaikan_bila_konfigurasi_kosong(): void
    {
        config(['services.fonnte.token' => null]);

        $hasil = NotifikasiWa::kirim('pesan uji');

        $this->assertFalse($hasil);
        Http::assertNothingSent();
    }

    public function test_respons_http_200_tapi_status_fonnte_gagal_dianggap_gagal(): void
    {
        // Fonnte membalas HTTP 200 untuk error seperti token tak dikenal —
        // kesuksesan harus dibaca dari body, bukan kode HTTP.
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'unknown token']),
        ]);

        $hasil = NotifikasiWa::kirim('pesan uji');

        $this->assertFalse($hasil);
    }
}
