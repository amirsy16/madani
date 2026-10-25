<?php

namespace App\Policies;

use App\Models\Pekerjaan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Master data 'Pekerjaan' hanya dikelola peran berizin (super_admin lewat
 * Gate::before); permission mengikuti pola Shield: pekerjaan.
 */
class PekerjaanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_pekerjaan');
    }

    public function view(User $user, Pekerjaan $pekerjaan): bool
    {
        return $user->can('view_pekerjaan');
    }

    public function create(User $user): bool
    {
        return $user->can('create_pekerjaan');
    }

    public function update(User $user, Pekerjaan $pekerjaan): bool
    {
        return $user->can('update_pekerjaan');
    }

    public function delete(User $user, Pekerjaan $pekerjaan): bool
    {
        return $user->can('delete_pekerjaan');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_pekerjaan');
    }
}
