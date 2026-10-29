<?php

namespace Tests\Feature;

use App\Filament\Pages\LaporanPemasukan;
use App\Filament\Resources\DonasiResource\Pages\CreateDonasi;
use App\Filament\Resources\DonasiResource\Pages\EditDonasi;
use App\Filament\Resources\DonaturResource\Pages\CreateDonatur;
use App\Filament\Resources\MetodePembayaranResource\Pages\CreateMetodePembayaran;
use App\Filament\Resources\PenggunaanHakAmilResource\Pages\CreatePenggunaanHakAmil;
use App\Filament\Resources\ProgramPenyaluranResource\Pages\CreateProgramPenyaluran;
use App\Models\BidangProgram;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\JenisDonasi;
use App\Models\JenisPenggunaanHakAmil;
use App\Models\MetodePembayaran;
use App\Models\PenggunaanHakAmil;
use App\Models\SumberDanaPenyaluran;
use App\Models\User;
use App\Services\DanaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Uji fungsional alur bisnis lengkap SIMADI dari ujung ke ujung:
 * master data -> input donasi (bawaan: terverifikasi) -> pemeriksaan ulang
 * lewat edit -> penyaluran dana
 * -> penggunaan hak amil -> laporan (pemasukan & perubahan dana) -> PDF.
 *
 * Angka uji dibuat bulat agar aritmetika keuangan bisa diperiksa eksak:
 * donasi terverifikasi Rp 1.000.000 pada sumber dana dengan hak amil 10%
 * => hak amil Rp 100.000, saldo tersedia Rp 900.000. Penyaluran Rp 500.000
 * menyisakan saldo Rp 400.000. Penggunaan hak amil Rp 60.000 menyisakan
 * Rp 40.000.
 *
 * Aspek yang sudah diuji file lain tidak diulang di sini: akses panel
 * (PanelAccessTest), validasi nominal 0/negatif/<1000 (DonasiJumlahValidationTest),
 * kunci donasi terverifikasi (DonasiVerifiedLockTest), dan render PDF dengan
 * payload sintetis (LaporanPerubahanDanaPdfTest).
 */
class AlurBisnisLengkapTest extends TestCase
{
    private const PERIODE_MULAI = '2026-06-01';

    private const PERIODE_AKHIR = '2026-06-30';

    private const TGL_DONASI = '2026-06-15';

    private const TGL_PENYALURAN = '2026-06-20';

    private const TGL_HAK_AMIL = '2026-06-25';

    /**
     * Rantai data master yang dipakai seluruh skenario.
     *
     * @return array{0: User, 1: Donatur, 2: JenisDonasi, 3: MetodePembayaran, 4: SumberDanaPenyaluran, 5: BidangProgram, 6: JenisPenggunaanHakAmil}
     */
    private function siapkanRantaiBisnis(): array
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $sumberDana = SumberDanaPenyaluran::create([
            'nama_sumber_dana' => 'Dana Zakat Uji Alur',
            'aktif' => true,
            'persentase_hak_amil' => 10,
        ]);
        $jenis = JenisDonasi::factory()->create([
            'nama' => 'Zakat Maal Uji Alur',
            'apakah_barang' => false,
            'membutuhkan_keterangan_tambahan' => false,
            'sumber_dana_penyaluran_id' => $sumberDana->id,
        ]);
        $metode = MetodePembayaran::create(['nama' => 'Tunai Uji Alur']);

        return [
            $user,
            Donatur::factory()->create(['nama' => 'Donatur Uji Alur']),
            $jenis,
            $metode,
            $sumberDana,
            BidangProgram::create(['nama_bidang' => 'Sosial Uji Alur', 'aktif' => true]),
            // Kolom aktif tidak dibuat di SQLite testing (migrasinya di-skip
            // karena memakai Blueprint::check), cukup nama untuk relasi form.
            JenisPenggunaanHakAmil::create(['nama' => 'Operasional Amil Uji Alur']),
        ];
    }

    /**
     * Isi form donasi via halaman CreateDonasi lalu simpan.
     *
     * @return \Livewire\Features\SupportTesting\Testable
     */
    private function isiFormDonasi(Donatur $donatur, JenisDonasi $jenis, MetodePembayaran $metode, array $tambahan = [])
    {
        return Livewire::test(CreateDonasi::class)
            ->fillForm(array_merge([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'metode_pembayaran_id' => $metode->id,
                'tanggal_donasi' => self::TGL_DONASI,
                'jumlah' => '1000000',
            ], $tambahan))
            ->call('create');
    }

    /**
     * Isi form program penyaluran (wizard) lalu simpan.
     *
     * @return \Livewire\Features\SupportTesting\Testable
     */
    private function isiFormPenyaluran(SumberDanaPenyaluran $sumberDana, BidangProgram $bidang, string $jumlahDana)
    {
        // sumber_dana_penyaluran_id diletakkan sebelum jumlah_dana: hook
        // afterStateUpdated sumber dana mereset jumlah_dana saat berubah.
        return Livewire::test(CreateProgramPenyaluran::class)
            ->fillForm([
                'nama_program' => 'Bantuan Fakir Uji Alur',
                'tanggal_penyaluran' => self::TGL_PENYALURAN,
                'lokasi_penyaluran' => 'Kota Jambi',
                'sumber_dana_penyaluran_id' => $sumberDana->id,
                'jumlah_dana' => $jumlahDana,
                'bidang_program_id' => $bidang->id,
                'tipe_penerima' => 'individu',
                'penerima_manfaat_individu' => 'Ahmad Uji',
                'jumlah_penerima_manfaat' => '10',
            ])
            ->call('create');
    }

    public function test_alur_lengkap_dari_master_data_sampai_laporan(): void
    {
        [$user, $donatur, $jenis, $metode, $sumberDana, $bidang, $jenisPenggunaan] = $this->siapkanRantaiBisnis();

        // 1. Master data: donatur dibuat lewat form.
        Livewire::test(CreateDonatur::class)
            ->fillForm(['gender' => 'male', 'nama' => 'Budi Uji Alur'])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('donaturs', ['nama' => 'Budi Uji Alur', 'gender' => 'male']);

        // 2. Input donasi Rp 1.000.000 -> tersimpan terverifikasi (bawaan).
        $this->isiFormDonasi($donatur, $jenis, $metode)->assertHasNoFormErrors();
        $donasi = Donasi::where('donatur_id', $donatur->id)->firstOrFail();
        $this->assertSame('verified', $donasi->status_konfirmasi);

        // 3. Pemeriksaan ulang lewat form edit tetap berjalan pada donasi
        // terverifikasi dan konfirmator tetap tercatat.
        Livewire::test(EditDonasi::class, ['record' => $donasi->getRouteKey()])
            ->fillForm([
                'status_konfirmasi' => 'verified',
                'catatan_konfirmasi' => 'Bukti transfer valid',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $donasi->refresh();
        $this->assertSame('verified', $donasi->status_konfirmasi);
        $this->assertSame($user->id, $donasi->dikofirmasi_oleh_user_id);
        $this->assertNotNull($donasi->dikonfirmasi_pada);

        // 4. Penyaluran Rp 500.000 dari saldo Rp 900.000.
        $this->isiFormPenyaluran($sumberDana, $bidang, '500000')->assertHasNoFormErrors();
        $this->assertDatabaseHas('program_penyalurans', [
            'sumber_dana_penyaluran_id' => $sumberDana->id,
            'jumlah_dana' => 500000,
        ]);

        // 5. Penggunaan hak amil Rp 60.000 dari sisa Rp 100.000.
        Livewire::test(CreatePenggunaanHakAmil::class)
            ->fillForm([
                'tanggal' => self::TGL_HAK_AMIL,
                'jenis_penggunaan_hak_amil_id' => $jenisPenggunaan->id,
                'keterangan' => 'Honor amil',
                'jumlah' => '60000',
                'user_id' => $user->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('penggunaan_hak_amils', ['jumlah' => 60000]);

        // 6. Angka akhir konsisten di seluruh buku besar.
        $danaService = app(DanaService::class);
        $this->assertSame(400000.0, (float) $danaService->getSaldoTersedia($sumberDana->id));
        $this->assertSame(40000.0, (float) $danaService->getSisaHakAmil());

        $laporan = $danaService->getLaporanPerubahanDana(self::PERIODE_MULAI, self::PERIODE_AKHIR);
        $barisSumberDana = collect($laporan)
            ->first(fn ($baris) => is_array($baris) && ($baris['sumber_dana_id'] ?? null) == $sumberDana->id);
        $this->assertNotNull($barisSumberDana);
        $this->assertSame(1000000.0, (float) $barisSumberDana['penerimaan']);
        $this->assertSame(100000.0, (float) $barisSumberDana['bagian_amil']);
        $this->assertSame(500000.0, (float) $barisSumberDana['penyaluran']);

        // 7. Jejak audit: input & verifikasi donasi serta penyaluran tercatat.
        $this->assertTrue(
            Activity::where('subject_type', Donasi::class)->where('subject_id', $donasi->id)->where('event', 'created')->exists()
        );
        $this->assertTrue(
            Activity::where('subject_type', Donasi::class)->where('subject_id', $donasi->id)->where('event', 'updated')->exists()
        );
        $this->assertTrue(
            Activity::where('subject_type', \App\Models\ProgramPenyaluran::class)->where('event', 'created')->exists()
        );
    }

    public function test_master_data_metode_pembayaran_dibuat_via_form(): void
    {
        $this->siapkanRantaiBisnis();

        Livewire::test(CreateMetodePembayaran::class)
            ->fillForm([
                'nama' => 'QRIS Uji Alur',
                'kode' => 'QRS',
                'tipe' => 'digital',
                'aktif' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('metode_pembayarans', ['nama' => 'QRIS Uji Alur', 'tipe' => 'digital', 'aktif' => true]);
    }

    public function test_donasi_uang_tersimpan_dengan_nomor_transaksi_otomatis(): void
    {
        [, $donatur, $jenis, $metode] = $this->siapkanRantaiBisnis();

        $this->isiFormDonasi($donatur, $jenis, $metode)->assertHasNoFormErrors();

        $donasi = Donasi::where('donatur_id', $donatur->id)->firstOrFail();
        $this->assertSame('verified', $donasi->status_konfirmasi);
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $donasi->nomor_transaksi_unik);
        $this->assertSame(1000000.0, (float) $donasi->jumlah);
        $this->assertSame(Auth::id(), $donasi->dicatat_oleh_user_id);
    }

    public function test_donasi_barang_menyimpan_perkiraan_nilai_dan_masuk_saldo_setelah_verifikasi(): void
    {
        [, $donatur, , $metode, $sumberDana] = $this->siapkanRantaiBisnis();
        $jenisBarang = JenisDonasi::factory()->create([
            'nama' => 'Donasi Barang Uji Alur',
            'apakah_barang' => true,
            'membutuhkan_keterangan_tambahan' => false,
            'sumber_dana_penyaluran_id' => $sumberDana->id,
        ]);

        Livewire::test(CreateDonasi::class)
            ->fillForm([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenisBarang->id,
                'metode_pembayaran_id' => $metode->id,
                'tanggal_donasi' => self::TGL_DONASI,
                'deskripsi_barang' => 'Beras 5 kg',
                'perkiraan_nilai_barang' => '500000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $donasi = Donasi::where('jenis_donasi_id', $jenisBarang->id)->firstOrFail();
        $this->assertSame('verified', $donasi->status_konfirmasi);
        // Field jumlah disembunyikan untuk donasi barang: tidak di-dehydrate,
        // tersimpan sebagai default 0 dan nilai ekonomi ada di perkiraan_nilai.
        $this->assertSame(0.0, (float) $donasi->jumlah);
        $this->assertSame(500000.0, (float) $donasi->perkiraan_nilai_barang);

        // Nilai barang ikut menjadi penerimaan: 500rb x (100% - 10%) = 450rb.
        $donasi->update(['status_konfirmasi' => 'verified']);
        $this->assertSame(450000.0, (float) app(DanaService::class)->getSaldoTersedia($sumberDana->id));
    }

    public function test_donasi_atas_nama_hamba_allah_tersimpan(): void
    {
        [, $donatur, $jenis, $metode] = $this->siapkanRantaiBisnis();

        $this->isiFormDonasi($donatur, $jenis, $metode, ['atas_nama_hamba_allah' => true])
            ->assertHasNoFormErrors();

        // Mode anonim sengaja mengosongkan donatur: donasi tercatat sebagai
        // "Hamba Allah" tanpa identitas (hook afterStateUpdated toggle).
        $this->assertDatabaseHas('donasis', [
            'donatur_id' => null,
            'atas_nama_hamba_allah' => true,
            'jumlah' => 1000000,
        ]);
    }

    public function test_nomor_transaksi_baru_melanjutkan_nomor_urut_warisan(): void
    {
        // Basis data produksi memakai nomor urut warisan LAZ (001451, 001452,
        // ...). Donasi baru harus melanjutkan urutan itu, bukan format TRX.
        [, $donatur, $jenis, $metode] = $this->siapkanRantaiBisnis();
        Donasi::factory()->create([
            'nomor_transaksi_unik' => '001453',
            'donatur_id' => $donatur->id,
            'jenis_donasi_id' => $jenis->id,
        ]);

        $this->isiFormDonasi($donatur, $jenis, $metode)->assertHasNoFormErrors();

        $terakhir = Donasi::query()->orderByDesc('id')->first();
        $this->assertSame('001454', $terakhir->nomor_transaksi_unik);
    }

    public function test_nomor_transaksi_dari_form_diabaikan_server_membuat_baru(): void
    {
        // Nilai nomor pada formulir hanyalah pratinjau; server selalu
        // menimpa dengan nomor urut baru sehingga dua formulir yang dibuka
        // bersamaan tidak saling memblokir.
        [, $donatur, $jenis, $metode] = $this->siapkanRantaiBisnis();
        Donasi::factory()->create([
            'nomor_transaksi_unik' => 'TRXDUPALUR',
            'donatur_id' => $donatur->id,
            'jenis_donasi_id' => $jenis->id,
        ]);

        Livewire::test(CreateDonasi::class)
            ->fillForm([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'metode_pembayaran_id' => $metode->id,
                'tanggal_donasi' => self::TGL_DONASI,
                'jumlah' => '250000',
                'nomor_transaksi_unik' => 'TRXDUPALUR',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $donasiBaru = Donasi::where('donatur_id', $donatur->id)->orderByDesc('id')->first();
        $this->assertNotSame('TRXDUPALUR', $donasiBaru->nomor_transaksi_unik);
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $donasiBaru->nomor_transaksi_unik);
        $this->assertSame(1, Donasi::where('nomor_transaksi_unik', 'TRXDUPALUR')->count());
    }

    public function test_penolakan_donasi_dengan_catatan(): void
    {
        [, $donatur, $jenis, $metode] = $this->siapkanRantaiBisnis();
        $this->isiFormDonasi($donatur, $jenis, $metode)->assertHasNoFormErrors();
        $donasi = Donasi::where('donatur_id', $donatur->id)->firstOrFail();

        Livewire::test(EditDonasi::class, ['record' => $donasi->getRouteKey()])
            ->fillForm([
                'status_konfirmasi' => 'rejected',
                'catatan_konfirmasi' => 'Bukti tidak jelas',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $donasi->refresh();
        $this->assertSame('rejected', $donasi->status_konfirmasi);
        $this->assertSame('Bukti tidak jelas', $donasi->catatan_konfirmasi);

        // Donasi ditolak tidak boleh dihitung sebagai penerimaan.
        $laporan = app(DanaService::class)->getLaporanPerubahanDana(self::PERIODE_MULAI, self::PERIODE_AKHIR);
        $barisSumberDana = collect($laporan)
            ->first(fn ($baris) => is_array($baris) && ($baris['sumber_dana_id'] ?? null) == $jenis->sumber_dana_penyaluran_id);
        $this->assertSame(0.0, (float) $barisSumberDana['penerimaan']);
    }

    public function test_donasi_terverifikasi_muncul_di_laporan_pemasukan(): void
    {
        [, $donatur, $jenis, $metode] = $this->siapkanRantaiBisnis();
        Donasi::factory()->create([
            'donatur_id' => $donatur->id,
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => 1000000,
            'tanggal_donasi' => self::TGL_DONASI,
            'status_konfirmasi' => 'verified',
        ]);

        Livewire::test(LaporanPemasukan::class)
            ->fillForm([
                'startDate' => self::PERIODE_MULAI,
                'endDate' => self::PERIODE_AKHIR,
            ])
            ->assertSuccessful()
            ->assertSet('grandTotalPemasukan', 1000000)
            ->assertSet('totalTransaksi', 1)
            ->assertSet('totalDonatur', 1)
            ->assertSet('totalZakat', 1000000);
    }

    public function test_penyaluran_melebihi_saldo_ditolak(): void
    {
        [, $donatur, $jenis, $metode, $sumberDana, $bidang] = $this->siapkanRantaiBisnis();
        Donasi::factory()->create([
            'donatur_id' => $donatur->id,
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => 1000000,
            'tanggal_donasi' => self::TGL_DONASI,
            'status_konfirmasi' => 'verified',
        ]);

        // Saldo = 1.000.000 - hak amil 100.000 = 900.000; 900.001 harus ditolak.
        $this->isiFormPenyaluran($sumberDana, $bidang, '900001')
            ->assertHasFormErrors(['jumlah_dana']);

        $this->assertDatabaseMissing('program_penyalurans', ['sumber_dana_penyaluran_id' => $sumberDana->id]);
    }

    public function test_penggunaan_hak_amil_melebihi_sisa_ditolak(): void
    {
        [$user, $donatur, $jenis, $metode, , , $jenisPenggunaan] = $this->siapkanRantaiBisnis();
        Donasi::factory()->create([
            'donatur_id' => $donatur->id,
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => 1000000,
            'tanggal_donasi' => self::TGL_DONASI,
            'status_konfirmasi' => 'verified',
        ]);

        // Sisa hak amil = 10% x 1.000.000 = 100.000; 100.001 harus ditolak.
        Livewire::test(CreatePenggunaanHakAmil::class)
            ->fillForm([
                'tanggal' => self::TGL_HAK_AMIL,
                'jenis_penggunaan_hak_amil_id' => $jenisPenggunaan->id,
                'keterangan' => 'Boros',
                'jumlah' => '100001',
                'user_id' => $user->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['jumlah']);

        $this->assertDatabaseMissing('penggunaan_hak_amils', ['keterangan' => 'Boros']);
    }

    public function test_pdf_laporan_perubahan_dana_dari_data_hasil_alur(): void
    {
        [$user, $donatur, $jenis, $metode, $sumberDana, $bidang] = $this->siapkanRantaiBisnis();
        Donasi::factory()->create([
            'donatur_id' => $donatur->id,
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => 1000000,
            'tanggal_donasi' => self::TGL_DONASI,
            'status_konfirmasi' => 'verified',
        ]);
        $this->isiFormPenyaluran($sumberDana, $bidang, '500000')->assertHasNoFormErrors();
        PenggunaanHakAmil::create([
            'tanggal' => self::TGL_HAK_AMIL,
            'jenis_penggunaan_hak_amil_id' => JenisPenggunaanHakAmil::firstOrFail()->id,
            'keterangan' => 'Operasional',
            'jumlah' => 60000,
            'user_id' => $user->id,
        ]);

        $laporan = app(DanaService::class)->getLaporanPerubahanDana(self::PERIODE_MULAI, self::PERIODE_AKHIR);

        // Payload meniru LaporanPerubahanDana::downloadPDF() dengan data asli.
        $pdf = Pdf::loadView('laporan.pdf.perubahan-dana-clean', [
            'reportData' => $laporan,
            'summaryData' => $laporan['summary'] ?? [],
            'totalSisaSaldo' => 400000,
            'penerimaanHakAmilDetail' => [],
            'totalPenerimaanHakAmil' => 100000,
            'penggunaanHakAmilDetail' => [],
            'totalPenggunaanHakAmil' => 60000,
            'surplusDefisitHakAmil' => 40000,
            'detailPengeluaranAsnaf' => [],
            'detailPengeluaranBidangProgram' => [],
            'detailJenisPenggunaanAmil' => [],
            'detailDanaNonHalal' => [],
            'startDate' => self::PERIODE_MULAI,
            'endDate' => self::PERIODE_AKHIR,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->output();

        $this->assertStringStartsWith('%PDF', $pdf);
        $html = view('laporan.pdf.perubahan-dana-clean', [
            'reportData' => $laporan,
            'summaryData' => $laporan['summary'] ?? [],
            'totalSisaSaldo' => 400000,
            'penerimaanHakAmilDetail' => [],
            'totalPenerimaanHakAmil' => 100000,
            'penggunaanHakAmilDetail' => [],
            'totalPenggunaanHakAmil' => 60000,
            'surplusDefisitHakAmil' => 40000,
            'detailPengeluaranAsnaf' => [],
            'detailPengeluaranBidangProgram' => [],
            'detailJenisPenggunaanAmil' => [],
            'detailDanaNonHalal' => [],
            'startDate' => self::PERIODE_MULAI,
            'endDate' => self::PERIODE_AKHIR,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->render();
        $this->assertStringContainsString('1.000.000', $html);
        $this->assertStringContainsString('500.000', $html);
    }
}
