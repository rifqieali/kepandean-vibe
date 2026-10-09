<?php

namespace App\Filament\Resources\Kategoris\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\Kategoris\KategoriResource;

class CreateKategori extends ScopedCreatePage
{
    protected static string $resource = KategoriResource::class;
}
