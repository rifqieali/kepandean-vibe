<?php

namespace App\Filament\Resources\Pejabats\Tables;

use App\Models\Pejabat;
use App\Support\Filament\DesaScoping;
use App\Support\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PejabatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Media::column('foto_path', 'Foto')->circular(),
                TextColumn::make('desa.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => DesaScoping::isDeveloperContext()),
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('jabatan')->label('Jabatan')->searchable()->sortable(),
                TextColumn::make('kelompok')
                    ->label('Kelompok')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Pejabat::kelompokOptions()[$state] ?? $state),
                TextColumn::make('wilayah_label')->label('Wilayah')->searchable(),
                TextColumn::make('urutan')->label('Urut')->sortable(),
            ])
            ->filters([
                SelectFilter::make('desa_id')
                    ->label('Desa')
                    ->relationship('desa', 'name')
                    ->visible(fn (): bool => DesaScoping::isDeveloperContext()),
                SelectFilter::make('kelompok')
                    ->label('Kelompok')
                    ->options(Pejabat::kelompokOptions()),
            ])
            ->defaultSort('urutan')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
