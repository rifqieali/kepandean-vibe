<?php

namespace App\Filament\Resources\Statistiks\Pages;

use App\Filament\Resources\Statistiks\StatistikResource;
use App\Models\Statistik;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;

class ListStatistiks extends ListRecords
{
    protected static string $resource = StatistikResource::class;

    protected function authorizeAccess(): void
    {
        // ListRecords tidak mengecek viewAny sendiri; kunci di sini agar
        // editor yang menebak URL langsung tetap ditolak (nav sudah disembunyikan policy).
        Gate::authorize('viewAny', Statistik::class);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
