<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use BelongsToDesa;

    protected $fillable = ['desa_id', 'nama', 'slug'];

    /** @return HasMany<Berita, $this> */
    public function beritas(): HasMany
    {
        return $this->hasMany(Berita::class);
    }

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }
}
