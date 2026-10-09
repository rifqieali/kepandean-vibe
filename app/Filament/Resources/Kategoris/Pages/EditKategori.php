<?php

namespace App\Filament\Resources\Kategoris\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\Kategoris\KategoriResource;
use Filament\Actions\DeleteAction;

class EditKategori extends ScopedEditPage
{
    protected static string $resource = KategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
