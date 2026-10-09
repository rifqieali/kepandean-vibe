<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Filament\DesaScoping;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDesa() || $user->isDeveloper();
    }

    public function view(User $user, User $model): bool
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

    public function update(User $user, User $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return true;
        }

        return $user->isAdminDesa() && DesaScoping::sameDesa($user, $model->desa_id);
    }

    public function delete(User $user, User $model): bool
    {
        if (DesaScoping::canSeeAllDesa($user)) {
            return $user->isNot($model);
        }

        return $user->isAdminDesa()
            && DesaScoping::sameDesa($user, $model->desa_id)
            && $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminDesa() || $user->isDeveloper();
    }
}
