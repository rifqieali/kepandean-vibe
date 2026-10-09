<?php

namespace App\Policies;

use App\Models\Pengumuman;
use App\Models\User;
use App\Support\Filament\DesaScoping;

/**
 * Issue #17: meniru BeritaPolicy (#16) — editor hanya boleh menulis draft,
 * tidak boleh menghapus apa pun. Hapus = hak publish (admin_desa/techade).
 */
class PengumumanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEditor() || $user->isAdminDesa() || $user->isTechade();
    }

    public function view(User $user, Pengumuman $model): bool
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

    public function update(User $user, Pengumuman $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        // Publish tetap dikunci terpisah via gate publish-content di halaman Create/Edit.
        return ($user->isEditor() || $user->isAdminDesa()) && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function delete(User $user, Pengumuman $model): bool
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
