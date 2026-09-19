<div align="center">

<img src="public/images/LOGOSI.png" alt="Logo Madani" width="110">

# SIMADI — Sistem Informasi Manajemen Dana ZIS

**Sistem manajemen pengumpulan dan penyaluran Zakat, Infaq, Sedekah & Dana CSR — LAZ Insan Madani Jambi**

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![Filament](https://img.shields.io/badge/Filament-3-F3AE4B?style=flat-square)](https://filamentphp.com)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Live](https://img.shields.io/badge/Status-Production-10B981?style=flat-square)](https://simadi.org)

</div>

---

## Tentang

SIMADI (sebelumnya madaniX) adalah aplikasi manajemen dana ZIS yang mencatat penghimpunan dari donatur hingga penyaluran ke penerima manfaat — lengkap dengan peran fundraiser, hak amil, dan pelaporan keuangan real-time.

## Fitur Utama

| Modul | Kemampuan |
|---|---|
| 🧕 **Manajemen Donatur** | Profil donatur, kode otomatis, kategori loyalitas (Platinum–Bronze), riwayat & donasi anonim |
| 💰 **Penghimpunan Dana** | Zakat (maal/fitrah), infaq terikat & tidak terikat, sedekah, wakaf, DSKL, donasi barang, dan CSR |
| 🎯 **Fundraiser** | Target donasi, performa fundraiser, atribusi sumber donasi |
| 📤 **Penyaluran** | Program penyaluran, penyaluran langsung, penerima manfaat (asnaf), dana non-halal terpisah |
| 👥 **Hak Amil** | Persentase hak amil per sumber dana, pencatatan penggunaan, sisa saldo |
| 📊 **Pusat Statistik** | Dashboard analitik ApexCharts, filter periode, modal breakdown per kategori, ekspor data |
| 🔐 **Kontrol Akses** | Manajemen peran & izin (Spatie Permission), audit trail aktivitas |

## Teknologi

- **Laravel 11** — kerangka aplikasi
- **Filament 3** — panel admin & widget
- **ApexCharts** — visualisasi data
- **MySQL** — basis data
- **DomPDF** — dokumen & laporan
- **Tailwind CSS** — tema kustom light/dark

## Persyaratan

- PHP ≥ 8.4 (ekstensi: `pdo_mysql`, `mbstring`, `gd`, `zip`)
- Composer, Node.js & NPM
- MySQL ≥ 8.0

## Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/amirsy16/madani.git
cd madani

# 2. Dependensi
composer install
npm install && npm run build

# 3. Konfigurasi
cp .env.example .env
php artisan key:generate

# 4. Database (migrasi skema; seeder data riil tidak disertakan)
php artisan migrate

# 5. Optimasi
php artisan storage:link
```

Akun pengelola dibuat melalui provisioning admin — tidak disertakan kredensial default di repositori ini.

## Pengujian

```bash
php artisan test
```

## Catatan Keamanan

- Direktori `database/seeders/` sengaja **tidak dipublikasikan** karena berisi data nyata donatur.
- Data sensitif (`.env`, log, berkas unggahan) berada di luar version control.

---

<div align="center">

**LAZ Insan Madani Jambi** · [simadi.org](https://simadi.org)

</div>
