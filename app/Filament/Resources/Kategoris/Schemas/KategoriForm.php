<?php

namespace App\Filament\Resources\Kategoris\Schemas;

use App\Support\Filament\DesaScoping;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class KategoriForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(120)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        $set('slug', Str::slug((string) $state));
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(140)
                    ->helperText('Otomatis dari nama, bisa diedit. Unik per desa.')
                    ->unique(
                        table: 'kategoris',
                        column: 'slug',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Get $get): Unique {
                            return DesaScoping::uniqueInDesa($rule, DesaScoping::desaIdForUnique($get));
                        },
                    ),
            ]);
    }
}
