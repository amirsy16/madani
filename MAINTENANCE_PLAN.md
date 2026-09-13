# Rencana Maintenance Besar — madaniX

> Dibuat: 13 September 2026. Basis: audit keamanan (security-auditor), review bug/kualitas kode (testing-reviewer), dan analisis performa. Semua temuan telah diverifikasi dengan membaca kode.

---

## STATUS SITUASI

**Server produksi (simadi.org) harus dianggap pernah/terus dalam kondisi terkompromi.** Bukti:

1. Webshell `6a2bb11a94e7.php` terbukti dieksekusi via HTTP di produksi pada 14 Mei 2026 (jejak di `error_log`).
2. Tiga salinan file downloader malware (MD5 identik `f1b7ab12...`) masih ada di repo:
   - `app/Filament/Resources/PenggunaanHakAmilResource/Pages/check-version.php`
   - `storage/framework/cache/ajax_php.php`
   - `resources/views/laporan/pdf/ajax_php.php`
3. Enam file `.htaccess` jebakan (`Require all granted` + FilesMatch php) yang memastikan webshell bisa diakses via web masih tersebar.
4. `public/lj.php` berisi `phpinfo()` + stub upload file — probe aktif.
5. Document root server mengarah ke root project (bukan `public/`), sehingga `/.env`, `/error_log`, dan semua file di luar `public/` dapat diakses/dieksekusi publik.

## DAFTAR TEMUAN (RINGKASAN)

### KRITIS — Keamanan
| # | Temuan | Lokasi |
|---|--------|--------|
| K1 | Webshell/downloader malware (3 salinan identik) | `check-version.php`, `storage/framework/cache/ajax_php.php`, `resources/views/laporan/pdf/ajax_php.php` |
| K2 | 6 file `.htaccess` jebakan pembuka eksekusi PHP publik | spt. `app/.../Widgets/.htaccess`, `storage/framework/views/.htaccess` (menimpa `Require all denied`) |
| K3 | `public/lj.php` (phpinfo + stub upload), `6a2bb11a94e7.php` ×4, `about.php`, `privacy-modules.php`, `media.php`, `PHPInit.php` ×2 — jejak infeksi dikosongkan tapi tidak dihapus | `public/`, `storage/`, `app/` |
| K4 | Document root = root project → `/.env` bisa diunduh publik | `index.php` + `.htaccess` root |
| K5 | `.env` berisi kredensial produksi asli (APP_KEY, DB, Gmail app password, Web3Forms) — anggap bocor | `.env` |
| K6 | `APP_ENV=local`, `APP_DEBUG=true` di produksi | `.env` |
| K7 | Route `/invoice/download/{donasi}` tanpa auth & tanpa cek kepemilikan → enumerate semua PDF donatur (nama, HP, alamat, nominal) | `routes/web.php:24-41` |
| K8 | SQL injection: `$fundraiserId` (public property Livewire, bisa dimodifikasi client) diinterpolasi ke `DB::raw()` | `app/Livewire/Filament/Widgets/DonaturFundraiserTable.php:52-54` |
| K9 | Invoice PDF & bukti transfer disimpan di disk publik (`/storage/invoices/...` dapat diunduh tanpa login) | `PdfService.php:43-46`, `DonasiResource.php:361` |
| K10 | `canAccessPanel()` return true tanpa syarat — semua user langsung akses panel | `app/Models/User.php:57-60` |

### KRITIS/TINGGI — Bug Fungsional
| # | Temuan | Lokasi |
|---|--------|--------|
| B1 | Dispatch `SendWhatsAppInvoice` salah signature (3 argumen vs 4 wajib, tipe Donasi vs Donatur) → TypeError tak tertangkap → fitur kirim invoice WA 100% rusak | `NotificationService.php:119` vs `app/Jobs/SendWhatsAppInvoice.php:36` |
| B2 | Tidak ada kode yang membuat record `InvoiceDonasi` → tracking invoice, filter, dan navigation badge mati/menyesatkan permanen | `DonasiResource.php:558-632, 810-840, 1167-1186` |
| B3 | Form kirim `regency_id`, kolom DB `city_id` → kota donatur tidak pernah tersimpan | `DonaturResource.php:161`, `DonasiResource.php:125-141` |
| B4 | Migration index pakai `CREATE INDEX IF NOT EXISTS` (tidak valid di MySQL) + error ditelan try/catch → 12 index TIDAK PERNAH dibuat | `database/migrations/2025_06_11_005856_...php:17-38` |
| B5 | Relasi `dikonfirmasiOleh()` pakai FK salah nama (`dikonfirmasi_` vs kolom `dikofirmasi_`) → jejak audit verifikator hilang | `app/Models/Donasi.php:29` |
| B6 | Filter "Status Invoice" merujuk tabel `invoice_donasis` (nama asli `invoice_donasi`) → SQL error saat dipakai | `DonasiResource.php:829-834` |
| B7 | Periode "Bulan Lalu / 3 Bulan / 6 Bulan" tidak ditangani → statistik menampilkan SEMUA data | `RingkasanStatistikUtama.php:59-70 vs 482-508` |
| B8 | Laporan Perubahan Dana: pembulatan inkonsisten, total baris tidak balance, `max(0, saldo)` menutup saldo negatif → keuangan salah tampil | `DanaService.php:90, 111-116, 161, 174-177` |
| B9 | Tidak ada `DB::transaction`/lock di seluruh flow keuangan (verifikasi, penyaluran cek-saldo TOCTOU, generate kode) | `ProgramPenyaluranResource`, `Donatur.php:62-96` |
| B10 | QUEUE_CONNECTION=database tapi tidak ada worker → job menumpuk selamanya, tidak pernah diproses | `.env` + tidak ada cron/worker |

### PENYEBAB LAG (Performa)
| # | Temuan | Lokasi |
|---|--------|--------|
| P1 | Kolom `invoice_status`: 3-5 query PER BARIS tabel → ±100-125 query per render, diulang tiap interaksi Livewire | `DonasiResource.php:558-631` |
| P2 | Navigation badge Donasi: anti-join COUNT full-table × 2, dieksekusi di SETIAP request panel | `DonasiResource.php:1167-1186` |
| P3 | Widget dashboard/statistik: 20+ aggregate full-table per render, NOL caching di seluruh app (`Cache::` tidak pernah dipakai) | `RingkasanStatistikUtama.php`, `DonasiOverviewStats.php`, `ZakatStatsOverview.php` |
| P4 | `HakAmilOverviewWidget` load seluruh donasi bulan ke memori + `find()` per baris | `HakAmilOverviewWidget.php:23-30` + `Donasi.php:66-81` |
| P5 | DanaService: query dalam loop per jenis donasi, rekomputasi penuh; halaman LaporanPerubahanDana lambat sejak dibuka | `DanaService.php:134-262, 371-421` |
| P6 | `TrenDonasiChart`: 12 query non-sargable (`whereYear`+`whereMonth`) seharusnya 1 GROUP BY | `TrenDonasiChart.php:36-57` |
| P7 | Generate PDF sinkron di request HTTP (DomPDF, `set_time_limit(120)`), bulk invoice loop per donasi sinkron | `NotificationService.php:111, 255-311`, `LaporanPerubahanDana.php:172-272` |
| P8 | Import Excel: 3-4 query per baris + 4-5 baris log per baris data | `ListDonasis.php:43-97, 157-266` |
| P9 | `whereHas('jenisDonasi', nama != 'Penyaluran Langsung')` di >15 tempat — EXISTS + LIKE tanpa index; harusnya kolom flag | hampir semua widget/laporan |
| P10 | Kombinasi B4 (index tidak pernah ada) mengalikan semua temuan di atas | — |

### REDUNDANSI & DEAD CODE (maintenance burden)
- `DonasiStat.php` = duplikat identik `DonasiOverviewStats.php`; widget terdaftar 2× (getWidgets + getHeaderWidgets).
- Perhitungan hak amil diimplementasi 5× dengan aturan pembulatan berbeda (sumber angka tidak konsisten antar halaman).
- Formula `SUM(jumlah + IFNULL(perkiraan_nilai_barang,0))` duplikat 15+ tempat.
- Filter periode copy-paste 7× di Livewire tables + widget.
- 2 pasang tabel Livewire paralel (satu pasang dead code); 9 method DanaService tanpa pemanggil; helper `InvoiceDonasi` & `NotificationService::sendInvoice*` banyak yang mati.
- File 0-byte: 4 console command, `DonasiModalTable.php`, `PHPInit.php`; `ListDonatisExample.php` tidak terpakai; `error_log` 392KB ter-commit.
- Log debug tertinggal di jalur produksi (`Log::info` per baris, kolom "untuk debug").
- **Tidak ada test sama sekali** (`tests/` tidak ada, `phpunit.xml` merujuk direktori kosong).

---

# RENCANA MAINTENANCE

## FASE 0 — RESPON INSIDEN KEAMANAN (SEKARANG, hari 1-2)
> Tujuan: memutus akses penyerang, menutup kebocoran kredensial. Jangan deploy apa pun sebelum ini selesai.

1. **Audit forensik server produksi** (sebelum pembersihan):
   - Unduh & simpan `error_log`, access log Apache (cari request ke `6a2bb11a94e7.php`, `check-version.php`, `ajax_php.php`, `lj.php`, `/.env`, `PHPInit.php` — filter sekitar 14 Mei 2026 dan sesudahnya).
   - Cek cron job cPanel, akun FTP/SSH tambahan, file yang dimodifikasi periode 13-15 Mei & 14 Jun 2026 (`find . -newermt ... -name "*.php"`).
2. **Rotasi SEMUA kredensial** (anggap bocor):
   - `php artisan key:generate` (semua session logout).
   - Password DB MySQL, app password Gmail, Web3Forms key, password semua akun admin (paksa reset).
   - Password cPanel/FTP/SSH hosting.
3. **Hapus semua artefak malware** (workstation & server):
   - Hapus: 3 file webshell (`check-version.php`, 2× `ajax_php.php`), `public/lj.php`, semua `6a2bb11a94e7.php`, `about.php`, `privacy-modules.php`, `media.php`, 2× `PHPInit.php`.
   - Hapus 5 `.htaccess` jebakan; **restore** `storage/framework/views/.htaccess` ke `Require all denied` (dan `storage/` serta `storage/framework/cache/` juga harus denied).
4. **Perbaiki deployment** (butuh akses hosting):
   - Document root WAJIB menunjuk ke `{project}/public/` saja. Hapus `index.php` dan `.htaccess` di root dari deployment.
   - Pindahkan seluruh kode keluar dari `public_html` (struktur cPanel standar: aplikasi di `~/madani`, docroot `~/madani/public`).
   - Hapus `error_log` dari docroot; tambahkan ke `.gitignore`.
5. **Set `.env` produksi**: `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`.

**Gerbang keluar Fase 0:** akses log sudah dianalisis, semua kredensial dirotasi, scan `find public storage app resources -name "*.php" -o -name ".htaccess"` bersih dari artefak, `curl https://simadi.org/.env` mengembalikan 404.

## FASE 1 — MENUTUP CELAH KEAMANAN APLIKASI (minggu 1)
> Tujuan: tidak ada lagi endpoint tanpa otorisasi.

1. Route `/invoice/download/{donasi}`: tambah `->middleware(['auth', 'can:view,donasi'])`; pertimbangkan signed URL untuk donatur eksternal. *(K7)*
2. Perbaiki SQL injection `DonaturFundraiserTable`: ganti interpolasi `DB::raw($this->fundraiserId)` menjadi subquery builder dengan binding / cast `(int)`. *(K8)*
3. Pindahkan invoice PDF & bukti transfer ke disk `private`; akses via route ber-auth yang stream file + `temporarySignedRoute`. Hapus file lama dari `public/storage/`. *(K9)*
4. `canAccessPanel()`: batasi `return $user->hasRole(['admin','operator','fundraiser']);` sesuai kebutuhan nyata. *(K10)*
5. Tambah `canAccess()`/policy untuk halaman laporan keuangan (LaporanPemasukan, LaporanPerubahanDana, AnalisisData) dan resource master-data (JenisDonasi, KategoriInfaqTerikat, MetodePembayaran). *(temuan sedang)*
6. Hapus `'verify' => false` dari semua panggilan WhatsAppService. *(A14)*
7. Perbaiki policy placeholder `{{ DeleteAny }}` (DonasiPolicy, RolePolicy).

## FASE 2 — PERBAIKAN BUG FUNGSIONAL & KEUANGAN (minggu 2-3)
> Tujuan: fitur inti bekerja, angka keuangan benar. Kerjakan berurutan, satu bug = satu commit.

1. **Perbaiki dispatch `SendWhatsAppInvoice`** (B1): samakan signature dengan constructor Job; buat record `InvoiceDonasi` pada saat pengiriman agar tracking/badge hidup (B2).
2. **Jalankan queue**: cron cPanel tiap menit `php artisan queue:work --stop-when-empty --max-time=55` (shared hosting) atau `queue:work` supervisor (B10). Monitor tabel `jobs` yang menumpuk sekarang → truncate setelah diverifikasi isinya.
3. **Perbaiki mapping kota** `regency_id` → `city_id` (B3); tulis migrasi data backfill `city_id` untuk donatur yang salah input bila memungkinkan.
4. **Tulis ulang migration index** dengan schema builder portable (`$table->index()`), nama kolom benar (`dikofirmasi_oleh_user_id`), hapus index duplikat/overlap; jalankan lalu verifikasi `SHOW INDEX FROM donasis` di produksi (B4, P10).
5. Perbaiki relasi `dikonfirmasiOleh()` + backfill data (B5); nama tabel `invoice_donasi` di filter (B6); periode filter bulan_lalu/3_bulan/6_bulan (B7); `kode_transaksi` → `nomor_transaksi_unik` (A11 reviewer).
6. **Konsolidasi perhitungan keuangan ke SATU tempat** (B8 + redundansi): buat `DanaService` sebagai satu-satunya sumber; semua widget memanggil service yang sama; putuskan aturan pembulatan tunggal; perbaiki total "balance" laporan perubahan dana; hapus `max(0, saldoAwal)` atau jadikan opsi eksplisit.
7. Bungkus verifikasi donasi & penyaluran dalam `DB::transaction` + `lockForUpdate` pada cek saldo; generate kode donatur dengan `retry_until` / unique + retry (B9).
8. Rapikan label `rejected` yang inkonsisten; hapus log debug produksi (`Log::info` di ListDonatis::parseTanggal, RingkasanStatistikUtama, dsb.).

## FASE 3 — PERBAIKAN PERFORMA (minggu 3-4)
> Tujuan: menghilangkan lag. Urutan berdasarkan dampak/effort.

1. **Kolom `invoice_status`** → `modifyQueryUsing(fn ($q) => $q->withCount('invoices')->withMax('invoices','created_at'))`, pakai relasi `latestInvoice` yang sudah ada. *(P1 — dampak terbesar, effort kecil)*
2. **Navigation badge**: hitung sekali, simpan di properti statik/cache; gabungkan query badge+color. *(P2)*
3. **Caching statistik**: bungkus `getStats()`/`getLaporan*()` dengan `Cache::remember(..., 300, ...)`; invalidasi via observer/event pada Donasi created/updated/deleted. Nol baris `Cache::` saat ini. *(P3)*
4. `HakAmilOverviewWidget` → single aggregate SQL (hapus loop + accessor yang `find()` per baris). *(P4)*
5. DanaService: hapus query dalam loop (gabung dengan GROUP BY), dan `LaporanPerubahanDana` jangan regenerasi penuh di `mount()` — lazy + cache. *(P5)*
6. `TrenDonasiChart` → 1 query `GROUP BY` bulan. *(P6)*
7. Pindahkan generate PDF ke job queued; bulk invoice = `Bus::batch`. *(P7)*
8. Tambah kolom flag `donasis.is_penyaluran_langsung` (atau cache daftar ID) untuk mengganti puluhan `whereHas(... nama != ...)`; index `penggunaan_hak_amils.tanggal`, `program_penyalurans.tanggal_penyaluran`. *(P9)*
9. Import Excel: hapus logging per-baris, kurangi lookup per baris (prefetch map jenis/metode/fundraiser sebelum loop). *(P8)*
10. Hilangkan widget duplikat (`DonasiStat.php` hapus; `DonasiOverviewStats` cukup terdaftar sekali).

## FASE 4 — PEMBERESAN KODE & ARSITEKTUR (minggu 5-6)
> Tujuan: menurunkan biaya maintenance.

1. Hapus dead code: `ListDonasisExample.php` + `GenerateDonationFormInvoiceAction.php`, `DonaturListTable`/`TransaksiListTable` + 2 blade yatim, 9 method DanaService tak terpakai, helper `InvoiceDonasi` mati (atau hidupkan lewat Fase 2.1), `sendInvoiceByEmail/Sms/Bulk` (hidupkan yang dipakai, hapus sisanya), 4 console command 0-byte, `DonasiModalTable.php`, blade base64 kosong, `CheckPasswordReset.php` middleware yatim.
2. Ekstrak filter periode menjadi satu trait `HasPeriodFilter`; formula total menjadi `Donasi::scopeTotalNilai()`; agregasi top-donatur menjadi satu method repository.
3. Ganti ketergantungan magic-string (`'Penyaluran Langsung'`, `LIKE '%zakat%'`) dengan relasi/kolom kategori eksplisit.
4. Ganti API deprecated Filament (`->reactive()` → `->live()`, `modalSubheading` → `modalDescription`).
5. Hapus hack `Table::configureUsing` yang `session()->forget()` per render di `AppServiceProvider`.
6. Aktifkan `laravel/pint` pada CI/pre-commit untuk standar kode.

## FASE 5 — FONDASI KUALITAS (berkelanjutan, mulai minggu 6)
> Tanpa ini, semua di atas akan berulang.

1. Buat `tests/` (saat ini NOL): 
   - Feature: lifecycle donasi (pending → verified/rejected), authorization (user biasa tak bisa akses laporan/invoice), route `/invoice/download` menolak tamu.
   - Unit: `DanaService` (saldo, hak amil, laporan balance: `penerimaan − bagian_amil − penyaluran = surplus`), generate kode donatur/nomor transaksi, `parseTanggal`.
2. Git: inisialisasi repo (saat ini BUKAN git repository!), `.gitignore` untuk `.env`, `error_log`, `public/storage`; branch + tag rilis; backup terjadwal DB + upload.
3. Monitoring: cek `storage/logs` & tabel `failed_jobs` mingguan; uptime sederhana.
4. Jadwal maintenance rutin: composer update bulanan (patch), `storage:optimize`/`config:cache` setelah deploy, review access log bulanan.

---

## URUTAN EKSEKUSI RINGKAS

| Prioritas | Aksi | Estimasi |
|---|---|---|
| 🔴 SEKARANG | Fase 0: forensik + rotasi kredensial + hapus malware + perbaiki docroot | 1-2 hari |
| 🔴 SEKARANG | Fase 1: auth route invoice, perbaiki SQLi, disk private | 2-3 hari |
| 🟠 Minggu 1-2 | Fase 2: invoice WA, queue worker, index DB, konsolidasi keuangan | 2 minggu |
| 🟡 Minggu 2-4 | Fase 3: N+1, caching, async PDF | 2 minggu |
| 🟢 Minggu 4-6 | Fase 4: pemberesan dead code & redundansi | 2 minggu |
| 🟢 Minggu 6+ | Fase 5: test suite + git + monitoring | berkelanjutan |

## DEFINISI SELESAI (acceptance)
- `curl -I https://simadi.org/.env` → 404; semua path webshell → 404.
- Tidak ada request tanpa-auth yang mengembalikan data donatur.
- Kolom "Status Invoice" hidup, badge navigasi akurat, invoice WA terkirim (cek tabel `jobs` berkurang).
- `SHOW INDEX FROM donasis` menampilkan index kerja; halaman Donasi & dashboard < 1 detik pada data nyata.
- Laporan Perubahan Dana balance (total baris = total kolom).
- `php artisan test` hijau untuk minimal: lifecycle donasi, otorisasi, DanaService.
- `grep -r "Cache::" app/` mengembalikan penggunaan caching di widget utama.
