<?php

namespace App\Policies;

use App\Models\Kategori;
use App\Models\User;
use App\Support\Filament\DesaScoping;

/**
 * Issue #16 (revisi): sama seperti Berita — editor boleh kelola
 * kategori desanya, tidak boleh menghapus.
 */
class KategoriPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEditor() || $user->isAdminDesa() || $user->isTechade();
    }

    public function view(User $user, Kategori $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return ($user->isEditor() || $user->isAdminDesa()) && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function create(User $user): bool
    {
        return $user->isEditor() || $user->isAdminDesa() || $user->isTechade();
    }

    public function update(User $user, Kategori $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return ($user->isEditor() || $user->isAdminDesa()) && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function delete(User $user, Kategori $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminDesa() || $user->isTechade();
    }
}
