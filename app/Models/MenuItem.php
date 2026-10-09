<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Menu navigasi dinamis per-desa (issue #19 checklist 3).
 *
 * Menu bawaan (Beranda, Profil, Informasi, ...) tetap hardcode di
 * PublicSite::nav() sampai modulnya native; baris di tabel ini tampil
 * SETELAH menu bawaan, terurut `urutan`. Satu tingkat nesting lewat
 * `parent_id` (anak hanya untuk induk tingkat-atas).
 */
class MenuItem extends Model
{
    use BelongsToDesa;

    protected $fillable = ['desa_id', 'label', 'url', 'urutan', 'parent_id'];

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<self, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan')->orderBy('id');
    }

    /**
     * Induk harus se-desa, tingkat-atas (satu tingkat nesting), dan
     * bukan diri sendiri. Dipanggil dari halaman create/edit Filament.
     */
    public static function assertValidParent(mixed $parentId, int $desaId, mixed $selfId = null): void
    {
        if ($parentId === null || $parentId === '') {
            return;
        }

        $parent = self::withoutGlobalScope('desa')->find($parentId);

        abort_unless($parent instanceof self, 422);
        abort_unless($parent->desa_id === $desaId, 422);
        abort_unless($parent->parent_id === null, 422);
        abort_unless((int) $parent->getKey() !== (int) $selfId, 422);
    }

    /**
     * URL admin dinormalisasi: absolut http(s) dibiarkan, sisanya
     * dipaksa jalur relatif berawalan `/` agar tidak jadi tautan mati.
     */
    public function navHref(): string
    {
        $url = trim((string) $this->url);

        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        return '/'.ltrim($url, '/');
    }
}
