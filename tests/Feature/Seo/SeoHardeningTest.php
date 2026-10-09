<?php

namespace Tests\Feature\Seo;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kategori;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #20 close-out: gerbang rilis SEO plus performa plus hardening.
 *
 * Gate otomatis yang diminta issue: meta unik per halaman, sitemap
 * memuat URL baru, robots valid, dan script suntikan ter-strip.
 */
class SeoHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    private function makeKategori(Desa $desa): Kategori
    {
        return Kategori::create(['desa_id' => $desa->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
    }

    public function test_public_pages_have_unique_meta_with_canonical(): void
    {
        $urls = [
            '/',
            '/berita',
            '/pengumuman',
            '/kegiatan',
            '/informasi',
            '/profil/sejarah-visi-misi',
            '/profil/struktur-organisasi',
        ];

        $titles = [];
        $descriptions = [];

        foreach ($urls as $url) {
            $props = $this->get($url)->assertOk()->inertiaProps();

            $title = (string) ($props['meta']['title'] ?? '');
            $description = (string) ($props['meta']['description'] ?? '');
            $canonical = (string) ($props['meta']['canonical_url'] ?? '');

            $this->assertNotSame('', $title, "Judul meta kosong untuk {$url}.");
            $this->assertNotSame('', $description, "Deskripsi meta kosong untuk {$url}.");
            $this->assertNotSame('', $canonical, "URL kanonis kosong untuk {$url}.");
            $this->assertStringNotContainsString('<script', $title.$description);

            $titles[] = $title;
            $descriptions[] = $description;
        }

        $this->assertSame(count($titles), count(array_unique($titles)), 'Judul meta halaman publik harus unik.');
        $this->assertSame(count($descriptions), count(array_unique($descriptions)), 'Deskripsi meta halaman publik harus unik.');
    }

    public function test_detail_pages_carry_unique_meta_and_article_schema(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = $this->makeKategori($desa);

        $berita = Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id,
            'judul' => 'Panen Raya Akbar', 'slug' => 'panen-raya-akbar',
            'isi' => '<p>Panen raya di sawah desa.</p>',
            'status' => Berita::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $pengumuman = Pengumuman::create([
            'desa_id' => $desa->id, 'judul' => 'Jadwal Posyandu Mei',
            'slug' => 'jadwal-posyandu-mei', 'isi' => '<p>Jadwal posyandu.</p>',
            'status' => Pengumuman::STATUS_PUBLISHED, 'published_at' => now()->subDay(),
        ]);

        $kegiatan = Kegiatan::create([
            'desa_id' => $desa->id, 'judul' => 'Kerja Bakti Gabungan',
            'slug' => 'kerja-bakti-gabungan', 'isi' => '<p>Kerja bakti.</p>',
            'status' => Kegiatan::STATUS_PUBLISHED, 'published_at' => now()->subDay(),
        ]);

        $date = $berita->published_at ?? now();
        $detailUrls = [
            "/berita/{$date->format('Y/m/d')}/panen-raya-akbar",
            '/pengumuman/jadwal-posyandu-mei',
            '/kegiatan/kerja-bakti-gabungan',
        ];

        $titles = [];

        foreach ($detailUrls as $url) {
            $props = $this->get($url)->assertOk()->inertiaProps();

            $title = (string) ($props['meta']['title'] ?? '');
            $canonical = (string) ($props['meta']['canonical_url'] ?? '');
            $schema = is_array($props['schema'] ?? null) ? $props['schema'] : [];

            $this->assertNotSame('', $title);
            $this->assertNotSame('', $canonical);
            $this->assertSame('NewsArticle', $schema['@type'] ?? null, "Schema {$url} harus NewsArticle.");

            $titles[] = $title;
        }

        $this->assertSame(count($titles), count(array_unique($titles)), 'Judul meta halaman detail harus unik.');

        $this->assertStringContainsString('Panen Raya Akbar', $titles[0]);
        $this->assertStringContainsString((string) $pengumuman->judul, $titles[1]);
        $this->assertStringContainsString((string) $kegiatan->judul, $titles[2]);
    }

    public function test_sitemap_includes_newly_published_url_with_cache_headers(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = $this->makeKategori($desa);

        Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id,
            'judul' => 'Rilis Sitemap Baru', 'slug' => 'rilis-sitemap-baru',
            'isi' => '<p>Berita baru untuk sitemap.</p>',
            'status' => Berita::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $cacheControl = (string) $response->headers->get('Cache-Control', '');
        $this->assertStringContainsString('public', $cacheControl);

        $body = (string) $response->getContent();
        $this->assertStringContainsString('rilis-sitemap-baru', $body);
        $this->assertStringContainsString('<loc>', $body);
    }

    public function test_robots_is_valid_and_points_to_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $body = (string) $response->getContent();

        $this->assertStringContainsString('User-agent:', $body);
        $this->assertStringContainsString('Disallow: /admin/', $body);
        $this->assertStringContainsString('Sitemap:', $body);
        $this->assertStringContainsString('/sitemap.xml', $body);
    }

    public function test_injected_scripts_are_stripped_from_public_props(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = $this->makeKategori($desa);

        Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id,
            'judul' => 'Uji Suntikan', 'slug' => 'uji-suntikan',
            'isi' => '<p>Konten asli</p><script>alert(1)</script>',
            'status' => Berita::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $date = now();
        $props = $this->get("/berita/{$date->format('Y/m/d')}/uji-suntikan")->assertOk()->inertiaProps();

        $this->assertStringNotContainsString('<script', (string) ($props['berita']['isi'] ?? ''));
        $this->assertStringContainsString('Konten asli', (string) ($props['berita']['isi'] ?? ''));
    }

    public function test_login_throttle_rejects_repeated_failures(): void
    {
        $user = User::factory()->create();

        $response = null;

        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'salah-beruntun',
            ]);
        }

        $this->assertNotNull($response);
        $response->assertTooManyRequests();
        $this->assertGuest();
    }
}
