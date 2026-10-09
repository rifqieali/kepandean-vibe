<?php

namespace App\Filament\Resources\Kegiatans\Schemas;

use App\Models\Kegiatan;
use App\Support\Filament\DesaScoping;
use App\Support\Filament\TerbitanForm;
use App\Support\Media;
use Filament\Schemas\Schema;

class KegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                TerbitanForm::titleField(),
                TerbitanForm::slugField('kegiatans'),
                Media::richEditor()
                    ->label('Isi')
                    ->required()
                    ->columnSpanFull(),
                Media::upload('cover_path', 'kegiatan', 'Cover')
                    ->helperText('Opsional. Gambar sampul (jpg/png/webp/gif/svg).'),
                ...TerbitanForm::statusFields(Kegiatan::statusOptions(), Kegiatan::STATUS_DRAFT, true),
            ]);
    }
}
