<?php

namespace App\Filament\Resources\Kegiatans\Tables;

use App\Models\Kegiatan;
use App\Support\Filament\TerbitanTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TerbitanTable::coverColumn(),
                TerbitanTable::desaColumn(),
                TextColumn::make('judul')->label('Judul')->searchable()->limit(50),
                TerbitanTable::statusColumn(Kegiatan::STATUS_PUBLISHED, Kegiatan::statusOptions()),
                TerbitanTable::publishedColumn(),
                TerbitanTable::expiredColumn(),
            ])
            ->filters([
                TerbitanTable::desaFilter(),
                TerbitanTable::statusFilter(Kegiatan::statusOptions()),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions(TerbitanTable::recordActions())
            ->toolbarActions(TerbitanTable::toolbarActions());
    }
}
