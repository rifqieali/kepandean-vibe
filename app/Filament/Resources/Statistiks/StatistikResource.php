<?php

namespace App\Filament\Resources\Statistiks;

use App\Filament\Resources\Concerns\ScopedResource;
use App\Filament\Resources\Statistiks\Pages\CreateStatistik;
use App\Filament\Resources\Statistiks\Pages\EditStatistik;
use App\Filament\Resources\Statistiks\Pages\ListStatistiks;
use App\Filament\Resources\Statistiks\Schemas\StatistikForm;
use App\Filament\Resources\Statistiks\Tables\StatistiksTable;
use App\Models\Statistik;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Issue #18: admin ubah angka Statistik tanpa developer; Beranda membaca
 * dari tabel ini. Scope per-desa mengikuti pola UserResource.
 */
class StatistikResource extends ScopedResource
{
    protected static ?string $model = Statistik::class;

    protected static ?string $navigationLabel = 'Statistik';

    protected static ?string $modelLabel = 'Statistik';

    protected static ?string $pluralModelLabel = 'Statistik';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static UnitEnum|string|null $navigationGroup = 'Konten';

    protected static ?string $recordTitleAttribute = 'kunci';

    public static function form(Schema $schema): Schema
    {
        return StatistikForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatistiksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatistiks::route('/'),
            'create' => CreateStatistik::route('/create'),
            'edit' => EditStatistik::route('/{record}/edit'),
        ];
    }
}
