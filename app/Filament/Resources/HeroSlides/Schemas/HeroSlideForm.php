<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use App\Support\Filament\DesaScoping;
use App\Support\Media;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                TextInput::make('judul')
                    ->label('Judul')
                    ->required()
                    ->maxLength(200),
                TextInput::make('subjudul')
                    ->label('Subjudul')
                    ->maxLength(300)
                    ->columnSpanFull()
                    ->helperText('Opsional, tampil di bawah judul.'),
                Media::upload('gambar_path', 'hero', 'Gambar')
                    ->columnSpanFull()
                    ->helperText('Gambar slide (jpg/png/webp, maks. 5 MB). Slide pertama dimuat prioritas, sisanya lazy-load.'),
                TextInput::make('tautan_label')
                    ->label('Label tautan')
                    ->maxLength(60)
                    ->helperText('Opsional, mis. Jelajahi Desa. Kosong = tanpa tombol.'),
                TextInput::make('tautan_url')
                    ->label('URL tautan')
                    ->maxLength(300)
                    ->url()
                    ->helperText('Opsional, tautan internal (mis. /profil/sejarah-visi-misi) atau eksternal https.'),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('Kecil tampil duluan. Hanya 5 aktif terdepan yang tampil di Beranda.'),
                Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Nonaktif = disembunyikan dari Beranda tanpa dihapus.'),
            ]);
    }
}
