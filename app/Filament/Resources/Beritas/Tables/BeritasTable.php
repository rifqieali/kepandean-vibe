<?php

namespace App\Filament\Resources\Beritas\Tables;

use App\Models\Berita;
use App\Support\Filament\TerbitanTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BeritasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TerbitanTable::coverColumn(),
                TerbitanTable::desaColumn(),
                TextColumn::make('judul')->label('Judul')->searchable()->limit(50),
                TextColumn::make('kategori.nama')->label('Kategori'),
                TerbitanTable::statusColumn(Berita::STATUS_PUBLISHED, Berita::statusOptions()),
                TerbitanTable::publishedColumn(),
            ])
            ->filters([
                TerbitanTable::desaFilter(),
                SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama'),
                TerbitanTable::statusFilter(Berita::statusOptions()),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions(TerbitanTable::recordActions())
            ->toolbarActions(TerbitanTable::toolbarActions());
    }
}
