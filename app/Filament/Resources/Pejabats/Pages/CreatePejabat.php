<?php

namespace App\Filament\Resources\Pejabats\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\Pejabats\PejabatResource;

class CreatePejabat extends ScopedCreatePage
{
    protected static string $resource = PejabatResource::class;
}
