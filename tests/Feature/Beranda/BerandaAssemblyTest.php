<?php

namespace Tests\Feature\Beranda;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\HeroSlide;
use App\Models\Kategori;
use App\Models\Profil;
use App\Models\Statistik;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #18 slice: Beranda assembly + Statistik + Lokasi + Footer.
 *
 * Seam: HTTP Feature only (spec-002 Testing Decisions).
 */
class BerandaAssemblyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    public function test_home_renders_welcome_with_locked_props(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Profil::forDesa($desa)->update(['sejarah' => '<p>Cuplikan.</p>']);
        Statistik::seedDefaults($desa);

        $this->get('/')
            ->assertOk()
            ->assertInertia(function ($page) {
                $page->component('welcome');
                $page->has('profilExcerpt');
                $page->has('beritaTerkini');
                $page->has('heroSlides');
                $page->has('statistik');
                $page->has('lokasi');
                $page->has('site');
                $page->has('meta.title');
                $page->has('meta.description');
            });
    }

    public function test_home_meta_is_unique_per_spec(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $props = $response->inertiaProps();
        $title = (string) ($props['meta']['title'] ?? '');
        $desc = (string) ($props['meta']['description'] ?? '');

        $this->assertStringContainsString('Kepandean', $title);
        $this->assertNotSame('', trim($desc));
    }

    public function test_statistik_update_reflects_on_home_without_developer(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Statistik::seedDefaults($desa);

        Statistik::forDesa($desa)->where('kunci', 'total_jiwa')->update(['nilai' => '7001']);

        $props = $this->get('/')->inertiaProps();

        $this->assertSame('7001', (string) ($props['statistik']['total_jiwa'] ?? ''));
    }

    public function test_lokasi_uses_canonical_spec_values(): void
    {
        $props = $this->get('/')->inertiaProps();
        $lokasi = $props['lokasi'] ?? [];

        $this->assertSame('52192', (string) ($lokasi['kode_pos'] ?? ''));
        $this->assertStringContainsString('-6.902522', (string) ($lokasi['koordinat'] ?? ''));
        $this->assertStringContainsString('109.114750', (string) ($lokasi['koordinat'] ?? ''));
        // Koordinat numerik untuk peta Leaflet client-only.
        $this->assertEqualsWithDelta(-6.902522, (float) ($lokasi['latitude'] ?? 0), 0.000001);
        $this->assertEqualsWithDelta(109.114750, (float) ($lokasi['longitude'] ?? 0), 0.000001);
        // Luas belum terkonfirmasi: tidak boleh tampil.
        $this->assertArrayNotHasKey('luas', $lokasi);
    }

    public function test_lokasi_peta_blanks_to_gmaps_and_embeds_directly(): void
    {
        $props = $this->get('/')->inertiaProps();
        $lokasi = $props['lokasi'] ?? [];

        // Tombol "Buka Peta Digital" blank ke Google Maps di tab baru —
        // bukan OSM /search yang memicu error Nominatim 400.
        $this->assertSame('https://maps.app.goo.gl/S6XXrMWLvmspNv5N9', (string) ($lokasi['peta_url'] ?? ''));

        // Peta inline memakai export/embed.html agar selalu ter-render
        // tanpa JS Leaflet di sisi klien.
        $this->assertStringContainsString('/export/embed.html', (string) ($lokasi['peta_embed'] ?? ''));
        $this->assertStringContainsString('marker=-6.902522', (string) ($lokasi['peta_embed'] ?? ''));
    }

    public function test_nav_groups_struktur_under_profil_and_pemerintahan_lists_lembaga_produk_laporan(): void
    {
        $props = $this->get('/')->inertiaProps();
        $items = collect($props['site']['nav'] ?? []);

        $profil = $items->firstWhere('label', 'Profil Desa');
        $pemerintahan = $items->firstWhere('label', 'Pemerintahan');

        $this->assertNotEmpty($profil['children'] ?? []);
        $this->assertContains('Struktur Organisasi', collect($profil['children'])->pluck('label')->all());

        $pemLabels = collect($pemerintahan['children'] ?? [])->pluck('label')->all();
        $this->assertContains('Lembaga Desa', $pemLabels);
        $this->assertContains('Produk Hukum', $pemLabels);
        $this->assertContains('Laporan', $pemLabels);
        $this->assertNotContains('Struktur Organisasi', $pemLabels);
    }

    public function test_nav_has_no_dead_links_or_intranet_ips(): void
    {
        $props = $this->get('/')->inertiaProps();
        $items = $props['site']['nav'] ?? [];

        $this->assertNotEmpty($items);

        $joined = json_encode($items);

        $this->assertStringNotContainsString('#', $joined);
        $this->assertStringNotContainsString('192.168.', $joined);
        $this->assertStringNotContainsString('xxx', strtolower($joined));
    }

    public function test_other_desa_statistik_never_leaks_to_home(): void
    {
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        Statistik::seedDefaults($desaB);
        Statistik::forDesa($desaB)->where('kunci', 'total_jiwa')->update(['nilai' => '9999']);

        $props = $this->get('http://kepandean.test/')->inertiaProps();

        $this->assertNotSame('9999', (string) ($props['statistik']['total_jiwa'] ?? ''));
    }

    public function test_berita_terkini_syncs_with_published_only(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = Kategori::create(['desa_id' => $desa->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
        Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id, 'judul' => 'Musyawarah Desa',
            'slug' => 'musyawarah-desa', 'isi' => '<p>Isi</p>',
            'status' => Berita::STATUS_PUBLISHED, 'published_at' => now(),
        ]);
        Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id, 'judul' => 'Masih Draft',
            'slug' => 'masih-draft', 'isi' => '<p>Draft</p>', 'status' => Berita::STATUS_DRAFT,
        ]);

        $props = $this->get('/')->inertiaProps();
        $items = collect($props['beritaTerkini'] ?? []);

        $this->assertCount(1, $items);
        $this->assertSame('Musyawarah Desa', $items->first()['judul']);
        $this->assertStringContainsString('musyawarah-desa', $items->first()['url']);
    }

    public function test_berita_terkini_never_leaks_other_desa(): void
    {
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        $kategoriB = Kategori::create(['desa_id' => $desaB->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
        Berita::create([
            'desa_id' => $desaB->id, 'kategori_id' => $kategoriB->id, 'judul' => 'Rahasia B',
            'slug' => 'rahasia-b', 'isi' => '<p>B</p>',
            'status' => Berita::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $props = $this->get('http://kepandean.test/')->inertiaProps();

        $this->assertSame([], $props['beritaTerkini'] ?? null);
    }

    public function test_berita_terkini_empty_without_published(): void
    {
        $props = $this->get('/')->inertiaProps();

        $this->assertSame([], $props['beritaTerkini'] ?? null);
    }

    public function test_hero_slider_empty_by_default(): void
    {
        $props = $this->get('/')->inertiaProps();

        $this->assertSame([], $props['heroSlides'] ?? null);
    }

    public function test_hero_slider_syncs_max_five_active_in_order(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        for ($i = 6; $i >= 1; $i--) {
            HeroSlide::create([
                'desa_id' => $desa->id,
                'judul' => "Slide {$i}",
                'urutan' => $i,
                'aktif' => true,
            ]);
        }
        HeroSlide::create([
            'desa_id' => $desa->id,
            'judul' => 'Nonaktif',
            'urutan' => 0,
            'aktif' => false,
        ]);

        $props = $this->get('/')->inertiaProps();
        $items = collect($props['heroSlides'] ?? []);

        // Maksimal 5 aktif, nonaktif disembunyikan, urut menaik.
        $this->assertCount(5, $items);
        $this->assertSame(
            ['Slide 1', 'Slide 2', 'Slide 3', 'Slide 4', 'Slide 5'],
            $items->pluck('judul')->all()
        );
        $this->assertNotContains('Nonaktif', $items->pluck('judul')->all());
    }

    public function test_hero_slider_never_leaks_other_desa(): void
    {
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        HeroSlide::create([
            'desa_id' => $desaB->id,
            'judul' => 'Rahasia B',
            'urutan' => 0,
            'aktif' => true,
        ]);

        $props = $this->get('http://kepandean.test/')->inertiaProps();

        $this->assertSame([], $props['heroSlides'] ?? null);
    }

    public function test_hero_slider_maps_gambar_and_tautan(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        HeroSlide::create([
            'desa_id' => $desa->id,
            'judul' => 'Musyawarah',
            'subjudul' => 'Sub',
            'tautan_label' => 'Selengkapnya',
            'tautan_url' => '/profil/sejarah-visi-misi',
            'urutan' => 0,
            'aktif' => true,
        ]);

        $props = $this->get('/')->inertiaProps();
        $item = collect($props['heroSlides'] ?? [])->first();

        $this->assertSame('Musyawarah', $item['judul']);
        $this->assertSame('Sub', $item['subjudul']);
        $this->assertSame('Selengkapnya', $item['tautan_label']);
        $this->assertSame('/profil/sejarah-visi-misi', $item['tautan_url']);
    }

    public function test_public_pages_all_render_for_footer_parity(): void
    {
        $this->get('/')->assertOk();
        $this->get('/profil/sejarah-visi-misi')->assertOk();
        $this->get('/profil/struktur-organisasi')->assertOk();
        $this->get('/segera-hadir/layanan')->assertOk();
        $this->get('/produk-hukum')->assertOk();
        $this->get('/laporan')->assertOk();
        $this->get('/lembaga-desa')->assertOk();
    }

    public function test_segera_hadir_page_is_honest_under_development(): void
    {
        $this->get('/segera-hadir/layanan')
            ->assertOk()
            ->assertInertia(function ($page) {
                $page->component('segera-hadir');
                $page->where('modul', 'layanan');
            });
    }
}
