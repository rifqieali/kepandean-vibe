<?php

namespace App\Concerns;

use App\Models\Desa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

trait BelongsToDesa
{
    /**
     * Standard trait boot hook (boot{TraitName}), so the global scope is
     * registered even when the using model defines its own booted().
     * A booted() method inside a trait would be silently overwritten by
     * the model's own booted(), dropping the desa scope (cross-desa leak).
     */
    protected static function bootBelongsToDesa(): void
    {
        static::addGlobalScope('desa', function (Builder $builder) {
            $desa = App::bound('current_desa') ? App::make('current_desa') : null;

            if ($desa instanceof Desa) {
                $builder->where('desa_id', $desa->id);
            }
        });
    }

    /** @return BelongsTo<Desa, $this> */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }
}
