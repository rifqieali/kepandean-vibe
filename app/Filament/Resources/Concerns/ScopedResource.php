<?php

namespace App\Filament\Resources\Concerns;

use App\Support\Filament\DesaScoping;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resource ter-scope desa secara bawaan (opt-out, bukan opt-in): query
 * admin selalu lewat DesaScoping. Resource yang butuh pengecualian
 * meng-override getEloquentQuery secara eksplisit.
 */
abstract class ScopedResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $model = static::getModel();

        return DesaScoping::scopeForAdmin($model::query());
    }
}
