<?php

namespace App\Support;

use App\Models\Fundraiser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Aturan pembatasan data antar peran (temuan AK-01): pengguna yang
 * satu-satunya perannya Fundraiser hanya boleh melihat donasi yang ia
 * kumpulkan sendiri; super_admin dan peran lainnya (Admin, Pengelola
 * Keuangan, Pimpinan) melihat seluruh donasi lembaga.
 *
 * Berbasis nama peran — bukan permission — karena permission widget/page
 * belum tentu tersinkron dengan DB produksi (semua seeder bersifat lokal).
 */
class AksesPanel
{
    /**
     * Apakah user boleh melihat donasi seluruh lembaga.
     */
    public static function lihatSemuaDonasi(?User $user): bool
    {
        if ($user === null) {
            return false;
        }
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->roles()->whereNotIn('name', ['Fundraiser'])->exists();
    }

    /**
     * ID fundraiser yang terhubung ke akun user (null bila tidak ada).
     */
    public static function fundraiserIdUser(User $user): ?int
    {
        $id = Fundraiser::where('user_id', $user->id)->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * Terapkan pembatasan pada query donasi: semua baris untuk peran
     * lengkap, atau hanya donasi milik fundraiser user.
     */
    public static function terapkanScopeDonasi(Builder $query, ?User $user): Builder
    {
        if (static::lihatSemuaDonasi($user)) {
            return $query;
        }

        // -1: user tanpa profil fundraiser tidak melihat donasi apa pun.
        return $query->where('donasis.fundraiser_id', static::fundraiserIdUser($user) ?? -1);
    }
}
