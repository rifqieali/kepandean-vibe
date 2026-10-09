<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Aduan extends Model
{
    use BelongsToDesa;

    protected $fillable = ['desa_id', 'nama', 'kontak', 'pesan', 'foto_path', 'lokasi'];

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }
}
