<?php

namespace App\Filament\Resources\LamanHeroes\Schemas;

use App\Models\LamanHero;
use App\Support\Filament\DesaScoping;
use App\Support\Media;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class LamanHeroForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                Select::make('slug')
                    ->label('Laman')
                    ->options(LamanHero::slugOptions())
                    ->required()
                    ->unique(
                        table: 'laman_heroes',
                        column: 'slug',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Get $get): Unique {
                            return DesaScoping::uniqueInDesa($rule, DesaScoping::desaIdForUnique($get));
                        },
                    )
                    ->helperText('Satu gambar untuk tiap laman.'),
                Media::upload('gambar_path', 'hero-laman', 'Gambar hero')
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Foto lebar (landscape) agar rapi mengisi hero.'),
            ]);
    }
}
