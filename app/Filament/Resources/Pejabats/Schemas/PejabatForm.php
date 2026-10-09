<?php

namespace App\Filament\Resources\Pejabats\Schemas;

use App\Models\Pejabat;
use App\Support\Filament\DesaScoping;
use App\Support\Media;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PejabatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(120),
                TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->required()
                    ->maxLength(120)
                    ->helperText('Mis. Kepala Desa, Sekretaris Desa, Ketua RW 05.'),
                Select::make('kelompok')
                    ->label('Kelompok')
                    ->options(Pejabat::kelompokOptions())
                    ->required(),
                TextInput::make('wilayah_label')
                    ->label('Label wilayah')
                    ->maxLength(60)
                    ->helperText('Wajib diisi untuk kelompok Wilayah, mis. RW 05.')
                    ->required(fn ($get): bool => $get('kelompok') === Pejabat::KELOMPOK_WILAYAH),
                Media::upload('foto_path', 'pejabat', 'Foto'),
                TextInput::make('urutan')
                    ->label('Nomor urut')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('Urutan tampil di halaman Struktur Organisasi.'),
            ]);
    }
}
