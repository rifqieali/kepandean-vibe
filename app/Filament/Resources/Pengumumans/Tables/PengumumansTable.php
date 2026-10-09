<?php

namespace App\Filament\Resources\Pengumumans\Tables;

use App\Models\Pengumuman;
use App\Support\Filament\TerbitanTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengumumansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TerbitanTable::coverColumn(),
                TerbitanTable::desaColumn(),
                TextColumn::make('judul')->label('Judul')->searchable()->limit(50),
                TerbitanTable::statusColumn(Pengumuman::STATUS_PUBLISHED, Pengumuman::statusOptions()),
                TerbitanTable::publishedColumn(),
                TerbitanTable::expiredColumn(),
            ])
            ->filters([
                TerbitanTable::desaFilter(),
                TerbitanTable::statusFilter(Pengumuman::statusOptions()),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions(TerbitanTable::recordActions())
            ->toolbarActions(TerbitanTable::toolbarActions());
    }
}
