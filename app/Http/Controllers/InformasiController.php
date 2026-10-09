<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kategori;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use App\Support\PublicSite;
use App\Support\Seo;
use App\Support\Terbitan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InformasiController extends Controller
{
    public function index(Request $request): Response
    {
        $desa = PublicSite::currentDesa();
        $nama = PublicSite::displayName($desa);

        /** @var string $tab */
        $tab = $request->query('tab', 'berita');
        $tab = in_array($tab, ['berita', 'pengumuman', 'kegiatan'], true) ? $tab : 'berita';

        $kategoris = $desa instanceof Desa
            ? Kategori::forDesa($desa)->orderBy('nama')->get()
            : collect();

        /** @var string|null $activeSlug */
        $activeSlug = $request->query('kategori');
        $active = is_string($activeSlug) && $activeSlug !== ''
            ? $kategoris->firstWhere('slug', $activeSlug)
            : null;

        $beritaQuery = Terbitan::archiveFor($desa, Berita::class, 'publishedForDesa', ['kategori']);

        if ($active instanceof Kategori) {
            $beritaQuery->where('kategori_id', $active->id);
        }

        // Paginasi terpisah per tab: page param berbeda agar tidak
        // saling menimpa (bukan load semua sekaligus).
        $beritaPaginator = $beritaQuery->paginate(6, ['*'], 'berita_page')->withQueryString();
        $beritaPaginator->getCollection()->transform(fn (Berita $berita): array => Terbitan::card(
            $berita,
            BeritaController::showUrl($berita),
            ['kategori' => BeritaController::kategoriArray($berita)],
        ));

        $pengumumanQuery = Terbitan::archiveFor($desa, Pengumuman::class, 'visibleForDesa');

        $pengumumanPaginator = $pengumumanQuery->paginate(6, ['*'], 'pengumuman_page')->withQueryString();
        $pengumumanPaginator->getCollection()->transform(fn (Pengumuman $pengumuman): array => Terbitan::card(
            $pengumuman,
            route('pengumuman.show', ['slug' => $pengumuman->slug]),
        ));

        $kegiatanQuery = Terbitan::archiveFor($desa, Kegiatan::class, 'visibleForDesa');

        $kegiatanPaginator = $kegiatanQuery->paginate(6, ['*'], 'kegiatan_page')->withQueryString();
        $kegiatanPaginator->getCollection()->transform(fn (Kegiatan $kegiatan): array => Terbitan::card(
            $kegiatan,
            route('kegiatan.show', ['slug' => $kegiatan->slug]),
        ));

        // Badge tab memakai total tanpa filter kategori agar konsisten
        // saat filter aktif (paginator di atas sudah terfilter).
        $beritaTotal = $desa instanceof Desa
            ? Berita::publishedForDesa($desa)->count()
            : 0;

        return Inertia::render('informasi/index', [
            'tab' => $tab,
            'berita' => $beritaPaginator,
            'pengumuman' => $pengumumanPaginator,
            'kegiatan' => $kegiatanPaginator,
            'kategoris' => $kategoris->map(fn (Kategori $kategori): array => [
                'nama' => $kategori->nama,
                'slug' => $kategori->slug,
            ])->all(),
            'activeKategori' => $active instanceof Kategori ? $active->slug : null,
            'counts' => [
                'berita' => $beritaTotal,
                'pengumuman' => $pengumumanPaginator->total(),
                'kegiatan' => $kegiatanPaginator->total(),
            ],
            'meta' => Seo::meta(
                "Informasi {$nama}",
                "Informasi {$nama}: arsip berita terkini, pengumuman, dan kegiatan resmi di portal resmi.",
                route('informasi'),
            ),
            'schema' => Seo::collectionSchema(
                "Informasi {$nama}",
                route('informasi'),
                "Informasi {$nama}: arsip berita terkini, pengumuman, dan kegiatan resmi di portal resmi.",
            ),
            ...PublicSite::sharedProps($desa),
        ]);
    }
}
