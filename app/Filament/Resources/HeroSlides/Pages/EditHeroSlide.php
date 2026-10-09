<?php

namespace App\Filament\Resources\HeroSlides\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\HeroSlides\HeroSlideResource;
use Filament\Actions\DeleteAction;

class EditHeroSlide extends ScopedEditPage
{
    protected static string $resource = HeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
