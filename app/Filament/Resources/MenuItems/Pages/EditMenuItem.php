<?php

namespace App\Filament\Resources\MenuItems\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\MenuItems\MenuItemResource;
use App\Models\MenuItem;
use App\Models\User;
use Filament\Actions\DeleteAction;

class EditMenuItem extends ScopedEditPage
{
    protected static string $resource = MenuItemResource::class;

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
        $recordDesaId = $this->record instanceof MenuItem ? $this->record->desa_id : null;

        MenuItem::assertValidParent(
            $data['parent_id'] ?? null,
            (int) ($data['desa_id'] ?? $recordDesaId),
            $this->record instanceof MenuItem ? $this->record->getKey() : null,
        );

        return $data;
    }
}
