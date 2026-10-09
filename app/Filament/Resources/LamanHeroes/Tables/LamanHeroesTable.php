<?php

namespace App\Filament\Resources\LamanHeroes\Tables;

use App\Models\LamanHero;
use App\Support\Filament\DesaScoping;
use App\Support\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LamanHeroesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Media::column('gambar_path', 'Gambar')->square(),
                TextColumn::make('desa.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => DesaScoping::isTechadeContext()),
                TextColumn::make('slug')
                    ->label('Laman')
                    ->formatStateUsing(fn (string $state): string => LamanHero::slugOptions()[$state] ?? $state),
            ])
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
