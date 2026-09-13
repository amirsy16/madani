<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Credits extends Page
{
    // Sembunyikan dari navigasi sidebar
    protected static bool $shouldRegisterNavigation = false;

    // Judul halaman
    protected static ?string $title = 'Tentang Proyek & Tim Pengembang';

    // Slug URL
    protected static ?string $slug = 'credits';

    // File view Blade yang akan digunakan
    protected static string $view = 'filament.pages.credits';
}
