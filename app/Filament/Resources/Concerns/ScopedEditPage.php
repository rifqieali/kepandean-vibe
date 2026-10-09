<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use App\Support\Filament\DesaScoping;
use Filament\Resources\Pages\EditRecord;

/**
 * Halaman edit ter-scope desa secara bawaan: desa_id dikunci agar baris
 * tidak bisa dipindahkan antar desa. Kekhasan resource tinggal di hook
 * mutateScopedData.
 */
abstract class ScopedEditPage extends EditRecord
{
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = DesaScoping::lockDesaIdForSave($data);

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
