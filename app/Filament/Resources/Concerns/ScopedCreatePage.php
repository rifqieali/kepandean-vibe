<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use App\Support\Filament\DesaScoping;
use Filament\Resources\Pages\CreateRecord;

/**
 * Halaman create ter-scope desa secara bawaan: desa_id di-resolve/dikunci
 * sebelum baris disimpan. Kekhasan resource (gate publish, validasi relasi)
 * tinggal di hook mutateScopedData.
 */
abstract class ScopedCreatePage extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = DesaScoping::resolveDesaIdForCreate($data);

        return $this->mutateScopedData($data, DesaScoping::currentUser());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateScopedData(array $data, ?User $user): array
    {
        return $data;
    }
}
