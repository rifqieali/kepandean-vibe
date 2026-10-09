<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pejabat extends Model
{
    use BelongsToDesa;

    public const KELOMPOK_PIMPINAN = 'pimpinan';

    public const KELOMPOK_PERANGKAT = 'perangkat';

    public const KELOMPOK_WILAYAH = 'wilayah';

    protected $fillable = [
        'desa_id',
        'nama',
        'jabatan',
        'kelompok',
        'wilayah_label',
        'foto_path',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    /** @return array<string, string> */
    public static function kelompokOptions(): array
    {
        return [
            self::KELOMPOK_PIMPINAN => 'Pimpinan',
            self::KELOMPOK_PERANGKAT => 'Perangkat',
            self::KELOMPOK_WILAYAH => 'Wilayah (RT/RW)',
        ];
    }

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }
}
