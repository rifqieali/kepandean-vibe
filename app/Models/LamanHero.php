<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Hero tiap laman dalam (issue #19): satu gambar per slug laman,
 * diatur admin lewat Filament. Kosong berarti pakai foto konten
 * atau foto sejarah sebagai cadangan.
 */
class LamanHero extends Model
{
    use BelongsToDesa;

    public const SLUGS = [
        'berita' => 'Berita Desa',
        'pengumuman' => 'Pengumuman',
        'kegiatan' => 'Kegiatan',
        'informasi' => 'Informasi',
        'profil' => 'Profil (Sejarah & Visi Misi)',
        'struktur' => 'Struktur Organisasi',
        'pemerintahan' => 'Pemerintahan',
        'lembaga-desa' => 'Lembaga Desa',
        'produk-hukum' => 'Produk Hukum',
        'laporan' => 'Laporan',
        'layanan-warga' => 'Layanan Warga',
        'layanan' => 'Layanan',
        'potensi-galeri' => 'Potensi & Galeri',
        'kontak-lokasi' => 'Kontak & Lokasi',
    ];

    protected $fillable = ['desa_id', 'slug', 'gambar_path'];

    /** @return Builder<self> */
    public static function forDesa(Desa $desa): Builder
    {
        return static::withoutGlobalScope('desa')->where('desa_id', $desa->id);
    }

    /** @return array<string, string> */
    public static function slugOptions(): array
    {
        return self::SLUGS;
    }
}
