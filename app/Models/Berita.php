<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use App\Concerns\TerbitanModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berita extends Model
{
    use BelongsToDesa;
    use TerbitanModel;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'desa_id',
        'kategori_id',
        'judul',
        'slug',
        'isi',
        'cover_path',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /** @return BelongsTo<Kategori, $this> */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }
}
