<?php

namespace App\Policies;

use App\Models\Berita;
use App\Models\User;
use App\Support\Filament\DesaScoping;

/**
 * Issue #16 (revisi): editor hanya boleh menulis draft Berita,
 * tidak boleh menghapus apa pun. Hapus = hak publish (admin_desa/techade).
 */
class BeritaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEditor() || $user->isAdminDesa() || $user->isTechade();
    }

    public function view(User $user, Berita $model): bool
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

    public function update(User $user, Berita $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        // Publish tetap dikunci terpisah via gate publish-content di halaman Create/Edit.
        return ($user->isEditor() || $user->isAdminDesa()) && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function delete(User $user, Berita $model): bool
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
