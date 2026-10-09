<?php

namespace App\Support;

use App\Models\Desa;
use App\Models\LamanHero;
use App\Models\MenuItem;
use App\Models\Profil;
use App\Models\Statistik;
use Illuminate\Support\Facades\App;

/**
 * Satu-satunya tempat resolusi desa + props publik bersama (issue #18/#19).
 *
 * Menggantikan duplikasi `App::bound('current_desa') ... Desa::getDefault()`
 * yang tersebar di routes + controller. Data kunci mengikuti docs/DESIGN.md
 * (spec-002 menang atas placeholder desain).
 */
class PublicSite
{
    public static function currentDesa(): ?Desa
    {
        $desa = App::bound('current_desa') ? App::make('current_desa') : Desa::getDefault();

        return $desa instanceof Desa ? $desa : null;
    }

    public static function displayName(?Desa $desa): string
    {
        return $desa instanceof Desa ? $desa->name : 'Desa Kepandean';
    }

    /**
     * Item nav mockup: selalu route nyata ke slug kanonis modul
     * (/pengumuman, bukan /segera-hadir/pengumuman), tidak pernah
     * href="#", IP intranet, atau placeholder. Label mengikuti Homepage.png.
     *
     * Menu dinamis (issue #19 checklist 3, tabel `menu_items`) tampil
     * SETELAH menu bawaan, terurut `urutan`; mockup bawaan tidak berubah
     * sampai modulnya native.
     *
     * @return array<int, array{label: string, href: string, children?: array<int, array{label: string, href: string}>}>
     */
    public static function nav(): array
    {
        $soon = fn (string $modul): string => route("segera-hadir.{$modul}", [], false);

        return [
            ['label' => 'Beranda', 'href' => route('home', [], false)],
            [
                'label' => 'Profil Desa',
                'href' => route('profil.sejarah-visi-misi', [], false),
                'children' => [
                    ['label' => 'Sejarah & Visi Misi', 'href' => route('profil.sejarah-visi-misi', [], false)],
                    ['label' => 'Struktur Organisasi', 'href' => route('profil.struktur', [], false)],
                ],
            ],
            [
                'label' => 'Pemerintahan',
                'href' => $soon('lembaga-desa'),
                'children' => [
                    ['label' => 'Lembaga Desa', 'href' => $soon('lembaga-desa')],
                    ['label' => 'Produk Hukum', 'href' => $soon('produk-hukum')],
                    ['label' => 'Laporan', 'href' => $soon('laporan')],
                ],
            ],
            ['label' => 'Layanan Warga', 'href' => $soon('layanan-warga')],
            [
                'label' => 'Informasi',
                'href' => route('informasi', [], false),
                'children' => [
                    ['label' => 'Berita Desa', 'href' => route('berita.index', [], false)],
                    ['label' => 'Pengumuman', 'href' => route('pengumuman.index', [], false)],
                    ['label' => 'Kegiatan', 'href' => route('kegiatan.index', [], false)],
                ],
            ],
            ['label' => 'Potensi & Galeri', 'href' => $soon('potensi-galeri')],
            ['label' => 'Kontak & Lokasi', 'href' => $soon('kontak-lokasi')],
            ...self::dynamicMenuItems(),
        ];
    }

    /**
     * @return array<int, array{label: string, href: string, children?: array<int, array{label: string, href: string}>}>
     */
    private static function dynamicMenuItems(): array
    {
        $desa = static::currentDesa();

        if (! $desa instanceof Desa) {
            return [];
        }

        $top = MenuItem::forDesa($desa)
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->orderBy('id')
            ->with('children')
            ->get();

        $items = [];

        foreach ($top as $menu) {
            $entry = ['label' => $menu->label, 'href' => $menu->navHref()];

            $children = [];

            foreach ($menu->children as $child) {
                // Pertahanan terakhir anti-siklus: anak yang menunjuk
                // dirinya sendiri tidak dirender.
                if ((int) $child->getKey() === (int) $menu->getKey()) {
                    continue;
                }

                $children[] = ['label' => $child->label, 'href' => $child->navHref()];
            }

            if ($children !== []) {
                $entry['children'] = $children;
            }

            $items[] = $entry;
        }

        return $items;
    }

    /**
     * Lokasi kanonis spec-002. Tanpa `luas`: UNCONFIRMED, jangan tampil.
     * Surel + alamat mengikuti design/Homepage.png dan BUTUH konfirmasi
     * perangkat desa (lihat docs/DESIGN.md) — bukan hasil karangan.
     *
     * peta_url adalah tautan tombol "Buka Peta Digital" (blank ke tab
     * baru); peta_embed adalah fallback <noscript> OSM agar alamat +
     * tombol tetap ada bila JS mati. Peta interaktif memakai Leaflet
     * client-only dengan latitude/longitude di bawah + tile OSM.
     *
     * Satu-satunya tempat ganti lokasi peta: ubah koordinat + peta_url
     * di bawah — frontend Inertia (welcome.tsx) hanya membaca props
     * `lokasi`, tidak ada URL peta yang di-hardcode di sana.
     *
     * @return array{kode_pos: string, koordinat: string, latitude: float, longitude: float, alamat: string, peta_url: string, peta_embed: string, surel: string}
     */
    public static function lokasi(): array
    {
        return [
            'kode_pos' => '52192',
            'koordinat' => '-6.902522, 109.114750',
            'latitude' => -6.902522,
            'longitude' => 109.114750,
            'alamat' => 'Kec. Dukuhturi, Kab. Tegal, Jawa Tengah',
            'peta_url' => 'https://maps.app.goo.gl/S6XXrMWLvmspNv5N9',
            'peta_embed' => 'https://www.openstreetmap.org/export/embed.html?bbox=109.094750%2C-6.912522%2C109.134750%2C-6.892522&layer=mapnik&marker=-6.902522%2C109.114750',
            'surel' => 'pemdes@kepandean.desa.id',
        ];
    }

    /**
     * Kontak footer: telepon butuh konfirmasi perangkat desa — teks jujur,
     * bukan `xxx` (aturan: tidak ada data yang dikarang). Jam pelayanan +
     * sosmed mengikuti design/Homepage.png dan ikut BUTUH konfirmasi
     * (lihat docs/DESIGN.md).
     *
     * @return array{telepon: string, telepon_status: string, surel: string, jam: array<int, array{hari: string, jam: string}>, sosmed: array<int, array{label: string, href: string}>}
     */
    public static function kontak(): array
    {
        return [
            'telepon' => '+62 817-7046-1804',
            'telepon_status' => 'confirmed',
            'surel' => 'pemdes@kepandean.desa.id',
            'jam' => [
                ['hari' => 'Senin - Kamis', 'jam' => '08.00 - 15.00 WIB'],
                ['hari' => 'Jumat', 'jam' => '08.00 - 11.30 WIB'],
                ['hari' => 'Sabtu - Minggu', 'jam' => 'Libur Operasional'],
            ],
            'sosmed' => [
                ['label' => 'Facebook Balai Desa Kepandean', 'href' => 'https://www.facebook.com/'],
            ],
        ];
    }

    /**
     * Kolom tautan footer. Satu-satunya sumber di backend agar perubahan
     * struktur tidak diedit di dua tempat (nav utama vs footer).
     *
     * @return array{layanan: array<int, array{label: string, href: string}>, cepat: array<int, array{label: string, href: string}>}
     */
    public static function footer(): array
    {
        $layanan = fn (): string => route('segera-hadir.layanan-warga', [], false);

        return [
            'layanan' => [
                ['label' => 'Alur Surat Digital', 'href' => $layanan()],
                ['label' => 'Cek Tagihan PBB', 'href' => $layanan()],
                ['label' => 'Cek DPT Online', 'href' => $layanan()],
                ['label' => 'Layanan Mandiri Warga', 'href' => $layanan()],
                ['label' => 'Aduan Warga', 'href' => '/#aduan'],
            ],
            'cepat' => [
                ['label' => 'Profil Desa', 'href' => route('profil.sejarah-visi-misi', [], false)],
                ['label' => 'Sejarah & Visi Misi', 'href' => route('profil.sejarah-visi-misi', [], false)],
                ['label' => 'Struktur Organisasi', 'href' => route('profil.struktur', [], false)],
                ['label' => 'Peta Desa', 'href' => route('segera-hadir.kontak-lokasi', [], false)],
                ['label' => 'Transparansi APBDes', 'href' => route('informasi', [], false)],
            ],
        ];
    }

    /**
     * Jalur internal untuk sitemap, diturunkan dari nav agar laman native
     * baru otomatis masuk sitemap tanpa edit kedua.
     *
     * @return array<int, string>
     */
    public static function sitemapPaths(): array
    {
        $paths = ['/'];

        $collect = function (array $items) use (&$collect, &$paths): void {
            foreach ($items as $item) {
                if (is_string($item['href'] ?? null) && str_starts_with($item['href'], '/')) {
                    $paths[] = $item['href'];
                }

                if (isset($item['children']) && is_array($item['children'])) {
                    $collect($item['children']);
                }
            }
        };

        $collect(static::nav());

        return array_values(array_unique($paths));
    }

    /** @return array<string, mixed> */
    public static function sharedProps(?Desa $desa = null): array
    {
        $desa ??= static::currentDesa();
        $nama = $desa instanceof Desa ? $desa->name : 'Desa Kepandean';

        // Foto sejarah sebagai latar hero cadangan tiap halaman dalam (satu query ringan, sama untuk semua halaman).
        $profilMedia = $desa instanceof Desa
            ? Profil::withoutGlobalScope('desa')->where('desa_id', $desa->id)->first(['foto_path', 'logo_path'])
            : null;
        $fotoPath = $profilMedia?->foto_path;
        $logoPath = $profilMedia?->logo_path;

        // Hero tiap laman yang diatur admin (slug => URL). Kosong berarti
        // frontend memakai foto konten, lalu foto sejarah sebagai cadangan.
        $heroLaman = [];

        if ($desa instanceof Desa) {
            $heroLaman = LamanHero::forDesa($desa)->get()->mapWithKeys(
                fn (LamanHero $hero): array => [$hero->slug => asset('storage/'.$hero->gambar_path)],
            )->all();
        }

        return [
            'desa' => $desa instanceof Desa ? [
                'name' => $desa->name,
                'slug' => $desa->slug,
            ] : null,
            'site' => [
                'nama' => $nama,
                'nav' => static::nav(),
                'footer' => static::footer(),
                'kontak' => static::kontak(),
                'logo_url' => Media::url($profilMedia?->logo_path),
                // Varian persegi anti-stretch; null berarti blade memakai
                // favicon statis.
                'favicon_url' => Favicon::url(is_string($logoPath) && $logoPath !== '' ? $logoPath : null),
                'hero_fallback_url' => is_string($fotoPath) && $fotoPath !== ''
                    ? asset('storage/'.$fotoPath)
                    : null,
                'hero_laman' => $heroLaman,
            ],
            'lokasi' => static::lokasi(),
            'statistik' => Statistik::currentMap(),
            'analytics' => [
                'ga_id' => config('analytics.ga_id'),
                'search_console_verification' => config('analytics.search_console_verification'),
            ],
        ];
    }
}
