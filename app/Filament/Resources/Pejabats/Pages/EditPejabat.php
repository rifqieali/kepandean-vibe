<?php

namespace App\Filament\Resources\Pejabats\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\Pejabats\PejabatResource;
use Filament\Actions\DeleteAction;

class EditPejabat extends ScopedEditPage
{
    protected static string $resource = PejabatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
