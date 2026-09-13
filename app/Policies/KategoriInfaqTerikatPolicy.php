<?php

namespace App\Policies;

use App\Models\User;
use App\Models\KategoriInfaqTerikat;
use Illuminate\Auth\Access\HandlesAuthorization;

class KategoriInfaqTerikatPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_kategori::infaq::terikat');
    }

    public function view(User $user, KategoriInfaqTerikat $kategoriInfaqTerikat): bool
    {
        return $user->can('view_kategori::infaq::terikat');
    }

    public function create(User $user): bool
    {
        return $user->can('create_kategori::infaq::terikat');
    }

    public function update(User $user, KategoriInfaqTerikat $kategoriInfaqTerikat): bool
    {
        return $user->can('update_kategori::infaq::terikat');
    }

    public function delete(User $user, KategoriInfaqTerikat $kategoriInfaqTerikat): bool
    {
        return $user->can('delete_kategori::infaq::terikat');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_kategori::infaq::terikat');
    }

    public function forceDelete(User $user, KategoriInfaqTerikat $kategoriInfaqTerikat): bool
    {
        return $user->can('force_delete_kategori::infaq::terikat');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_kategori::infaq::terikat');
    }

    public function restore(User $user, KategoriInfaqTerikat $kategoriInfaqTerikat): bool
    {
        return $user->can('restore_kategori::infaq::terikat');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_kategori::infaq::terikat');
    }

    public function replicate(User $user, KategoriInfaqTerikat $kategoriInfaqTerikat): bool
    {
        return $user->can('replicate_kategori::infaq::terikat');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_kategori::infaq::terikat');
    }
}
