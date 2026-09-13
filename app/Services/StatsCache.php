<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache untuk statistik dashboard/laporan.
 *
 * Driver database tidak mendukung cache tags, jadi invalidasi dilakukan
 * lewat version key: observer menaikkan version saat data berubah,
 * kunci lama ditinggal kedaluwarsa sendiri.
 */
class StatsCache
{
    public const TTL = 600; // 10 menit

    public static function remember(string $key, Closure $callback, ?int $ttl = null): mixed
    {
        $version = Cache::rememberForever('stats:version', fn () => 1);

        return Cache::remember("stats:v{$version}:{$key}", $ttl ?? self::TTL, $callback);
    }

    public static function flush(): void
    {
        $version = (int) Cache::get('stats:version', 1);

        Cache::put('stats:version', $version + 1);
    }
}
