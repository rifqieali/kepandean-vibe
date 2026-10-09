<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Statistik extends Model
{
    use BelongsToDesa;

    protected $fillable = ['desa_id', 'kunci', 'nilai'];

    /**
     * Kunci kanonis + nilai default dari spec-002 (sumber kebenaran).
     * Luas wilayah SENGAJA tidak ada: UNCONFIRMED, jangan tampil.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'total_jiwa' => '6997',
            'laki_laki' => '3599',
            'perempuan' => '3398',
            'kepala_keluarga' => '3378',
        ];
    }

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }

    public static function seedDefaults(Desa $desa): void
    {
        foreach (static::defaults() as $kunci => $nilai) {
            static::withoutGlobalScope('desa')->firstOrCreate(
                ['desa_id' => $desa->id, 'kunci' => $kunci],
                ['nilai' => $nilai]
            );
        }
    }

    /**
     * Peta kunci => nilai untuk desa saat ini (scope global berlaku).
     *
     * @return array<string, string>
     */
    public static function currentMap(): array
    {
        $map = static::defaults();

        foreach (static::query()->pluck('nilai', 'kunci')->all() as $kunci => $nilai) {
            $map[$kunci] = (string) $nilai;
        }

        return $map;
    }
}
