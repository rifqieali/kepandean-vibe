<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\AduanController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\InformasiController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PejabatController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SitemapController;
use App\Models\Berita;
use App\Models\Desa;
use App\Models\HeroSlide;
use App\Models\Profil;
use App\Support\HtmlSanitizer;
use App\Support\Media;
use App\Support\PublicSite;
use App\Support\Seo;
use App\Support\Terbitan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/', function () {
    $desa = PublicSite::currentDesa();
    $nama = PublicSite::displayName($desa);

    $profil = $desa instanceof Desa
        ? Profil::withoutGlobalScope('desa')->where('desa_id', $desa->id)->first()
        : null;

    $sejarah = trim(strip_tags(HtmlSanitizer::clean((string) $profil?->sejarah)));

    // Berita Terkini beranda: 2 terbitan terbaru desa aktif
    // (hanya published — draft dan desa lain tidak pernah bocor).
    $beritaTerkini = $desa instanceof Desa
        ? Terbitan::archiveFor($desa, Berita::class, 'publishedForDesa')
            ->limit(2)
            ->get()
            ->map(fn (Berita $berita): array => [
                'judul' => $berita->judul,
                'tanggal' => Terbitan::displayDate($berita)->format('d M Y'),
                'cover_url' => Media::url($berita->cover_path),
                'url' => BeritaController::showUrl($berita),
            ])->all()
        : [];

    // Hero slider Beranda: maksimal 5 slide aktif desa ini
    // (urutan lalu id; nonaktif dan desa lain tidak pernah bocor).
    // Kosong → frontend menyembunyikan slider total.
    $heroSlides = $desa instanceof Desa
        ? HeroSlide::activeForDesa($desa)
            ->limit(HeroSlide::MAX_ACTIVE)
            ->get()
            ->map(fn (HeroSlide $slide): array => [
                'judul' => $slide->judul,
                'subjudul' => $slide->subjudul,
                'gambar_url' => $slide->gambar_path ? asset('storage/'.$slide->gambar_path) : null,
                'tautan_label' => $slide->tautan_label,
                'tautan_url' => $slide->tautan_url,
            ])->all()
        : [];

    return Inertia::render('welcome', [
        // Excerpt feed for the Beranda assembly in issue 18.
        'profilExcerpt' => [
            'sejarah' => $sejarah === '' ? null : Str::limit($sejarah, 200),
            'foto_url' => Media::url($profil?->foto_path),
            'urls' => [
                'sejarah' => route('profil.sejarah-visi-misi', [], false).'#sejarah',
                'visiMisi' => route('profil.sejarah-visi-misi', [], false).'#visi-misi',
            ],
        ],
        'beritaTerkini' => $beritaTerkini,
        'heroSlides' => $heroSlides,
        'meta' => Seo::meta(
            "Portal Resmi {$nama}",
            "Website resmi {$nama}: profil desa, layanan publik, berita terkini, lokasi kantor desa, dan Aduan warga.",
            route('home'),
        ),
        'schema' => Seo::websiteSchema(
            "Portal Resmi {$nama}",
            route('home'),
            "Website resmi {$nama}: profil desa, layanan publik, berita terkini, lokasi kantor desa, dan Aduan warga.",
        ),
        ...PublicSite::sharedProps($desa),
    ]);
})->name('home');

Route::get('profil/sejarah-visi-misi', [ProfilController::class, 'sejarahVisiMisi'])->name('profil.sejarah-visi-misi');
Route::get('profil/struktur-organisasi', [PejabatController::class, 'index'])->name('profil.struktur');

// Alias lama: satu halaman gabungan (lihat design "Sejarah & Visi misi.png")
// + struktur di bawah Profil — diteruskan 301 agar tautan lama tidak patah.
Route::get('profil/sejarah', fn () => redirect()->route('profil.sejarah-visi-misi', status: 301))->name('profil.sejarah');
Route::get('profil/visi-misi', fn () => redirect()->route('profil.sejarah-visi-misi', status: 301))->name('profil.visi-misi');

// Issue #15: Struktur Organisasi native (menggantikan mockup segera-hadir).
Route::get('struktur-pemerintahan', fn () => redirect()->route('profil.struktur', status: 301))->name('struktur');

// Issue #16: Berita end to end (pola baku untuk Pengumuman #17).
Route::get('berita', [BeritaController::class, 'index'])->name('berita.index');
Route::get('berita/{tahun}/{bulan}/{tanggal}/{slug}', [BeritaController::class, 'show'])
    ->where(['tahun' => '[0-9]{4}', 'bulan' => '[0-9]{2}', 'tanggal' => '[0-9]{2}', 'slug' => '[a-z0-9-]+'])
    ->name('berita.show');

// Issue #17: Pengumuman native (meniru pola Berita #16, versi ringan:
// URL slug-saja, tanpa filter kategori). Menggantikan mockup segera-hadir.
Route::get('pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
Route::get('pengumuman/{slug}', [PengumumanController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('pengumuman.show');

// Alias lama /segera-hadir/pengumuman diteruskan ke rute native (301).
Route::get('segera-hadir/pengumuman', function () {
    return redirect()->route('pengumuman.index', status: 301);
});

// Kegiatan native (meniru pola Pengumuman #17: URL slug-saja,
// tanpa filter kategori). Menggantikan mockup segera-hadir.
Route::get('kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
Route::get('kegiatan/{slug}', [KegiatanController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('kegiatan.show');

// Alias lama /segera-hadir/kegiatan diteruskan ke rute native (301).
Route::get('segera-hadir/kegiatan', function () {
    return redirect()->route('kegiatan.index', status: 301);
});

// Issue #16: sitemap otomatis memuat URL berita yang baru terbit.
// Issue #20: robots.txt sebagai route (bukan file statis) agar URL
// sitemap selalu absolut mengikuti APP_URL.
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Issue gabungan: halaman Informasi agregat Berita + Pengumuman
// (tab/filter/paginasi terpisah, bukan load semua sekaligus).
Route::get('informasi', [InformasiController::class, 'index'])->name('informasi');

// Modul belum-siap memakai slug kanonisnya sendiri (/pemerintahan,
// bukan /segera-hadir/...) dengan status under-development
// eksplisit — tidak pernah 404 / link mati / hash.
// (Pengumuman sudah native via issue #17, Informasi native via agregat.)
foreach ([
    'pemerintahan' => 'Pemerintahan',
    'lembaga-desa' => 'Lembaga Desa',
    'produk-hukum' => 'Produk Hukum',
    'laporan' => 'Laporan',
    'layanan-warga' => 'Layanan Warga',
    'layanan' => 'Layanan',
    'potensi-galeri' => 'Potensi & Galeri',
    'kontak-lokasi' => 'Kontak & Lokasi',
] as $slug => $label) {
    Route::get($slug, function () use ($slug, $label) {
        $desa = PublicSite::currentDesa();
        $nama = PublicSite::displayName($desa);
        $title = "{$label} {$nama}";
        $description = "Modul {$label} {$nama} sedang disiapkan dan akan segera hadir di portal resmi.";

        return Inertia::render('segera-hadir', [
            'modul' => $slug,
            'meta' => Seo::meta($title, $description, url($slug)),
            'schema' => Seo::collectionSchema($title, url($slug), $description),
            ...PublicSite::sharedProps($desa),
        ]);
    })->name("segera-hadir.{$slug}");
}

// Alias lama dipertahankan agar tautan lama tidak patah (tetap 200).
// Alias lama /segera-hadir/informasi diteruskan ke rute native (301).
Route::get('segera-hadir/informasi', function () {
    return redirect()->route('informasi', status: 301);
});
Route::get('segera-hadir/{modul}', function (string $modul) {
    $desa = PublicSite::currentDesa();
    $nama = PublicSite::displayName($desa);
    $title = "Segera Hadir di {$nama}";
    $description = "Modul {$modul} {$nama} sedang disiapkan dan akan segera hadir di portal resmi.";

    return Inertia::render('segera-hadir', [
        'modul' => $modul,
        'meta' => Seo::meta($title, $description, url("segera-hadir/{$modul}")),
        'schema' => Seo::collectionSchema($title, url("segera-hadir/{$modul}"), $description),
        ...PublicSite::sharedProps($desa),
    ]);
})->whereIn('modul', [
    'pemerintahan',
    'lembaga-desa',
    'produk-hukum',
    'laporan',
    'layanan-warga',
    'layanan',
    'potensi-galeri',
    'kontak-lokasi',
])->name('segera-hadir');

// Issue #19: form Aduan sederhana — simpan sederhana, tanpa disposisi.
Route::post('aduan', [AduanController::class, 'store'])->name('aduan.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['web', 'auth', 'verified'])->prefix(config('filament.path', 'admin'))->group(function () {
    Route::post('media/upload', [MediaController::class, 'upload'])->name('admin.media.upload');
});

require __DIR__.'/settings.php';
