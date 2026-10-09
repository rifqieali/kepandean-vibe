<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kategori;
use App\Support\Media;
use App\Support\PublicSite;
use App\Support\Seo;
use App\Support\Terbitan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BeritaController extends Controller
{
    public function index(Request $request): Response
    {
        $desa = PublicSite::currentDesa();
        $nama = PublicSite::displayName($desa);

        $kategoris = $desa instanceof Desa
            ? Kategori::forDesa($desa)->orderBy('nama')->get()
            : collect();

        /** @var string|null $activeSlug */
        $activeSlug = $request->query('kategori');
        $active = is_string($activeSlug) && $activeSlug !== ''
            ? $kategoris->firstWhere('slug', $activeSlug)
            : null;

        $query = Terbitan::archiveFor($desa, Berita::class, 'publishedForDesa', ['kategori']);

        if ($active instanceof Kategori) {
            $query->where('kategori_id', $active->id);
        }

        $paginator = $query->paginate(9)->withQueryString();

        $paginator->getCollection()->transform(fn (Berita $berita): array => Terbitan::card(
            $berita,
            self::showUrl($berita),
            ['kategori' => self::kategoriArray($berita)],
        ));

        $title = $active instanceof Kategori ? "Berita {$active->nama} {$nama}" : "Berita {$nama}";

        $description = $active instanceof Kategori
            ? "Arsip berita {$active->nama} {$nama} di portal resmi."
            : "Arsip berita terkini {$nama} di portal resmi.";

        $canonical = $active instanceof Kategori
            ? route('berita.index', ['kategori' => $active->slug])
            : route('berita.index');

        return Inertia::render('berita/index', [
            'berita' => $paginator,
            'kategoris' => $kategoris->map(fn (Kategori $kategori): array => [
                'nama' => $kategori->nama,
                'slug' => $kategori->slug,
            ])->all(),
            'activeKategori' => $active instanceof Kategori ? $active->slug : null,
            'meta' => Seo::meta($title, $description, $canonical),
            'schema' => Seo::collectionSchema($title, $canonical, $description),
            ...PublicSite::sharedProps($desa),
        ]);
    }

    /**
     * @param  numeric-string  $tahun
     * @param  numeric-string  $bulan
     * @param  numeric-string  $tanggal
     */
    public function show(string $tahun, string $bulan, string $tanggal, string $slug): Response
    {
        $desa = PublicSite::currentDesa();

        $berita = Terbitan::findFor($desa, Berita::class, 'publishedForDesa', $slug, ['kategori']);

        abort_if($berita === null, 404);

        $date = Terbitan::displayDate($berita);

        abort_unless(
            (int) $tahun === (int) $date->format('Y')
                && (int) $bulan === (int) $date->format('m')
                && (int) $tanggal === (int) $date->format('d'),
            404
        );

        $isi = Terbitan::cleanedIsi($berita);
        $nama = PublicSite::displayName($desa);
        $url = self::showUrl($berita);
        $description = Terbitan::metaDescription($isi);

        return Inertia::render('berita/detail', [
            'berita' => Terbitan::detail(
                $berita,
                $url,
                $isi,
                ['kategori' => self::kategoriArray($berita)],
            ),
            'meta' => Seo::meta(
                "{$berita->judul} ({$nama})",
                $description,
                $url,
                Media::url($berita->cover_path),
            ),
            'schema' => Seo::articleSchema(
                $berita->judul,
                $url,
                Terbitan::displayDate($berita)->toAtomString(),
                $description,
                Media::url($berita->cover_path),
            ),
            ...PublicSite::sharedProps($desa),
        ]);
    }

    public static function showUrl(Berita $berita): string
    {
        $date = Terbitan::displayDate($berita);

        return route('berita.show', [
            'tahun' => $date->format('Y'),
            'bulan' => $date->format('m'),
            'tanggal' => $date->format('d'),
            'slug' => $berita->slug,
        ]);
    }

    /** @return array{nama: string, slug: string}|null */
    public static function kategoriArray(Berita $berita): ?array
    {
        return $berita->kategori instanceof Kategori ? [
            'nama' => $berita->kategori->nama,
            'slug' => $berita->kategori->slug,
        ] : null;
    }
}
