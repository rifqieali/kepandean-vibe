<?php

namespace App\Filament\Resources\Beritas\Pages;

use App\Filament\Resources\Beritas\BeritaResource;
use App\Filament\Resources\Concerns\ScopedEditPage;
use App\Models\Berita;
use App\Models\Kategori;
use App\Models\User;
use App\Support\Filament\TerbitanForm;
use Filament\Actions\DeleteAction;

class EditBerita extends ScopedEditPage
{
    protected static string $resource = BeritaResource::class;

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
        $data = TerbitanForm::applyPublishRules($data, $user, Berita::STATUS_PUBLISHED);

        $kategori = Kategori::withoutGlobalScope('desa')->find($data['kategori_id'] ?? null);

        $recordDesaId = $this->record instanceof Berita ? $this->record->desa_id : null;

        abort_unless($kategori instanceof Kategori && $kategori->desa_id === (int) ($data['desa_id'] ?? $recordDesaId), 422);

        return $data;
    }
}
