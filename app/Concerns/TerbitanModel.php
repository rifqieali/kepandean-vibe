<?php

namespace App\Concerns;

use App\Models\Desa;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;

/**
 * Perilaku bersama model terbitan (Berita, Pengumuman, Kegiatan): scope
 * per-desa, opsi status, dan sanitasi isi saat simpan. Kelas memakai tetap
 * mendefinisikan konstanta STATUS_* dan kekhasannya sendiri (kategori
 * Berita, kedaluarsa Pengumuman/Kegiatan).
 */
trait TerbitanModel
{
    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            static::STATUS_DRAFT => 'Draft',
            static::STATUS_PUBLISHED => 'Published',
        ];
    }

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }

    /** @return Builder<self> */
    public static function publishedForDesa(Desa $desa): Builder
    {
        return static::forDesa($desa)->where('status', static::STATUS_PUBLISHED);
    }

    public function isPublished(): bool
    {
        return $this->status === static::STATUS_PUBLISHED;
    }

    protected static function bootTerbitanModel(): void
    {
        static::saving(function (self $terbitan): void {
            $terbitan->isi = HtmlSanitizer::clean((string) $terbitan->isi);
        });
    }
}
