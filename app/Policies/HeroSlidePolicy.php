<?php

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;
use App\Support\Filament\DesaScoping;

/**
 * Issue #18 checklist 1b: slide hero hanya dikelola admin_desa
 * (satu desa) dan developer (lintas desa).
 */
class HeroSlidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDesa() || $user->isDeveloper();
    }

    public function view(User $user, HeroSlide $model): bool
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

    public function update(User $user, HeroSlide $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function delete(User $user, HeroSlide $model): bool
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
