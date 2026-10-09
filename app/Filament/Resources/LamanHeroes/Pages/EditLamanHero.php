<?php

namespace App\Filament\Resources\LamanHeroes\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\LamanHeroes\LamanHeroResource;
use Filament\Actions\DeleteAction;

class EditLamanHero extends ScopedEditPage
{
    protected static string $resource = LamanHeroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
