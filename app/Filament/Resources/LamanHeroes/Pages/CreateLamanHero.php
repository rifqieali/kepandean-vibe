<?php

namespace App\Filament\Resources\LamanHeroes\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\LamanHeroes\LamanHeroResource;

class CreateLamanHero extends ScopedCreatePage
{
    protected static string $resource = LamanHeroResource::class;
}
