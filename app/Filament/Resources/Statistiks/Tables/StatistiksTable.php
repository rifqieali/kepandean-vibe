<?php

namespace App\Filament\Resources\Statistiks\Tables;

use App\Support\Filament\DesaScoping;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatistiksTable
{
    /** @return array<string, string> */
    public static function kunciOptions(): array
    {
        return [
            'total_jiwa' => 'Total jiwa',
            'laki_laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
            'kepala_keluarga' => 'Kepala keluarga (KK)',
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('desa.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => DesaScoping::isDeveloperContext()),
                TextColumn::make('kunci')
                    ->label('Kunci')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => self::kunciOptions()[$state] ?? $state),
                TextColumn::make('nilai')->label('Nilai')->searchable(),
                TextColumn::make('updated_at')->label('Diubah')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('desa_id')
                    ->label('Desa')
                    ->relationship('desa', 'name')
                    ->visible(fn (): bool => DesaScoping::isDeveloperContext()),
                SelectFilter::make('kunci')
                    ->label('Kunci')
                    ->options(self::kunciOptions()),
            ])
            ->defaultSort('updated_at', 'desc')
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
