<?php

namespace App\Filament\Resources\Pejabats\Pages;

use App\Filament\Resources\Pejabats\PejabatResource;
use App\Models\Pejabat;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;

class ListPejabats extends ListRecords
{
    protected static string $resource = PejabatResource::class;

    protected function authorizeAccess(): void
    {
        // ListRecords tidak mengecek viewAny sendiri; kunci di sini agar
        // editor yang menebak URL langsung tetap ditolak (nav sudah disembunyikan policy).
        Gate::authorize('viewAny', Pejabat::class);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
