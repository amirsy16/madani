<?php

namespace App\Support;

use App\Services\NotifikasiWa;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Notifikasi WA (Fonnte) untuk error aplikasi tak terduga (500-an) —
 * dipasang lewat hook reportable di bootstrap/app.php.
 *
 * Error "wajar" dari input user/bot (validasi, 404, 403, auth) sengaja
 * dilewati agar WA tidak berisik, dan maksimal 10 pesan per jam: sisanya
 * diabaikan dan disebutkan ringkasnya di pesan berikutnya.
 */
class NotifikasiError
{
    private const MAKS_PER_JAM = 10;

    /**
     * Jumlah error yang terabaikan sejak pesan WA terakhir terkirim.
     */
    private static int $terabaikan = 0;

    public static function laporkan(Throwable $e): void
    {
        if (self::harusDilewati($e)) {
            return;
        }

        try {
            $terkirim = RateLimiter::attempt(
                'wa-notifikasi-error',
                self::MAKS_PER_JAM,
                function () use ($e): void {
                    $catatan = self::$terabaikan > 0
                        ? "\n(".self::$terabaikan.' error lain sebelumnya diabaikan.)'
                        : '';
                    self::$terabaikan = 0;

                    NotifikasiWa::kirim(self::susunPesan($e, $catatan));
                },
                3600
            );

            if (! $terkirim) {
                self::$terabaikan++;
            }
        } catch (Throwable) {
            // Pelaporan error tidak boleh gagal karena notifikasinya sendiri.
        }
    }

    /**
     * True bila error berasal dari perilaku wajar user/bot, bukan kerusakan aplikasi.
     */
    private static function harusDilewati(Throwable $e): bool
    {
        if ($e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof ModelNotFoundException) {
            return true;
        }

        return $e instanceof HttpException && $e->getStatusCode() < 500;
    }

    private static function susunPesan(Throwable $e, string $catatan): string
    {
        $pesan = str_replace(["\r", "\n"], ' ', $e->getMessage());
        $pesan = mb_substr(trim($pesan) !== '' ? $pesan : '(tanpa pesan)', 0, 160);
        $url = app()->runningInConsole() ? 'console' : (string) request()->fullUrl();
        $berkas = mb_substr($e->getFile(), strlen(base_path()) + 1);

        return "🚨 *Error Aplikasi*\n".
            class_basename($e).": {$pesan}\n".
            "{$berkas}:{$e->getLine()}\n".
            "URL: {$url}\n".
            NotifikasiWa::stempelWaktu().
            $catatan;
    }
}
