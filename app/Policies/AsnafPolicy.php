<?php

namespace App\Policies;

use App\Models\Asnaf;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Master data 'Asnaf' hanya dikelola peran berizin (super_admin lewat
 * Gate::before); permission mengikuti pola Shield: asnaf.
 */
class AsnafPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_asnaf');
    }

    public function view(User $user, Asnaf $asnaf): bool
    {
        return $user->can('view_asnaf');
    }

    public function create(User $user): bool
    {
        return $user->can('create_asnaf');
    }

    public function update(User $user, Asnaf $asnaf): bool
    {
        return $user->can('update_asnaf');
    }

    public function delete(User $user, Asnaf $asnaf): bool
    {
        return $user->can('delete_asnaf');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_asnaf');
    }
}
