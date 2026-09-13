<?php

namespace Tests\Unit;

use App\Services\StatsCache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * StatsCache memakai store 'array' di testing (lihat phpunit.xml),
 * sehingga tidak butuh database.
 */
class StatsCacheTest extends TestCase
{
    #[Test]
    public function remember_menyimpan_dan_mengembalikan_nilai_sama_tanpa_eksekusi_ulang(): void
    {
        $executions = 0;

        $callback = function () use (&$executions) {
            $executions++;

            return ['total_donasi' => 5_000_000];
        };

        $pertama = StatsCache::remember('uji:dashboard', $callback);
        $kedua = StatsCache::remember('uji:dashboard', $callback);
        $ketiga = StatsCache::remember('uji:dashboard', $callback);

        $this->assertSame(1, $executions, 'Callback hanya boleh dieksekusi sekali untuk key yang sama.');
        $this->assertSame(['total_donasi' => 5_000_000], $pertama);
        $this->assertSame($pertama, $kedua);
        $this->assertSame($pertama, $ketiga);
    }

    #[Test]
    public function flush_menaikkan_versi_dan_memicu_eksekusi_ulang(): void
    {
        $executions = 0;

        $callback = function () use (&$executions) {
            return ++$executions;
        };

        $this->assertSame(1, StatsCache::remember('uji:flush', $callback));
        $this->assertSame(1, StatsCache::remember('uji:flush', $callback), 'Nilai cache harus dipakai ulang sebelum flush.');

        StatsCache::flush();

        $this->assertSame(2, StatsCache::remember('uji:flush', $callback), 'Setelah flush, callback harus dieksekusi ulang.');

        StatsCache::flush();

        $this->assertSame(3, StatsCache::remember('uji:flush', $callback), 'Flush kedua harus menaikkan versi lagi.');
    }

    #[Test]
    public function key_yang_berbeda_tidak_saling_mengganggu(): void
    {
        $executionsA = 0;
        $executionsB = 0;

        $callbackA = function () use (&$executionsA) {
            return 'A'.++$executionsA;
        };
        $callbackB = function () use (&$executionsB) {
            return 'B'.++$executionsB;
        };

        $this->assertSame('A1', StatsCache::remember('uji:a', $callbackA));
        $this->assertSame('B1', StatsCache::remember('uji:b', $callbackB));

        // Callback A tidak dieksekusi ulang karena callback B dijalankan.
        $this->assertSame('A1', StatsCache::remember('uji:a', $callbackA));
        $this->assertSame(1, $executionsA);
        $this->assertSame(1, $executionsB);
    }

    #[Test]
    public function flush_pada_satu_key_mengubah_semua_key(): void
    {
        $executions = 0;

        $callback = function () use (&$executions) {
            return ++$executions;
        };

        StatsCache::remember('uji:x', $callback);
        StatsCache::remember('uji:y', $callback);

        StatsCache::flush();

        // Versi baru => kedua key lama kedaluwarsa, keduanya dieksekusi ulang.
        $this->assertSame(3, StatsCache::remember('uji:x', $callback));
        $this->assertSame(4, StatsCache::remember('uji:y', $callback));
    }
}
