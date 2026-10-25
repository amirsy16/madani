<?php

namespace App\Policies;

use App\Models\KategoriDanaNonHalal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Master data 'Kategori Dana Non-Halal' hanya dikelola peran berizin (super_admin lewat
 * Gate::before); permission mengikuti pola Shield: kategori_dana_non_halal.
 */
class KategoriDanaNonHalalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_kategori_dana_non_halal');
    }

    public function view(User $user, KategoriDanaNonHalal $kategoriDanaNonHalal): bool
    {
        return $user->can('view_kategori_dana_non_halal');
    }

    public function create(User $user): bool
    {
        return $user->can('create_kategori_dana_non_halal');
    }

    public function update(User $user, KategoriDanaNonHalal $kategoriDanaNonHalal): bool
    {
        return $user->can('update_kategori_dana_non_halal');
    }

    public function delete(User $user, KategoriDanaNonHalal $kategoriDanaNonHalal): bool
    {
        return $user->can('delete_kategori_dana_non_halal');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_kategori_dana_non_halal');
    }
}
