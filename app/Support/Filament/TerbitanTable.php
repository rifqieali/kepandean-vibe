<?php

namespace App\Support\Filament;

use App\Support\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

/**
 * Kolom, filter, dan aksi bersama tabel arsip (Berita, Pengumuman,
 * Kegiatan). Adapter hanya memilih opsi status, nilai published, dan
 * perlu-tidaknya kolom kategori/kedaluarsa.
 */
final class TerbitanTable
{
    public static function coverColumn(): ImageColumn
    {
        return Media::column('cover_path', 'Cover')->circular();
    }

    public static function desaColumn(): TextColumn
    {
        return TextColumn::make('desa.name')
            ->label('Desa')
            ->searchable()
            ->sortable()
            ->visible(fn (): bool => DesaScoping::isDeveloperContext());
    }

    /**
     * @param  array<string, string>  $statusOptions
     */
    public static function statusColumn(string $publishedValue, array $statusOptions): TextColumn
    {
        return TextColumn::make('status')
            ->label('Status')
            ->badge()
            ->color(fn (string $state): string => $state === $publishedValue ? 'success' : 'gray')
            ->formatStateUsing(fn (string $state): string => $statusOptions[$state] ?? $state);
    }

    public static function publishedColumn(): TextColumn
    {
        return TextColumn::make('published_at')->label('Terbit')->dateTime('d M Y H:i')->sortable();
    }

    public static function expiredColumn(): TextColumn
    {
        return TextColumn::make('expired_at')->label('Kedaluarsa')->dateTime('d M Y H:i')->sortable();
    }

    public static function desaFilter(): SelectFilter
    {
        return SelectFilter::make('desa_id')
            ->label('Desa')
            ->relationship('desa', 'name')
            ->visible(fn (): bool => DesaScoping::isDeveloperContext());
    }

    /**
     * @param  array<string, string>  $statusOptions
     */
    public static function statusFilter(array $statusOptions): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Status')
            ->options($statusOptions);
    }

    /** @return array<int, mixed> */
    public static function recordActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /** @return array<int, mixed> */
    public static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
