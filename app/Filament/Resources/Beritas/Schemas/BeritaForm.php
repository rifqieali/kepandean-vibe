<?php

namespace App\Filament\Resources\Beritas\Schemas;

use App\Models\Berita;
use App\Support\Filament\DesaScoping;
use App\Support\Filament\TerbitanForm;
use App\Support\Media;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BeritaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                Select::make('kategori_id')
                    ->label('Kategori')
                    ->options(fn (Get $get): array => DesaScoping::kategoriOptions(null, $get('desa_id')))
                    ->required()
                    ->searchable(),
                TerbitanForm::titleField(),
                TerbitanForm::slugField('beritas'),
                Media::richEditor()
                    ->label('Isi')
                    ->required()
                    ->columnSpanFull(),
                Media::upload('cover_path', 'berita', 'Cover')
                    ->helperText('Wajib bertipe gambar (jpg/png/webp/gif/svg).'),
                ...TerbitanForm::statusFields(Berita::statusOptions(), Berita::STATUS_DRAFT, false),
            ]);
    }
}
