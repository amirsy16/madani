<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\NotifikasiWa;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;

/**
 * Notifikasi WA (Fonnte) untuk aktivitas login panel admin — berhasil
 * maupun gagal (indikasi orang coba-coba). Didaftarkan manual di
 * AppServiceProvider untuk event autentikasi bawaan Illuminate.
 */
class NotifikasiLogin
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $peran = $user->getRoleNames()->implode(', ');
        if ($peran === '') {
            $peran = 'tanpa peran';
        }

        NotifikasiWa::kirim(
            "✅ *Login Panel*\n".
            "{$user->name} — {$peran}\n".
            'IP '.(request()->ip() ?? '-')."\n".
            NotifikasiWa::stempelWaktu()
        );
    }

    public function handleGagal(Failed $event): void
    {
        $email = trim((string) ($event->credentials['email'] ?? ''));
        $identitas = $email !== '' ? $email : (request()->ip() ?? 'tanpa-ip');

        // Batasi spam WA saat dicoba-coba: maksimal 5 pemberitahuan
        // per identitas per jam (pola sama dengan NotifikasiError).
        \Illuminate\Support\Facades\RateLimiter::attempt(
            'wa-login-gagal:'.md5(strtolower($identitas)),
            5,
            function () use ($email): void {
                NotifikasiWa::kirim(
                    "⚠️ *Login Gagal*\n".
                    ($email !== '' ? "Email: {$email}\n" : '').
                    'IP '.(request()->ip() ?? '-')."\n".
                    NotifikasiWa::stempelWaktu()
                );
            },
            3600
        );
    }
}
