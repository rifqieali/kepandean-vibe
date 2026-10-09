<?php

namespace App\Filament\Resources\Kegiatans;

use App\Filament\Resources\Concerns\ScopedResource;
use App\Filament\Resources\Kegiatans\Pages\CreateKegiatan;
use App\Filament\Resources\Kegiatans\Pages\EditKegiatan;
use App\Filament\Resources\Kegiatans\Pages\ListKegiatans;
use App\Filament\Resources\Kegiatans\Schemas\KegiatanForm;
use App\Filament\Resources\Kegiatans\Tables\KegiatansTable;
use App\Models\Kegiatan;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Arsip Kegiatan: adapter ringan di atas Terbitan (kedaluarsa = hilang,
 * meniru Pengumuman #17). Punya cover, tanpa kategori.
 * Editor boleh simpan draft; publish hanya canPublish() (admin_desa/techade).
 */
class KegiatanResource extends ScopedResource
{
    protected static ?string $model = Kegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static UnitEnum|string|null $navigationGroup = 'Konten';

    protected static ?string $recordTitleAttribute = 'judul';

    // Slug eksplisit: turunan otomatis Filament memberi
    // `kegiatans/pengumumen` untuk namespace bertingkat ini.
    protected static ?string $slug = 'kegiatans';

    public static function getLabel(): string
    {
        return 'Kegiatan';
    }

    public static function getPluralLabel(): string
    {
        return 'Kegiatan';
    }

    public static function form(Schema $schema): Schema
    {
        return KegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KegiatansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKegiatans::route('/'),
            'create' => CreateKegiatan::route('/create'),
            'edit' => EditKegiatan::route('/{record}/edit'),
        ];
    }
}
