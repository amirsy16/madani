<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    /**
     * Batasi percobaan login gagal: maksimal 5 per menit per email+IP
     * untuk memperlambat brute force (AK-08).
     */
    public function authenticate(): ?LoginResponse
    {
        $kunci = 'login:'.strtolower(trim((string) ($this->data['email'] ?? ''))).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'data.email' => 'Terlalu banyak percobaan gagal. Coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.',
            ]);
        }

        try {
            $hasil = parent::authenticate();
        } catch (ValidationException $e) {
            RateLimiter::hit($kunci, 60);

            throw $e;
        }

        RateLimiter::clear($kunci);

        return $hasil;
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getSubHeading(): ?string
    {
        return null;
    }
}
