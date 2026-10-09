<?php

namespace App\Filament\Resources\Pengumumans\Schemas;

use App\Models\Pengumuman;
use App\Support\Filament\DesaScoping;
use App\Support\Filament\TerbitanForm;
use App\Support\Media;
use Filament\Schemas\Schema;

class PengumumanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DesaScoping::desaSelect(),
                TerbitanForm::titleField(),
                TerbitanForm::slugField('pengumumans'),
                Media::richEditor()
                    ->label('Isi')
                    ->required()
                    ->columnSpanFull(),
                Media::upload('cover_path', 'pengumuman', 'Cover')
                    ->helperText('Opsional. Gambar sampul (jpg/png/webp/gif/svg).'),
                ...TerbitanForm::statusFields(Pengumuman::statusOptions(), Pengumuman::STATUS_DRAFT, true),
            ]);
    }
}
