<?php

namespace App\Filament\Resources\Beritas\Pages;

use App\Filament\Resources\Beritas\BeritaResource;
use App\Filament\Resources\Concerns\ScopedCreatePage;
use App\Models\Berita;
use App\Models\Kategori;
use App\Models\User;
use App\Support\Filament\TerbitanForm;

class CreateBerita extends ScopedCreatePage
{
    protected static string $resource = BeritaResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        $data = TerbitanForm::applyPublishRules($data, $user, Berita::STATUS_PUBLISHED);

        // Kategori harus milik desa yang sama.
        $kategori = Kategori::withoutGlobalScope('desa')->find($data['kategori_id'] ?? null);

        abort_unless($kategori instanceof Kategori && $kategori->desa_id === (int) $data['desa_id'], 422);

        return $data;
    }
}
