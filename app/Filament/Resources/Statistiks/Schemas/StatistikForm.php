<?php

namespace App\Filament\Resources\Statistiks\Schemas;

use App\Support\Filament\DesaScoping;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class StatistikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                Select::make('kunci')
                    ->label('Kunci')
                    ->options([
                        'total_jiwa' => 'Total jiwa',
                        'laki_laki' => 'Laki-laki',
                        'perempuan' => 'Perempuan',
                        'kepala_keluarga' => 'Kepala keluarga (KK)',
                    ])
                    ->required()
                    // Unik per-desa mengikuti unique(['desa_id','kunci']),
                    // bukan unik global (desa kedua harus bisa memakai kunci sama).
                    ->unique(
                        table: 'statistiks',
                        column: 'kunci',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Get $get): Unique {
                            return DesaScoping::uniqueInDesa($rule, DesaScoping::desaIdForUnique($get));
                        },
                    ),
                TextInput::make('nilai')
                    ->label('Nilai')
                    ->required()
                    ->maxLength(50)
                    ->helperText('Hanya angka + satuan pendek, mis. 6997 atau 3378.'),
            ]);
    }
}
