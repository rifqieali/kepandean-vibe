<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;

class EditUser extends ScopedEditPage
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        abort_unless($user instanceof User && ($user->isAdminDesa() || $user->isTechade()), 403);

        // Hanya techade yang boleh memberi role techade.
        if (($data['role'] ?? null) === 'techade') {
            abort_unless($user->isTechade(), 403);
        }

        return $data;
    }
}
