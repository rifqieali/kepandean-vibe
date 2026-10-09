<?php

namespace App\Policies;

use App\Models\Statistik;
use App\Models\User;
use App\Support\Filament\DesaScoping;

/**
 * Revisi peran: Statistik bukan konten editor. Hanya admin_desa
 * (satu desa) dan techade (lintas desa) yang boleh akses.
 */
class StatistikPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDesa() || $user->isTechade();
    }

    public function view(User $user, Statistik $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdminDesa() || $user->isTechade();
    }

    public function update(User $user, Statistik $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function delete(User $user, Statistik $model): bool
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
