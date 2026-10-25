<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\JenisDonasi;
use App\Models\PenggunaanHakAmil;
use App\Models\ProgramPenyaluran;
use App\Models\SumberDanaPenyaluran;
use App\Observers\InvalidatesStatsCache;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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

        foreach ([Donasi::class, Donatur::class, ProgramPenyaluran::class, PenggunaanHakAmil::class, JenisDonasi::class, SumberDanaPenyaluran::class] as $model) {
            $model::observe(InvalidatesStatsCache::class);
        }

        // Setiap activity yang lahir dari request web menyimpan IP & user-agent
        // di properties untuk keperluan audit keamanan (halaman Log Aktivitas).
        Activity::creating(function (Activity $activity): void {
            if (app()->runningInConsole()) {
                return;
            }

            $activity->properties = collect($activity->properties ?? [])->merge([
                'ip_address' => request()->ip(),
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 255),
            ]);
        });
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
