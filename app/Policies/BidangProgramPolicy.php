<?php

namespace App\Policies;

use App\Models\BidangProgram;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Master data 'Bidang Program' hanya dikelola peran berizin (super_admin lewat
 * Gate::before); permission mengikuti pola Shield: bidang_program.
 */
class BidangProgramPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_bidang_program');
    }

    public function view(User $user, BidangProgram $bidangProgram): bool
    {
        return $user->can('view_bidang_program');
    }

    public function create(User $user): bool
    {
        return $user->can('create_bidang_program');
    }

    public function update(User $user, BidangProgram $bidangProgram): bool
    {
        return $user->can('update_bidang_program');
    }

    public function delete(User $user, BidangProgram $bidangProgram): bool
    {
        return $user->can('delete_bidang_program');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_bidang_program');
    }
}
