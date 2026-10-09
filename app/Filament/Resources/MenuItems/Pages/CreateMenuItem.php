<?php

namespace App\Filament\Resources\MenuItems\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\MenuItems\MenuItemResource;
use App\Models\MenuItem;
use App\Models\User;

class CreateMenuItem extends ScopedCreatePage
{
    protected static string $resource = MenuItemResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        MenuItem::assertValidParent($data['parent_id'] ?? null, (int) $data['desa_id']);

        return $data;
    }
}
