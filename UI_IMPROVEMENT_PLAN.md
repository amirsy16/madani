# UI IMPROVEMENT PLAN — madaniX (Laravel 11 + Filament 3.3)

Tanggal: 2026-09-13 · Status: PLANNING (belum ada kode diubah)
Target user: tampilan **compact, clean, mudah dilihat, dan USEFUL** — semua data penting punya drill-down detail.

---

## A. RINGKASAN KEADAAN SEKARANG (per kelompok halaman)

### A.1 Dashboard
| Widget | Implementasi | Penilaian |
|---|---|---|
| `ZakatStatsOverview.php` | Native Filament `StatsOverviewWidget` + StatsCache | Baik, patokan desain. Tidak ada drill-down (angka mati). |
| `TopDonaturRingkasWidget.php` + blade | Custom card, manual SVG | Rapi, ada link ke detail donatur (baris 39). OK. |
| `TrenDonasiChart.php` | Native `ChartWidget`, **tanpa `isDiscovered=false`** | Auto-discovered → tampil di dashboard tanpa disengaja (AdminPanelProvider.php:73 `discoverWidgets`). Perlu keputusan eksplisit. |
| `HakAmilOverviewWidget.php` | Native `StatsOverviewWidget`, **tanpa `isDiscovered=false`** | Idem — kemungkinan bocor ke dashboard. |

### A.2 Halaman Analisis Data (`filament.pages.analisis-data`)
- **`ringkasan-statistik-utama.blade.php` (511 baris) + `RingkasanStatistikUtama.php` (825 baris)** — file paling bermasalah:
  - **Sisa artefak debug**: `@php $debugCount = 0` (blade:202-204), tombol `"DEBUG: Tombol ini HARUS muncul"` + label `Card #N` (blade:226-235), badge `"v2.0"` (blade:197).
  - **Inline style `!important`** pada tombol drill-down (blade:231, 306-312, 390-403) — indikasi pernah rusak lalu "dipaksa" tampil; juga `onmouseover/onmouseout` JS inline (blade:311-312, 395-396).
  - **Bug warna dinamis**: `class="text-{{ $stat['color'] }}-600"` (blade:115) — Tailwind JIT tidak bisa generate class hasil interpolasi, dan `success/info` bukan palet default Tailwind. Nilai statistik kemungkinan besar **tidak berwarna** sesuai rencana. PHP sudah menyediakan key `color` + `clickable`/`action` (RingkasanStatistikUtama.php:374-484) yang diabaikan blade.
  - **Ikon emoji** di heading & label (📊💰🕌 dst) — inkonsisten dengan Heroicons di halaman lain.
  - **Modal hand-rolled** (blade:442-509): overlay/panel modal dibuat manual, bukan `<x-filament::modal>`; konten tabelnya (`detail-donasi-modal.blade.php`, 300 baris) **meniru markup internal Filament (`fi-ta-*`)** secara manual — sangat rawan rusak saat update Filament 3.x.
  - **Drill-down parsial**: Infaq Terikat / Zakat / Donasi Barang punya detail (toggle panel + modal). **Angka mati**: DANA INFAQ (agregat), SEDEKAH, CSR, DSKL, HAK AMIL, PENGGUNAAN & SISA HAK AMIL, PENYALURAN PROGRAM/LANGSUNG, SISA DANA PROGRAM.
  - Label stat ALL CAPS (`'TOTAL DANA TERHIMPUN'` PHP:376) vs heading sentence case di halaman lain — inkonsisten.
  - `wire:key="stat-{{ $index }}"` (blade:101) — key berbasis index pada list yang bisa berubah urutan/isi; lebih aman key by label.
- **`metode-pembayaran-chart.blade.php` (472 baris)**:
  - 2 modal hand-rolled (blade:269-362, 364-470) alih-alih `x-filament::modal`.
  - Tombol mata "Aksi" **tanpa handler** (blade:250-252) — dead button (row click sudah `wire:click="selectMetode"`, blade:190-192).
  - Inline `!important` lagi (blade:450).
  - 3 kartu ringkasan dengan progress bar dekoratif yang selalu 100% (blade:98-102, 119-123) — tidak informatif.
- **`TopDonaturWidget.php` + `top-donatur/donasi-modal.blade.php` + `analytics-modal.blade.php`** — native Filament Table + slide-over berisi Livewire table. Ini **pola terbaik** di codebase; jadikan template drill-down. Kekurangan: `analytics-modal.blade.php` (556 baris) masih banyak gradient/emoji (`from-blue-500 to-purple-600`, 💎👑🥇) tidak nyambung dengan palet maroon panel.
- **`FundraiserPerformanceWidget.php`** — native table + slide-over ke Livewire table (`fundraiser-transaksi-modal`, `fundraiser-donatur-modal`). Baik. Kontra: `->paginated(false)` dengan `limit 0 = Semua` berisiko render ribuan baris; filter memutasi properti di dalam `->query()` closure (side-effect, dieksekusi saat sorting/pagination juga).
- `analisis-data.blade.php` menimpa heading tabel Filament via `<style>` + `!important` (baris 2-15) — konflik dengan heading native widget.

### A.3 Laporan Pemasukan (`laporan-pemasukan.blade.php` 295 baris + `LaporanPemasukan.php` 929 baris)
- Hierarki baik (filter → ringkasan global → metrik + perbandingan periode → breakdown → tabel). Metrik + delta % periode sebelumnya = data berguna.
- Semua kartu metrik & baris breakdown (Zakat/Infaq/Sedekah/CSR, status Verified/Pending/Rejected) **tidak bisa diklik** — drill-down nol, padahal tabel detail tersedia di bawah.
- Emoji icon (💵📦💰📊🕌💚💝🏢) + SVG inline manual berulang; tabel "Ringkasan per Jenis Donasi" dibuat manual (blade:251-287) padahal Filament table sudah dipakai untuk detail.
- Kartu memakai `rounded-xl` + shadow duplikatif bertingkat (card dalam card dalam card).

### A.4 Laporan Perubahan Dana (`laporan-perubahan-dana.blade.php` 860 baris + `LaporanPerubahanDana.php`)
- **~224 baris `<style>` custom** (gradients, `:root` palette biru, animasi `fade-in`, hover transform) — palet biru/ungu ini **menabrak primary maroon `#800020`** (AdminPanelProvider.php:45).
- **JS imperative di blade** (`DOMContentLoaded` + IntersectionObserver + hover listener, baris 807-860): tidak akan jalan ulang setelah re-render Livewire (`wire:submit="generateReport"`), dan tidak berguna (duplikasi CSS animation).
- **N+1 query di blade**: `SumberDanaPenyaluran::find($data['sumber_dana_id'])` di dalam loop kartu dana (baris 474-481) — harus dihitung di `DanaService` dan dikirim sebagai data.
- Header seksi gradient biru + huruf ALL CAPS (`RINGKASAN KESELURUHAN`), emoji besar sebagai ikon (💰📤🏦📊).
- Konten laporan (struktur penerimaan→penyaluran→saldo) bagus & padat; masalah utamanya styling & CSS/JS, bukan struktur.
- `hak-amil-section.blade.php` + data hak amil dimuat di `LaporanPerubahanDana.php:62-65` tapi **tidak pernah dirender** di view (data dimuat sia-sia).

### A.5 Aduan Saran & Credits
- `aduan-saran.blade.php` — paling sehat: komponen Filament (`x-filament-panels::page`, `x-filament::button`, `{{ $this->form }}`), jelas, responsif. Jadi contoh pola halaman.
- `credits.blade.php` (378 baris) — custom CSS gradient, hover transform; halaman low-traffic, prioritas rendah. Modal changelog pakai `x-show` manual (bisa jadi `x-filament::modal`).

### A.6 Partial laporan & tabel Livewire
- `livewire/filament/widgets/*.blade.php` — wrapper `{!! $this->table !!}` 3 baris, wajar untuk Livewire table Filament. OK.
- `tables/fundraiser-header.blade.php`, `tables/top-donatur-header.blade.php` — **tidak direferensikan**; isinya menimpa heading native Filament dengan font 2xl → hapus.

### A.7 Tema & konteks
- Primary `#800020` (maroon), top navigation, sidebar 13rem, Shield + Breezy + ApexCharts plugin terpasang (`FilamentApexChartsPlugin`, AdminPanelProvider.php:25) — ApexCharts **belum dipakai**; bisa dipakai untuk chart tanpa CDN Chart.js manual.
- `tailwind.config.js` hanya menambah palet `madani` (hijau) — tidak dipakai blade manapun (grep kosong); tidak ada safelist untuk warna dinamis → mengonfirmasi bug warna dinamis di A.2.

---

## B. BLADE ORPHAN / DEAD (aman dihapus — semua sudah diverifikasi tanpa referensi di `app/`, `resources/`, `routes/`)

| # | File | Bukti |
|---|---|---|
| 1 | `resources/views/filament/modals/advanced-analytics.blade.php` | 0 byte, tanpa referensi |
| 2 | `resources/views/filament/widgets/donatur-detail-modal.blade.php` | 0 byte, tanpa referensi |
| 3 | `resources/views/filament/widgets/top-donatur-analytics.blade.php` | 0 byte; yang dipakai `TopDonaturWidget.php:604` adalah `top-donatur/analytics-modal` |
| 4 | `resources/views/filament/pages/laporan/dana-section.blade.php` | Seluruh isi dikomentari; tanpa referensi |
| 5 | `resources/views/filament/pages/laporan/hak-amil-section.blade.php` | Tanpa referensi; hak amil tidak dirender di `laporan-perubahan-dana` |
| 6 | `resources/views/filament/widgets/top-donatur/detail-modal.blade.php` | Tanpa referensi (grep `top-donatur.detail-modal` kosong) |
| 7 | `resources/views/filament/widgets/fundraiser-performance/jenis-donasi-modal.blade.php` | Tanpa referensi; berisi query DB langsung di `@php` blade (anti-pattern, untung orphan) |
| 8 | `resources/views/livewire/filament/widgets/donatur-list-table.blade.php` | Komponen `DonaturListTable.php` tidak ada di `app/Livewire` |
| 9 | `resources/views/livewire/filament/widgets/transaksi-list-table.blade.php` | Komponen `TransaksiListTable.php` tidak ada |
| 10 | `resources/views/filament/widgets/tren-donasi-chart.blade.php` | `TrenDonasiChart.php` adalah Filament `ChartWidget` (view vendor), custom blade ini tidak dipakai |
| 11 | `resources/views/tables/fundraiser-header.blade.php` | Tanpa referensi |
| 12 | `resources/views/tables/top-donatur-header.blade.php` | Tanpa referensi |

Catatan: hapus #10 hanya jika keputusan di Fase 4 memilih ApexCharts native (disarankan). PHP widget `TrenDonasiChart.php` & `HakAmilOverviewWidget.php` perlu keputusan eksplisit: tambahkan `isDiscovered = false` atau biarkan di dashboard.

---

## C. RENCANA PERBAIKAN (bertahap, prioritas)

> Prinsip: tidak menyentuh query/branch bisnis & perhitungan keuangan (DanaService, StatsCache, formula hak amil). Perubahan UI + tempat data disiapkan untuk view saja.

### PRIORITAS 1 — Cleanup & bug nyata (Week 1) — effort S, risiko rendah
1. **Hapus 12 file orphan** (daftar B). Risiko: hampir nol; verifikasi `php artisan view:clear` + buka 3 halaman utama.
2. **Bersihkan artefak debug** di `ringkasan-statistik-utama.blade.php`: hapus `$debugCount` (202-204), tombol DEBUG + "Card #N" (226-235), badge "v2.0" (197), komentar "DEBUG" (226). Effort S. Risiko: nol.
3. **Perbaiki warna stat dinamis** (blade:115): ganti interpolasi dengan mapping statis di PHP, mis. di `RingkasanStatistikUtama::computeStats()` kirim kelas final `'value_class' => 'text-emerald-600 dark:text-emerald-400'` (atau pakai palet panel: primary/success/warning/danger Filament via style `color: var(--success-600)`). Effort S. Risiko: rendah — murni tampilan.
4. **Hapus tombol mata dead** di `metode-pembayaran-chart.blade.php:249-253` dan inline `!important` (450) + `onmouseover` JS; ganti dengan class `hover:bg-…`. Effort S.
5. **Hapus `<style>` override heading** di `analisis-data.blade.php:2-15` — biarkan heading tabel Filament native. Effort S. Risiko: heading tabel jadi ukuran default Filament (ini justru tujuan konsistensi).
6. **Laporan Perubahan Dana — buang CSS/JS custom**: hapus `<style>` (baris 2-226) dan `<script>` (807-860); ganti `.report-card/.section-header/.modern-table/.status-indicator/.progress-bar/.currency` dengan utility Tailwind + komponen `x-filament::section`/badge. Ganti gradient biru → netral + aksen primary maroon & status emerald/rose. Pertahankan struktur tabel. Effort M. Risiko: sedang (regresi visual — uji light+dark).
7. **Pindahkan query `SumberDanaPenyaluran::find()`** dari blade (474-481) ke `DanaService`/`LaporanPerubahanDana::generateReport()` sehingga `$data['persentase_hak_amil']` sudah tersedia. Effort S. Risiko: rendah (nilai sama, hanya sumbernya).

### PRIORITAS 2 — Standarisasi & compact (Week 2) — effort M, risiko rendah–sedang
8. **Buat komponen blade bersama** `resources/views/components/metric-card.blade.php` (props: label, value, delta, icon, color, optional action/event) dan `resources/views/components/status-badge.blade.php`. Gunakan di: `laporan-pemasukan` (metrik & breakdown), `ringkasan-statistik-utama` (grid stat), kartu ringkasan `metode-pembayaran-chart`. Tujuan: satu skala font/spacing/sumber warna. Effort M. Risiko: sedang — refactor visual bertahap per halaman.
9. **Konsistensi ikon**: ganti semua emoji (📊💰🕌💚📦🔍 dst) di `ringkasan-statistik-utama`, `laporan-pemasukan`, `laporan-perubahan-dana`, `analytics-modal` dengan `x-heroicon-o-*` / `<x-filament::icon>`. Emoji sebagai ikon data = AI-slop; maksimal 1 ikon per kartu. Effort M.
10. **Skala tipografi & density** (detail di bagian D): heading seksi `text-lg`, nilai statistik `text-2xl` (bukan 3xl/4xl), label `text-xs`, angka `tabular-nums`, padding kartu `p-4`, gap grid `gap-3/4`. Rapikan ALL CAPS label stat → sentence case (ubah string label di `RingkasanStatistikUtama.php:374-484`). Effort S–M.
11. **Format rupiah seragam**: semua `Rp {{ number_format(...) }}` tetap, tapi cegah duplikat "Rp" (mis. detail-donasi-modal sudah benar; `analytics-modal` campur `Rp 12,5M` — samakan format penuh). Effort S.
12. **Modal → komponen Filament**: ganti 3 modal hand-rolled di `ringkasan-statistik-utama.blade.php:442-509` dan `metode-pembayaran-chart.blade.php:269-362, 364-470` dengan `<x-filament::modal wire:model="showDetailDonasiModal">` (+ `x-filament::modal`). Benefit: aksesibilitas, ESC/overlay close, dark mode, ukuran konsisten. Effort M. Risiko: sedang — wiring Livewire harus diuji.
13. **Progress bar dekoratif**: di kartu ringkasan `metode-pembayaran-chart` (bar selalu 100%) → hapus; pertahankan bar persentase nyata di tabel metode. Effort S.
14. **Keputusan widget dashboard**: set `TrenDonasiChart::$isDiscovered = false` (atau jadikan widget dashboard resmi) dan sama untuk `HakAmilOverviewWidget`. Effort S. Risiko: perubahan isi dashboard — konfirmasi ke user sebelum eksekusi.

### PRIORITAS 3 — Drill-down menyeluruh (Week 3) — effort L, risiko sedang
15. **`RingkasanStatistikUtama`: semua kartu klikabel.**
    - PHP: setiap item stat sudah punya slot `clickable`/`action` — jadikan generik: setiap kategori (infaq, sedekah, csr, dskl, hak amil, penyaluran program) membuka panel detail ringkas per jenis/kategori (pola yang sudah ada untuk zakat/barang), dan angka utama membuka **modal daftar transaksi**.
    - Blade: ganti 3 blok `@if(str_contains($stat['label']...))` (blade:126-165) dengan satu render dari `$stat['action']`; tombol "Lihat Selengkapnya" diganti seluruh kartu `wire:click` + `role="button"`/`tabindex` + focus ring.
    - `wire:key` per kartu → `stat-{{ $stat['label'] }}`. Effort M.
16. **Modal detail transaksi → Livewire Filament Table**: refaktor `detail-donasi-modal.blade.php` (fi-* tiruan) menjadi komponen Livewire table ala `DonasiDonaturListTable` (searchable, sortable, pagination, export) yang dirender di dalam modal. Ini membuang ~300 baris HTML rapuh dan memberi search/pagination gratis. Effort L. Risiko: sedang — data loader tetap memakai method `getDonasiByInfaqKategori` dll. yang ada (tidak menulis ulang logika).
17. **Laporan Pemasukan: metrik & breakdown klikabel.** Klik kartu "Total Zakat" / baris breakdown → set filter tabel detail (sumber dana / status) via `updatedInteractsWithTable`/`$this->tableFilters` + scroll ke tabel; atau buka modal berisi tabel terfilter. Klik "Pending (N)" → filter status pending. Effort M–L. Risiko: sedang (interaksi filter Livewire table harus diuji; jangan ubah query dasar).
18. **`TopDonaturWidget` analytics modal**: rapikan `analytics-modal.blade.php` (556 baris) dengan komponen kartu bersama (#8), hapus gradient/emoji, segmentasi Platinum–Regular pakai warna status standar; angka segmentasi klikabel → set filter kategori di tabel utama. Effort M.
19. **`FundraiserPerformanceWidget`**: ganti filter yang memutasi properti di `->query()` closure menjadi properti terkomputasi/`updated*` hook agar tidak dieksekusi berulang; bila `limit = 0` (Semua) aktifkan pagination default Filament (10–25/baris) daripada `paginated(false)`. Effort M. Risiko: sedang — perilaku tabel berubah (perlu konfirmasi UX: default tetap Top 10).

### PRIORITAS 4 — Native-isasi & responsif (Week 4) — effort M, risiko sedang
20. **Ganti chart Chart.js-CDN dengan ApexCharts native**: hapus Alpine+CDN loader di `tren-donasi-chart.blade.php` (sudah orphan); tambahkan widget ApexCharts `TrenDonasiChart` (atau turunan baru) ke `AnalisisData::getHeaderWidgets/getFooterWidgets` dengan filter periode & jenis donasi via `getFilters()` bawaan ChartWidget/ApexCharts. Benefit: hilang dependensi CDN runtime + `new Function()` parsing. Effort M.
21. **Responsif**: audit semua tabel custom — pastikan `overflow-x-auto` (laporan-pemasukan:251 sudah, periksa tabel metode & detail modal); tombol filter periode `flex-wrap` di mobile; `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4` seragam; date-picker custom (`ringkasan-statistik-utama:42-82`) boleh diganti DatePicker Filament di form `getFilters()`. Effort M.
22. **`credits.blade.php`**: pindahkan `<style>` ke utility Tailwind, modal changelog → `x-filament::modal`. Effort S. (low priority, boleh digeser)
23. **Regresi test**: buka semua halaman di light/dark, desktop/tablet/mobile, peran admin + petugas; pastikan angka ringkasan (`RingkasanStatistikUtama`, `LaporanPerubahanDana`, `ZakatStatsOverview`) identik sebelum & sesudah refactor (screenshot diff / bandingkan manual) — **tidak ada perubahan perhitungan**.

---

## D. STANDAR DESAIN YANG DIUSULKAN (acuan lintas halaman)

**Warna & status (ikat ke panel, bukan warna ad-hoc):**
- Primary: maroon `#800020` (panel) — untuk aksi & aksen heading. Tidak ada gradient biru/ungu dekoratif.
- Status transaksi: Verified/Positif = `success` (emerald), Pending = `warning` (amber), Rejected/Negatif/Defisit = `danger` (rose), Info/netral = `info` (blue) / `gray`.
- Badge & indikator: gunakan `<x-filament::badge color="...">` — bukan `span` custom.
- Hapus: emoji-ikon, progress bar dekoratif 100%, hover-lift `translateY`, animasi `fade-in`.

**Tipografi:**
- Heading halaman: bawaan Filament; heading seksi `text-base/lg font-semibold`; label kartu `text-xs font-medium`; nilai statistik `text-2xl font-bold tabular-nums` (maks `text-3xl` hanya untuk 1 angka hero per halaman); deskripsi `text-xs text-gray-500`.
- Label statistik sentence case (bukan ALL CAPS).

**Spacing & density:**
- Kartu: `p-4 rounded-lg shadow-sm ring-1 ring-gray-950/5` (ikuti gaya `fi-wi-stats-overview-card` yang sudah dipakai `top-donatur-ringkas-widget`); grid `gap-3` (dense) atau `gap-4`; section spacing antar-blok `space-y-4/6`, bukan `space-y-8`.
- Angka kanan-aligned + `tabular-nums`; satuan ("transaksi", "metode") `text-xs text-gray-400`.

**Format angka:** `Rp {{ number_format($n, 0, ',', '.') }}` di satu tempat (helper/component), tanpa singkatan "M/K" pada angka keputusan (boleh sebagai sub-teks sekunder).

**Pola drill-down (aturan: setiap angka agregat harus bisa dijelaskan):**
- Level 1 — kartu statistik klikabel (keseluruhan kartu, bukan tombol kecil) → **Level 2** panel/section breakdown per jenis/kategori (inline, bisa ditutup) → **Level 3** modal daftar transaksi = **Livewire Filament Table** (search, sort, pagination, export) dalam `<x-filament::modal>` / slide-over.
- Semua drill-down menghormati filter periode aktif dan menampilkan ringkasannya (jumlah transaksi + total) di header modal.
- Pola contoh yang sudah benar dan dipakai sebagai template: `TopDonaturWidget` (total klikabel → slide-over `donasi-donatur-list-table`) dan `FundraiserPerformanceWidget` (badge transaksi/donatur → slide-over Livewire table).

**Responsif:**
- Grid metrik: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`.
- Tabel: selalu dalam `overflow-x-auto`; di mobile kolom sekunder toggleable (sudah dipakai di TopDonaturWidget — pertahankan).
- Filter/baris aksi: `flex-wrap` dengan `gap-2`, tombol `size="sm"` di mobile.

**Anti-pattern yang dilarang mulai sekarang:** class Tailwind hasil interpolasi string, `!important` inline, `fi-*`/`fi-ta-*` class internal Filament ditulis manual, query DB di blade, `DOMContentLoaded` untuk elemen Livewire, emoji sebagai ikon UI.

---

## E. URUTAN EKSEKUSI (3–4 minggu)

| Minggu | Fase | Item | Output terukur |
|---|---|---|---|
| 1 | Cleanup & bug nyata | B (hapus 12 orphan) + C.2–C.7 | 0 artefak debug, 0 `!important`, warna stat tampil benar, Laporan Perubahan Dana tanpa CSS/JS custom, N+1 hilang |
| 2 | Standarisasi | C.8–C.14 | Komponen `metric-card`/`status-badge` dipakai di 3 halaman; semua emoji → Heroicon; modal pakai `x-filament::modal`; keputusan widget dashboard final |
| 3 | Drill-down | C.15–C.19 | Semua kartu stat di Analisis Data & Laporan Pemasukan klikabel; detail transaksi = Filament Table di modal |
| 4 | Native-asi & QA | C.20–C.23 | Chart tanpa CDN manual; audit responsif; regresi angka diverifikasi |

Titik keputusan user sebelum eksekusi: (a) apakah `TrenDonasiChart` & `HakAmilOverviewWidget` memang sengaja di dashboard; (b) pagination vs "Semua" di FundraiserPerformanceWidget; (c) preferensi warna aksen Laporan Perubahan Dana (maroon panel vs netral).
