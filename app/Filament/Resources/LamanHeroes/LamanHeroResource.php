<?php

namespace App\Filament\Resources\LamanHeroes;

use App\Filament\Resources\Concerns\ScopedResource;
use App\Filament\Resources\LamanHeroes\Pages\CreateLamanHero;
use App\Filament\Resources\LamanHeroes\Pages\EditLamanHero;
use App\Filament\Resources\LamanHeroes\Pages\ListLamanHeroes;
use App\Filament\Resources\LamanHeroes\Schemas\LamanHeroForm;
use App\Filament\Resources\LamanHeroes\Tables\LamanHeroesTable;
use App\Models\LamanHero;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Issue #19: atur gambar hero tiap laman dalam. Satu baris per
 * slug laman per desa (unik desa+slug).
 */
class LamanHeroResource extends ScopedResource
{
    protected static ?string $model = LamanHero::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static UnitEnum|string|null $navigationGroup = 'Konten';

    protected static ?string $recordTitleAttribute = 'slug';

    public static function getLabel(): string
    {
        return 'Hero Laman';
    }

    public static function getPluralLabel(): string
    {
        return 'Hero Laman';
    }

    public static function form(Schema $schema): Schema
    {
        return LamanHeroForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LamanHeroesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLamanHeroes::route('/'),
            'create' => CreateLamanHero::route('/create'),
            'edit' => EditLamanHero::route('/{record}/edit'),
        ];
    }
}
