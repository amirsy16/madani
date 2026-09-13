<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JenisDonasi;
use Illuminate\Auth\Access\HandlesAuthorization;

class JenisDonasiPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_jenis::donasi');
    }

    public function view(User $user, JenisDonasi $jenisDonasi): bool
    {
        return $user->can('view_jenis::donasi');
    }

    public function create(User $user): bool
    {
        return $user->can('create_jenis::donasi');
    }

    public function update(User $user, JenisDonasi $jenisDonasi): bool
    {
        return $user->can('update_jenis::donasi');
    }

    public function delete(User $user, JenisDonasi $jenisDonasi): bool
    {
        return $user->can('delete_jenis::donasi');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_jenis::donasi');
    }

    public function forceDelete(User $user, JenisDonasi $jenisDonasi): bool
    {
        return $user->can('force_delete_jenis::donasi');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_jenis::donasi');
    }

    public function restore(User $user, JenisDonasi $jenisDonasi): bool
    {
        return $user->can('restore_jenis::donasi');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_jenis::donasi');
    }

    public function replicate(User $user, JenisDonasi $jenisDonasi): bool
    {
        return $user->can('replicate_jenis::donasi');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_jenis::donasi');
    }
}
