<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Models\MenuItem;
use App\Models\User;
use App\Support\Filament\DesaScoping;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                TextInput::make('label')
                    ->label('Label')
                    ->required()
                    ->maxLength(60),
                TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Tautan relatif (/profil, https://...) — tampil setelah menu bawaan.'),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Select::make('parent_id')
                    ->label('Induk (opsional)')
                    ->options(fn (Get $get): array => self::parentOptions($get))
                    ->searchable()
                    ->helperText('Kosongkan untuk menu tingkat-atas. Anak hanya satu tingkat.'),
            ]);
    }

    /** @return array<int, string> */
    private static function parentOptions(Get $get): array
    {
        $user = DesaScoping::currentUser();
        $desaId = (int) ($get('desa_id') ?? ($user instanceof User ? $user->desa_id : 0));

        if ($desaId <= 0) {
            return [];
        }

        return MenuItem::withoutGlobalScope('desa')
            ->where('desa_id', $desaId)
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->orderBy('id')
            ->pluck('label', 'id')
            ->all();
    }
}
