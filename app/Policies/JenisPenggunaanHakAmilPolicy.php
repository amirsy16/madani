<?php

namespace App\Policies;

use App\Models\JenisPenggunaanHakAmil;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Master data 'Jenis Penggunaan Hak Amil' hanya dikelola peran berizin (super_admin lewat
 * Gate::before); permission mengikuti pola Shield: jenis_penggunaan_hak_amil.
 */
class JenisPenggunaanHakAmilPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_jenis_penggunaan_hak_amil');
    }

    public function view(User $user, JenisPenggunaanHakAmil $jenisPenggunaanHakAmil): bool
    {
        return $user->can('view_jenis_penggunaan_hak_amil');
    }

    public function create(User $user): bool
    {
        return $user->can('create_jenis_penggunaan_hak_amil');
    }

    public function update(User $user, JenisPenggunaanHakAmil $jenisPenggunaanHakAmil): bool
    {
        return $user->can('update_jenis_penggunaan_hak_amil');
    }

    public function delete(User $user, JenisPenggunaanHakAmil $jenisPenggunaanHakAmil): bool
    {
        return $user->can('delete_jenis_penggunaan_hak_amil');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_jenis_penggunaan_hak_amil');
    }
}
