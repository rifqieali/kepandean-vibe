<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Support\Media;
use App\Support\PublicSite;
use App\Support\Seo;
use App\Support\Terbitan;
use Inertia\Inertia;
use Inertia\Response;

class PengumumanController extends Controller
{
    public function index(): Response
    {
        $desa = PublicSite::currentDesa();
        $nama = PublicSite::displayName($desa);

        $paginator = Terbitan::archiveFor($desa, Pengumuman::class, 'visibleForDesa')
            ->paginate(9)
            ->withQueryString();

        $paginator->getCollection()->transform(fn (Pengumuman $pengumuman): array => Terbitan::card(
            $pengumuman,
            route('pengumuman.show', ['slug' => $pengumuman->slug]),
        ));

        return Inertia::render('pengumuman/index', [
            'pengumuman' => $paginator,
            'meta' => Seo::meta(
                "Pengumuman {$nama}",
                "Pengumuman resmi {$nama} di portal resmi.",
                route('pengumuman.index'),
            ),
            'schema' => Seo::collectionSchema(
                "Pengumuman {$nama}",
                route('pengumuman.index'),
                "Pengumuman resmi {$nama} di portal resmi.",
            ),
            ...PublicSite::sharedProps($desa),
        ]);
    }

    public function show(string $slug): Response
    {
        $desa = PublicSite::currentDesa();

        $pengumuman = Terbitan::findFor($desa, Pengumuman::class, 'visibleForDesa', $slug);

        abort_if($pengumuman === null, 404);

        $isi = Terbitan::cleanedIsi($pengumuman);
        $nama = PublicSite::displayName($desa);
        $url = route('pengumuman.show', ['slug' => $pengumuman->slug]);
        $description = Terbitan::metaDescription($isi);

        return Inertia::render('pengumuman/detail', [
            'pengumuman' => Terbitan::detail(
                $pengumuman,
                $url,
                $isi,
                ['kedaluarsa' => $pengumuman->expired_at?->format('d M Y')],
            ),
            'meta' => Seo::meta(
                "{$pengumuman->judul} ({$nama})",
                $description,
                $url,
                Media::url($pengumuman->cover_path),
            ),
            'schema' => Seo::articleSchema(
                $pengumuman->judul,
                $url,
                Terbitan::displayDate($pengumuman)->toAtomString(),
                $description,
                Media::url($pengumuman->cover_path),
            ),
            ...PublicSite::sharedProps($desa),
        ]);
    }
}
