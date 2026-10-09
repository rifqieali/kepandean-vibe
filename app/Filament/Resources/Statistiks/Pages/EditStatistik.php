<?php

namespace App\Filament\Resources\Statistiks\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\Statistiks\StatistikResource;
use Filament\Actions\DeleteAction;

class EditStatistik extends ScopedEditPage
{
    protected static string $resource = StatistikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
