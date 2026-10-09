<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use BelongsToDesa;

    public const MAX_ACTIVE = 5;

    protected $fillable = [
        'desa_id',
        'gambar_path',
        'judul',
        'subjudul',
        'tautan_label',
        'tautan_url',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }

    /** @return Builder<self> */
    public static function activeForDesa(Desa $desa): Builder
    {
        return static::forDesa($desa)->where('aktif', true)->orderBy('urutan')->orderBy('id');
    }
}
