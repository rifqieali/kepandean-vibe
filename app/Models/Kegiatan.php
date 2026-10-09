<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use App\Concerns\TerbitanModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use BelongsToDesa;
    use TerbitanModel;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $table = 'kegiatans';

    protected $fillable = [
        'desa_id',
        'judul',
        'slug',
        'isi',
        'cover_path',
        'status',
        'published_at',
        'expired_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Yang tayang = published DAN belum kedaluarsa.
     * Kedaluarsa = hilang (tak tampil di index, detail 404) , pilihan
     * meniru Pengumuman (#17): kedaluarsa = hilang dari publik.
     *
     * @return Builder<self>
     */
    public static function visibleForDesa(Desa $desa): Builder
    {
        return static::publishedForDesa($desa)->where(function (Builder $query): void {
            $query->whereNull('expired_at')->orWhere('expired_at', '>', now());
        });
    }

    public function isExpired(): bool
    {
        return $this->expired_at !== null && ! $this->expired_at->isFuture();
    }
}
