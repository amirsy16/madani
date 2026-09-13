<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;
use Filament\Tables\Table;
use App\Models\Donasi;
use App\Models\ProgramPenyaluran;
use App\Models\PenggunaanHakAmil;
use App\Models\JenisDonasi;
use App\Models\SumberDanaPenyaluran;
use App\Observers\InvalidatesStatsCache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->paginationPageOptions([25, 50, 100, 250])
                ->defaultPaginationPageOption(25);
        });

        foreach ([Donasi::class, ProgramPenyaluran::class, PenggunaanHakAmil::class, JenisDonasi::class, SumberDanaPenyaluran::class] as $model) {
            $model::observe(InvalidatesStatsCache::class);
        }
        // if(config('app.env') === 'local')
        // {
        //     URL::forceScheme('https');
            
        //     // Handle ngrok URLs dynamically
        //     if (request()->hasHeader('x-forwarded-host')) {
        //         $host = request()->header('x-forwarded-host');
        //         URL::forceRootUrl('https://' . $host);
        //     }
        // }

        
    }
}
