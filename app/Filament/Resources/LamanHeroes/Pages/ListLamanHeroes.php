<?php

namespace App\Filament\Resources\LamanHeroes\Pages;

use App\Filament\Resources\LamanHeroes\LamanHeroResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLamanHeroes extends ListRecords
{
    protected static string $resource = LamanHeroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
