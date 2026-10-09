<?php

namespace App\Filament\Resources\Beritas;

use App\Filament\Resources\Beritas\Pages\CreateBerita;
use App\Filament\Resources\Beritas\Pages\EditBerita;
use App\Filament\Resources\Beritas\Pages\ListBeritas;
use App\Filament\Resources\Beritas\Schemas\BeritaForm;
use App\Filament\Resources\Beritas\Tables\BeritasTable;
use App\Filament\Resources\Concerns\ScopedResource;
use App\Models\Berita;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Issue #16: Berita end to end — pola baku yang ditiru Pengumuman (#17).
 * Editor boleh simpan draft; publish hanya canPublish() (admin_desa/developer).
 */
class BeritaResource extends ScopedResource
{
    protected static ?string $model = Berita::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static UnitEnum|string|null $navigationGroup = 'Konten';

    protected static ?string $recordTitleAttribute = 'judul';

    public static function getLabel(): string
    {
        return 'Berita';
    }

    public static function getPluralLabel(): string
    {
        return 'Berita';
    }

    public static function form(Schema $schema): Schema
    {
        return BeritaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeritasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeritas::route('/'),
            'create' => CreateBerita::route('/create'),
            'edit' => EditBerita::route('/{record}/edit'),
        ];
    }
}
