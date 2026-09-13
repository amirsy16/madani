# Madani - Sistem Informasi Pengelolaan Dana ZIS

Sistem pengelolaan donatur, fundraiser, dan penyaluran dana Zakat, Infaq, dan Sedekah.

## Fitur Baru: Seeder Gabungan Januari-Mei 2025

Telah ditambahkan seeder gabungan untuk mengimpor data donasi dari Januari sampai Mei 2025. Fitur-fitur seeder ini meliputi:

- Import data 5 bulan sekaligus dalam satu proses
- Anti-duplikasi nomor kwitansi dengan prefix bulan dan suffix unik
- Deteksi pintar untuk jenis donor (individu/organisasi/anonim)
- Deteksi gender berdasarkan nama dan prefix (Bapak, Ibu, dll)
- Statistik lengkap per bulan, per tipe donasi, dan tipe donor

Untuk informasi lebih lanjut dan cara penggunaan, baca [DOKUMENTASI_SEEDER_GABUNGAN.md](DOKUMENTASI_SEEDER_GABUNGAN.md).

### Cara Menjalankan Seeder Gabungan

#### Windows:
```
run_complete_seeder.bat
```

#### PHP:
```
php run_complete_seeder.php
```

#### Artisan Command:
```
php artisan db:seed --class=DonasiJanuariSampaiMei2025Seeder
```

## Fitur Sistem

- Pengelolaan Donatur
- Pencatatan Donasi (Zakat, Infaq Terikat, Infaq Tidak Terikat, DSKL)
- Manajemen Fundraiser
- Pengelolaan Penyaluran Dana
- Dashboard Analitik
- Laporan dan Statistik
- Manajemen User dan Hak Akses

## Petunjuk Instalasi

```bash
# Clone repository
git clone [url-repository]

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Compile assets
npm run dev

# Run server
php artisan serve
```

## Modul dan Dokumentasi

- [FITUR_TOP_DONATUR_WIDGET.md](FITUR_TOP_DONATUR_WIDGET.md) - Widget Top Donatur
- [FITUR_PDF_TANPA_GD_EXTENSION.md](FITUR_PDF_TANPA_GD_EXTENSION.md) - Generasi PDF Tanpa GD Extension
- [MIGRASI_PDF_DOKUMENTASI.md](MIGRASI_PDF_DOKUMENTASI.md) - Dokumentasi Migrasi PDF
- [SISTEM_HAK_AMIL_DOKUMENTASI.md](SISTEM_HAK_AMIL_DOKUMENTASI.md) - Sistem Hak Amil
- [DOKUMENTASI_SEEDER_GABUNGAN.md](DOKUMENTASI_SEEDER_GABUNGAN.md) - Seeder Gabungan Januari-Mei 2025
