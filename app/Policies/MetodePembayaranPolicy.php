<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MetodePembayaran;
use Illuminate\Auth\Access\HandlesAuthorization;

class MetodePembayaranPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_metode::pembayaran');
    }

    public function view(User $user, MetodePembayaran $metodePembayaran): bool
    {
        return $user->can('view_metode::pembayaran');
    }

    public function create(User $user): bool
    {
        return $user->can('create_metode::pembayaran');
    }

    public function update(User $user, MetodePembayaran $metodePembayaran): bool
    {
        return $user->can('update_metode::pembayaran');
    }

    public function delete(User $user, MetodePembayaran $metodePembayaran): bool
    {
        return $user->can('delete_metode::pembayaran');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_metode::pembayaran');
    }

    public function forceDelete(User $user, MetodePembayaran $metodePembayaran): bool
    {
        return $user->can('force_delete_metode::pembayaran');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_metode::pembayaran');
    }

    public function restore(User $user, MetodePembayaran $metodePembayaran): bool
    {
        return $user->can('restore_metode::pembayaran');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_metode::pembayaran');
    }

    public function replicate(User $user, MetodePembayaran $metodePembayaran): bool
    {
        return $user->can('replicate_metode::pembayaran');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_metode::pembayaran');
    }
}
