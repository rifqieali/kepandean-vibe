<?php

namespace App\Support;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Satu-satunya seam logika terbitan (Berita, Pengumuman, Kegiatan): query
 * arsip, temu slug, kartu, dan detil. Tiga controller arsip plus
 * InformasiController plus sitemap duduk di seam ini sebagai adapter:
 * mereka hanya memilih scope visibilitas, nama rute, dan salinan meta.
 */
final class Terbitan
{
    /**
     * @template T of Model
     *
     * @param  class-string<T>  $model
     * @param  string[]  $with
     * @return Builder<T>
     */
    public static function archiveFor(?Desa $desa, string $model, string $scope, array $with = []): Builder
    {
        if (! $desa instanceof Desa) {
            return $model::query()->whereRaw('1 = 0');
        }

        return $model::{$scope}($desa)->with($with)->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * @template T of Model
     *
     * @param  class-string<T>  $model
     * @param  string[]  $with
     * @return T|null
     */
    public static function findFor(?Desa $desa, string $model, string $scope, string $slug, array $with = []): ?Model
    {
        if (! $desa instanceof Desa) {
            return null;
        }

        return $model::{$scope}($desa)->with($with)->where('slug', $slug)->first();
    }

    public static function displayDate(Berita|Pengumuman|Kegiatan $item): CarbonInterface
    {
        return $item->published_at ?? $item->created_at ?? now();
    }

    public static function cleanedIsi(Berita|Pengumuman|Kegiatan $item): string
    {
        return HtmlSanitizer::clean(Media::renderRich($item->isi));
    }

    public static function excerpt(?string $isi): ?string
    {
        $excerpt = Str::limit(trim(strip_tags(HtmlSanitizer::clean((string) $isi))), 160);

        return $excerpt === '' ? null : $excerpt;
    }

    public static function metaDescription(string $isi): string
    {
        return Str::limit(trim(strip_tags($isi)), 150);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public static function card(Berita|Pengumuman|Kegiatan $item, string $url, ?array $extra = null): array
    {
        return array_merge([
            'judul' => $item->judul,
            'slug' => $item->slug,
            'excerpt' => self::excerpt($item->isi),
            'cover_url' => Media::url($item->cover_path),
            'tanggal' => self::displayDate($item)->format('d M Y'),
            'url' => $url,
        ], $extra ?? []);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public static function detail(Berita|Pengumuman|Kegiatan $item, string $url, string $isi, ?array $extra = null): array
    {
        return array_merge([
            'judul' => $item->judul,
            'slug' => $item->slug,
            'isi' => $isi,
            'cover_url' => Media::url($item->cover_path),
            'tanggal' => self::displayDate($item)->format('d M Y'),
            'url' => $url,
        ], $extra ?? []);
    }
}
