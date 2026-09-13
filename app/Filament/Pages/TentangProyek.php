<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TentangProyek extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Tentang Proyek';

    protected static ?string $navigationGroup = 'Lainnya';

    protected static ?string $title = 'SIMADI v2.0 — Apa yang Baru';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.tentang-proyek';
}
