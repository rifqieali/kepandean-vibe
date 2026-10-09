<?php

namespace App\Filament\Resources\Pengumumans;

use App\Filament\Resources\Concerns\ScopedResource;
use App\Filament\Resources\Pengumumans\Pages\CreatePengumuman;
use App\Filament\Resources\Pengumumans\Pages\EditPengumuman;
use App\Filament\Resources\Pengumumans\Pages\ListPengumumans;
use App\Filament\Resources\Pengumumans\Schemas\PengumumanForm;
use App\Filament\Resources\Pengumumans\Tables\PengumumansTable;
use App\Models\Pengumuman;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Arsip Pengumuman (#17): adapter ringan di atas Terbitan.
 * Punya cover dan kedaluarsa, tanpa kategori.
 * Editor boleh simpan draft; publish hanya canPublish() (admin_desa/techade).
 */
class PengumumanResource extends ScopedResource
{
    protected static ?string $model = Pengumuman::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static UnitEnum|string|null $navigationGroup = 'Konten';

    protected static ?string $recordTitleAttribute = 'judul';

    // Slug eksplisit: turunan otomatis Filament memberi
    // `pengumumans/pengumumen` untuk namespace bertingkat ini.
    protected static ?string $slug = 'pengumumans';

    public static function getLabel(): string
    {
        return 'Pengumuman';
    }

    public static function getPluralLabel(): string
    {
        return 'Pengumuman';
    }

    public static function form(Schema $schema): Schema
    {
        return PengumumanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengumumansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengumumans::route('/'),
            'create' => CreatePengumuman::route('/create'),
            'edit' => EditPengumuman::route('/{record}/edit'),
        ];
    }
}
