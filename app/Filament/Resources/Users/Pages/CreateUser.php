<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;

class CreateUser extends ScopedCreatePage
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        abort_unless($user instanceof User && ($user->isAdminDesa() || $user->isTechade()), 403);

        // Hanya techade yang boleh membuat akun techade.
        if (($data['role'] ?? null) === 'techade') {
            abort_unless($user->isTechade(), 403);
        }

        return $data;
    }
}
