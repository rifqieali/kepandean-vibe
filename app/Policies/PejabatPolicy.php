<?php

namespace App\Policies;

use App\Models\Pejabat;
use App\Models\User;
use App\Support\Filament\DesaScoping;

/**
 * Revisi peran: Pejabat bukan konten editor. Hanya admin_desa
 * (satu desa) dan developer (lintas desa) yang boleh akses.
 */
class PejabatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDesa() || $user->isDeveloper();
    }

    public function view(User $user, Pejabat $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdminDesa() || $user->isDeveloper();
    }

    public function update(User $user, Pejabat $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function delete(User $user, Pejabat $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminDesa() || $user->isDeveloper();
    }
}
