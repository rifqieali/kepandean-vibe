<?php

namespace App\Filament\Resources\Pengumumans\Pages;

use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Filament\Resources\Pengumumans\PengumumanResource;
use App\Models\Pengumuman;
use App\Models\User;
use App\Support\Filament\TerbitanForm;
use Filament\Actions\DeleteAction;

class EditPengumuman extends ScopedEditPage
{
    protected static string $resource = PengumumanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        return TerbitanForm::applyPublishRules($data, $user, Pengumuman::STATUS_PUBLISHED);
    }
}
