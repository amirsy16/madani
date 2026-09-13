<?php

namespace App\Observers;

use App\Services\StatsCache;

/**
 * Invalidate cache statistik ketika data sumber statistik berubah.
 * Dipasang untuk: Donasi, ProgramPenyaluran, PenggunaanHakAmil,
 * JenisDonasi, SumberDanaPenyaluran.
 */
class InvalidatesStatsCache
{
    public function saved(): void
    {
        StatsCache::flush();
    }

    public function deleted(): void
    {
        StatsCache::flush();
    }
}
