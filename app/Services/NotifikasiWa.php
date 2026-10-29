<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pengirim pesan WhatsApp via gateway Fonnte (fonnte.com) untuk notifikasi
 * operasional: donasi baru, login panel, dan error aplikasi.
 *
 * Pengiriman dilakukan langsung saat kejadian (tanpa queue — volume app ini
 * rendah) dan PASTI tidak pernah melempar error: kegagalan jaringan/Fonnte
 * hanya dicatat di log sehingga alur bisnis (simpan donasi, login) tidak
 * pernah terganggu.
 *
 * Konfigurasi di .env (dibaca config/services.php):
 * - FONNTE_TOKEN     : device token dari dashboard fonnte.com
 * - WA_NOTIF_TARGET  : nomor penerima, format 62…
 * - WA_NOTIF_ENABLED : matikan sementara tanpa hapus token (default true)
 * Bila belum lengkap, service ini no-op — lokal/testing otomatis senyap.
 */
class NotifikasiWa
{
    private const ENDPOINT = 'https://api.fonnte.com/send';

    /**
     * Tahan seluruh pengiriman sementara waktu (mis. selama impor massal
     * berlangsung agar puluhan baris tidak memicu puluhan pesan).
     */
    public static bool $ditahan = false;

    /**
     * Kirim pesan WA ke penerima terdaftar. Aman dipanggil di mana pun —
     * tidak pernah melempar error.
     */
    public static function kirim(string $pesan): bool
    {
        if (self::$ditahan) {
            return false;
        }

        $token = trim((string) config('services.fonnte.token'));
        $target = trim((string) config('services.fonnte.target'));

        if (! config('services.fonnte.enabled') || $token === '' || $target === '') {
            return false;
        }

        try {
            $respons = Http::withHeaders(['Authorization' => $token])
                ->asForm()
                ->timeout(10)
                ->post(self::ENDPOINT, [
                    'target' => $target,
                    'message' => $pesan,
                ]);

            // Fonnte membalas HTTP 200 walau gagal (mis. "unknown token") —
            // kesuksesan diputuskan dari kolom `status` pada body, bukan
            // kode HTTP semata.
            if (! $respons->successful() || $respons->json('status') !== true) {
                Log::warning('Notifikasi WA gagal terkirim via Fonnte', [
                    'status_http' => $respons->status(),
                    'status_fonnte' => $respons->json('status'),
                    'alasan' => $respons->json('reason'),
                    'respons' => mb_substr($respons->body(), 0, 300),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Notifikasi WA bermasalah via Fonnte: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Baris stempel waktu berbahasa Indonesia untuk pesan notifikasi.
     */
    public static function stempelWaktu(): string
    {
        return now()->locale('id')->translatedFormat('d M Y H:i');
    }
}
