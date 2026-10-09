<?php

namespace App\Filament\Resources\Pengumumans\Pages;

use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Filament\Resources\Pengumumans\PengumumanResource;
use App\Models\Pengumuman;
use App\Models\User;
use App\Support\Filament\TerbitanForm;

class CreatePengumuman extends ScopedCreatePage
{
    protected static string $resource = PengumumanResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        return TerbitanForm::applyPublishRules($data, $user, Pengumuman::STATUS_PUBLISHED);
    }
}
