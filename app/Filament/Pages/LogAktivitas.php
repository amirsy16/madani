<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogAktivitas extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administrator';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $title = 'Log Aktivitas Input Data';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.log-aktivitas';

    /**
     * Peta kolom relasi *_id → [model, kolom nama] agar detail perubahan
     * menampilkan nama (mis. "donatur = Ibu Eka"), bukan ID mentah.
     *
     * @var array<string, array{0: class-string<\Illuminate\Database\Eloquent\Model>, 1: string}>
     */
    private const RELASI_NAMA = [
        'donatur_id' => [\App\Models\Donatur::class, 'nama'],
        'fundraiser_id' => [\App\Models\Fundraiser::class, 'nama_fundraiser'],
        'jenis_donasi_id' => [\App\Models\JenisDonasi::class, 'nama'],
        'metode_pembayaran_id' => [\App\Models\MetodePembayaran::class, 'nama'],
        'sumber_dana_penyaluran_id' => [\App\Models\SumberDanaPenyaluran::class, 'nama_sumber_dana'],
        'asnaf_id' => [\App\Models\Asnaf::class, 'nama_asnaf'],
        'bidang_program_id' => [\App\Models\BidangProgram::class, 'nama_bidang'],
        'jenis_penggunaan_hak_amil_id' => [\App\Models\JenisPenggunaanHakAmil::class, 'nama'],
        'kategori_dana_non_halal_id' => [\App\Models\KategoriDanaNonHalal::class, 'nama'],
        'pekerjaan_id' => [\App\Models\Pekerjaan::class, 'nama'],
        'user_id' => [\App\Models\User::class, 'name'],
        'dicatat_oleh_user_id' => [\App\Models\User::class, 'name'],
        'dikofirmasi_oleh_user_id' => [\App\Models\User::class, 'name'],
        'dicatat_oleh_id' => [\App\Models\User::class, 'name'],
        'province_id' => [\App\Models\Province::class, 'name'],
        'city_id' => [\App\Models\Regency::class, 'name'],
        'district_id' => [\App\Models\District::class, 'name'],
        'village_id' => [\App\Models\Village::class, 'name'],
    ];

    /** @var array<string, ?string> cache nama relasi per render */
    private array $cacheNamaRelasi = [];

    /**
     * Kolom yang dinilai penting untuk dibaca pengguna — kolom lain
     * (nomor transaksi, path bukti, kolom auto/ketertelusuran) disembunyikan
     * agar modal detail ringkas.
     *
     * @var list<string>
     */
    private const KOLOM_PENTING = [
        // Donasi
        'jumlah', 'perkiraan_nilai_barang', 'deskripsi_barang', 'donatur_id',
        'jenis_donasi_id', 'metode_pembayaran_id', 'fundraiser_id', 'tanggal_donasi',
        'status_konfirmasi', 'catatan_konfirmasi',
        // Donatur
        'nama', 'nomor_hp', 'email', 'gender', 'alamat_detail', 'pekerjaan_id',
        'province_id', 'city_id',
        // Program penyaluran
        'nama_program', 'jumlah_dana', 'tanggal_penyaluran', 'sumber_dana_penyaluran_id',
        'asnaf_id', 'bidang_program_id', 'lokasi_penyaluran', 'penerima_manfaat_individu',
        'penerima_manfaat_lembaga', 'jumlah_penerima_manfaat', 'keterangan',
        // Penggunaan hak amil & user
        'tanggal', 'jenis_penggunaan_hak_amil_id',
        // Master keuangan (BG-06/AK-10)
        'nama_sumber_dana', 'persentase_hak_amil', 'aktif', 'nama_fundraiser',
        'nomor_identitas', 'user_id', 'nama_kategori', 'kode', 'tipe',
        'nomor_rekening', 'atas_nama_rekening', 'bank_name', 'apakah_barang',
        'mengandung_dana_non_halal', 'membutuhkan_keterangan_tambahan',
    ];

    /**
     * Hanya super_admin yang boleh melihat jejak aktivitas pengguna lain.
     */
    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->with(['causer', 'subject'])->latest('created_at'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->date('d M Y')
                    ->description(fn (Activity $record): string => $record->created_at?->format('H:i') ?? '')
                    ->sortable(),
                ViewColumn::make('pengguna')
                    ->label('Pengguna')
                    ->state(fn (Activity $record): array => [
                        'nama' => $record->causer?->name ?? 'Sistem',
                        'email' => $record->causer?->email ?? 'console / sistem',
                    ])
                    ->view('filament.tables.columns.log-pengguna'),
                ViewColumn::make('aksi')
                    ->label('Aksi')
                    ->state(fn (Activity $record): string => (string) ($record->event ?? ''))
                    ->view('filament.tables.columns.log-aksi'),
                TextColumn::make('subjek')
                    ->label('Deskripsi')
                    ->state(fn (Activity $record): string => $this->labelSubjek($record))
                    ->description(fn (Activity $record): string => $this->labelModul((string) ($record->log_name ?? '')))
                    ->wrap(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->state(fn (Activity $record): string => data_get($record->properties, 'ip_address') ?? '-')
                    ->description(fn (Activity $record): string => $this->labelBrowser(data_get($record->properties, 'user_agent')))
                    ->copyable()
                    ->copyMessage('IP disalin')
                    ->copyMessageDuration(1500),
            ])
            ->actions([
                Action::make('detail')
                    ->label('Detail Perubahan')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip('Lihat detail perubahan')
                    ->modalHeading('Detail Perubahan')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Activity $record) => view('filament.tables.log-detail-perubahan', [
                        'event' => (string) ($record->event ?? ''),
                        'judul' => $this->labelSubjek($record),
                        'modul' => $this->labelModul((string) ($record->log_name ?? '')),
                        'rincian' => $this->rincianTerstruktur($record),
                    ]))
                    ->visible(fn (Activity $record): bool => data_get($record->properties, 'attributes', []) !== []),
            ])
            ->filters([
                SelectFilter::make('causer_id')
                    ->label('Pengguna')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('event')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Menambahkan',
                        'updated' => 'Memperbarui',
                        'deleted' => 'Menghapus',
                        'restored' => 'Memulihkan',
                    ]),
                SelectFilter::make('log_name')
                    ->label('Modul')
                    ->options([
                        'donasi' => 'Donasi',
                        'donatur' => 'Donatur',
                        'penyaluran' => 'Program Penyaluran',
                        'hak-amil' => 'Penggunaan Hak Amil',
                        'user' => 'Pengguna',
                        'sumber-dana' => 'Sumber Dana Penyaluran',
                        'metode-pembayaran' => 'Metode Pembayaran',
                        'jenis-donasi' => 'Jenis Donasi',
                        'fundraiser' => 'Fundraiser',
                        'kategori-infaq' => 'Kategori Infaq Terikat',
                    ]),
                Filter::make('created_at')
                    ->label('Rentang Waktu')
                    ->form([
                        DatePicker::make('dari')->label('Dari'),
                        DatePicker::make('sampai')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'] ?? null, fn (Builder $q, string $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['sampai'] ?? null, fn (Builder $q, string $v) => $q->whereDate('created_at', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indikasi = [];
                        if ($data['dari'] ?? null) {
                            $indikasi[] = 'Dari '.$data['dari'];
                        }
                        if ($data['sampai'] ?? null) {
                            $indikasi[] = 'Sampai '.$data['sampai'];
                        }

                        return $indikasi;
                    }),
            ])
            ->paginated([10, 25, 50, 100])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Hapus Terpilih')
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    /**
     * Label modul Indonesia dari log_name.
     */
    private function labelModul(string $logName): string
    {
        return match ($logName) {
            'donasi' => 'Donasi',
            'donatur' => 'Donatur',
            'penyaluran' => 'Program Penyaluran',
            'hak-amil' => 'Penggunaan Hak Amil',
            'user' => 'Pengguna',
            'sumber-dana' => 'Sumber Dana Penyaluran',
            'metode-pembayaran' => 'Metode Pembayaran',
            'jenis-donasi' => 'Jenis Donasi',
            'fundraiser' => 'Fundraiser',
            'kategori-infaq' => 'Kategori Infaq Terikat',
            default => ucfirst($logName),
        };
    }

    /**
     * Label singkat browser/OS dari user-agent untuk keterangan kolom IP.
     */
    private function labelBrowser(?string $userAgent): string
    {
        if (blank($userAgent)) {
            return '';
        }

        $browser = str_contains($userAgent, 'Edg/') ? 'Edge'
            : (str_contains($userAgent, 'Firefox') ? 'Firefox'
            : (str_contains($userAgent, 'Chrome') ? 'Chrome'
            : (str_contains($userAgent, 'Safari') ? 'Safari' : 'Browser')));

        $os = str_contains($userAgent, 'Windows') ? 'Windows'
            : (str_contains($userAgent, 'Android') ? 'Android'
            : ((str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) ? 'iOS'
            : (str_contains($userAgent, 'Mac') ? 'macOS'
            : (str_contains($userAgent, 'Linux') ? 'Linux' : ''))));

        return $browser.($os !== '' ? ' · '.$os : '');
    }

    /**
     * Label data yang tersentuh: tipe + judul ringkas (null-safe untuk data terhapus).
     */
    private function labelSubjek(Activity $record): string
    {
        if (! $record->subject_type) {
            return '-';
        }

        $tipe = class_basename($record->subject_type);
        $subjek = $record->subject;

        $judul = null;
        if ($subjek !== null) {
            // Donasi ditampilkan sebagai nominalnya (total uang + barang), bukan nomor transaksi.
            $judul = $subjek instanceof \App\Models\Donasi
                ? 'Rp '.number_format((float) $subjek->total_nilai, 0, ',', '.')
                : ($subjek->nama
                    ?? $subjek->name
                    ?? $subjek->nama_program
                    ?? null);
        }

        if ($judul !== null && $judul !== '') {
            return $tipe.' — '.$judul;
        }

        // Fallback terakhir bila judul tak tersedia (mis. data sudah dihapus).
        return $tipe.' #'.($record->subject_id ?? '-');
    }

    /**
     * Statistik ringkas untuk kartu di atas tabel.
     *
     * @return array{total: int, hari_ini: int, minggu_ini: int, pengguna: int}
     */
    public function statistikLog(): array
    {
        $dasar = Activity::query();

        return [
            'total' => (clone $dasar)->count(),
            'hari_ini' => (clone $dasar)->whereDate('created_at', today())->count(),
            'minggu_ini' => (clone $dasar)->where('created_at', '>=', now()->startOfWeek())->count(),
            'pengguna' => (clone $dasar)->distinct()->count('causer_id'),
        ];
    }

    /**
     * Rincian perubahan terstruktur untuk modal detail:
     * satu item = satu kolom dengan label ramah + nilai lama/baru.
     *
     * @return array<int, array{kolom: string, lama: ?string, baru: string, tambah: bool}>
     */
    private function rincianTerstruktur(Activity $record): array
    {
        $atributBaru = data_get($record->properties, 'attributes', []);
        $atributLama = data_get($record->properties, 'old', []);

        if ($atributBaru === []) {
            return [];
        }

        $kolomDilewati = ['id', 'created_at', 'updated_at'];
        $baris = [];
        foreach ($atributBaru as $kolom => $nilaiBaru) {
            if (in_array($kolom, $kolomDilewati, true) || ! in_array($kolom, self::KOLOM_PENTING, true)) {
                continue;
            }

            [, $teksBaru] = $this->labelDanNilai($kolom, $nilaiBaru);
            $teksBaru = $this->terjemahkanNilai($kolom, $nilaiBaru, $teksBaru ?? $this->nilaiRingkas($nilaiBaru));

            $nilaiLama = data_get($atributLama, $kolom);
            if ($atributLama === []) {
                $baris[] = [
                    'kolom' => $this->labelKolom($kolom),
                    'lama' => null,
                    'baru' => $teksBaru,
                    'tambah' => true,
                ];
            } else {
                [, $teksLama] = $this->labelDanNilai($kolom, $nilaiLama);
                $baris[] = [
                    'kolom' => $this->labelKolom($kolom),
                    'lama' => $this->terjemahkanNilai($kolom, $nilaiLama, $teksLama ?? $this->nilaiRingkas($nilaiLama)),
                    'baru' => $teksBaru,
                    'tambah' => false,
                ];
            }
        }

        return $baris;
    }

    /**
     * Label kolom yang mudah dipahami orang awam.
     */
    private function labelKolom(string $kolom): string
    {
        $peta = [
            'jumlah' => 'Jumlah Donasi',
            'perkiraan_nilai_barang' => 'Perkiraan Nilai Barang',
            'deskripsi_barang' => 'Deskripsi Barang',
            'tanggal_donasi' => 'Tanggal Donasi',
            'donatur' => 'Donatur',
            'donatur_id' => 'Donatur',
            'fundraiser' => 'Fundraiser',
            'fundraiser_id' => 'Fundraiser',
            'jenis_donasi' => 'Jenis Donasi',
            'jenis_donasi_id' => 'Jenis Donasi',
            'metode_pembayaran' => 'Metode Pembayaran',
            'metode_pembayaran_id' => 'Metode Pembayaran',
            'status_konfirmasi' => 'Status Konfirmasi',
            'catatan_konfirmasi' => 'Catatan Konfirmasi',
            'catatan_donatur' => 'Catatan Donatur',
            'keterangan_infak_khusus' => 'Keterangan Infak Khusus',
            'keterangan' => 'Keterangan',
            'atas_nama_hamba_allah' => 'Anonim (Hamba Allah)',
            'nama' => 'Nama',
            'email' => 'Email',
            'nomor_hp' => 'Nomor HP',
            'gender' => 'Jenis Kelamin',
            'alamat_detail' => 'Alamat',
            'alamat_lengkap' => 'Alamat Lengkap',
            'province' => 'Provinsi',
            'province_id' => 'Provinsi',
            'city' => 'Kota/Kabupaten',
            'city_id' => 'Kota/Kabupaten',
            'district' => 'Kecamatan',
            'district_id' => 'Kecamatan',
            'village' => 'Kelurahan/Desa',
            'village_id' => 'Kelurahan/Desa',
            'pekerjaan' => 'Pekerjaan',
            'pekerjaan_id' => 'Pekerjaan',
            'nama_program' => 'Nama Program',
            'jumlah_dana' => 'Jumlah Dana',
            'tanggal_penyaluran' => 'Tanggal Penyaluran',
            'lokasi_penyaluran' => 'Lokasi Penyaluran',
            'tanggal' => 'Tanggal',
            'asnaf' => 'Asnaf',
            'asnaf_id' => 'Asnaf',
            'bidang_program' => 'Bidang Program',
            'bidang_program_id' => 'Bidang Program',
            'sumber_dana_penyaluran' => 'Sumber Dana',
            'sumber_dana_penyaluran_id' => 'Sumber Dana',
            'penerima_manfaat_individu' => 'Penerima Manfaat (Individu)',
            'penerima_manfaat_lembaga' => 'Penerima Manfaat (Lembaga)',
            'jumlah_penerima_manfaat' => 'Jumlah Penerima Manfaat',
            'nama_sumber_dana' => 'Nama Sumber Dana',
            'persentase_hak_amil' => 'Persentase Hak Amil',
            'aktif' => 'Status Aktif',
            'nama_fundraiser' => 'Nama Fundraiser',
            'nomor_identitas' => 'Nomor Identitas',
            'user_id' => 'Pengguna',
            'nama_kategori' => 'Nama Kategori',
            'kode' => 'Kode',
            'tipe' => 'Tipe',
        ];

        return $peta[$kolom] ?? ucwords(str_replace('_', ' ', $kolom));
    }

    /**
     * Terjemahkan nilai teknis agar mudah dibaca orang awam.
     */
    private function terjemahkanNilai(string $kolom, mixed $nilai, string $teks): string
    {
        if ($kolom === 'status_konfirmasi') {
            return match ($nilai) {
                'pending' => 'Menunggu Verifikasi',
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak',
                default => $teks,
            };
        }

        if ($kolom === 'gender') {
            return match ($nilai) {
                'male' => 'Laki-laki',
                'female' => 'Perempuan',
                default => $teks,
            };
        }

        if (in_array($kolom, ['atas_nama_hamba_allah', 'aktif'], true) && is_bool($nilai)) {
            return $nilai ? 'Ya' : 'Tidak';
        }

        return $teks;
    }

    /**
     * Label & teks nilai kolom: kolom relasi (*_id) diterjemahkan menjadi
     * nama relasinya; null berarti pemanggil memakai nilai mentah.
     *
     * @return array{0: string, 1: ?string}
     */
    private function labelDanNilai(string $kolom, mixed $nilai): array
    {
        if (! isset(self::RELASI_NAMA[$kolom])) {
            return [$kolom, null];
        }

        [$kelasModel, $kolomNama] = self::RELASI_NAMA[$kolom];
        $kunci = $kolom.'='.(string) $nilai;
        if (! array_key_exists($kunci, $this->cacheNamaRelasi)) {
            $this->cacheNamaRelasi[$kunci] = $nilai === null
                ? null
                : $kelasModel::query()->whereKey($nilai)->value($kolomNama);
        }

        $nama = $this->cacheNamaRelasi[$kunci];

        return [str_replace('_id', '', $kolom), $nama !== null ? (string) $nama : null];
    }

    private function nilaiRingkas(mixed $nilai): string
    {
        if ($nilai === null) {
            return 'kosong';
        }
        if (is_bool($nilai)) {
            return $nilai ? 'true' : 'false';
        }
        if (is_array($nilai)) {
            $nilai = json_encode($nilai);
        }

        $teks = (string) $nilai;

        if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $teks)) {
            return Carbon::parse($teks)->translatedFormat('d M Y H:i');
        }

        if (is_numeric($teks) && (float) $teks == (int) $teks) {
            return (string) (int) $teks;
        }

        return mb_strlen($teks) > 40 ? mb_substr($teks, 0, 40).'…' : ($teks === '' ? '""' : $teks);
    }
}
