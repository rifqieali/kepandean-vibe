<?php

namespace App\Filament\Resources\HeroSlides\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\HeroSlides\HeroSlideResource;

class CreateHeroSlide extends ScopedCreatePage
{
    protected static string $resource = HeroSlideResource::class;
}
