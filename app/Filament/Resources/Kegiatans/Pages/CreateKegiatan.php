<?php

namespace App\Filament\Resources\Kegiatans\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\Kegiatans\KegiatanResource;
use App\Models\Kegiatan;
use App\Models\User;
use App\Support\Filament\TerbitanForm;

class CreateKegiatan extends ScopedCreatePage
{
    protected static string $resource = KegiatanResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        return TerbitanForm::applyPublishRules($data, $user, Kegiatan::STATUS_PUBLISHED);
    }
}
