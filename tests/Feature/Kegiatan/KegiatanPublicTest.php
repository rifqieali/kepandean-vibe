<?php

namespace Tests\Feature\Kegiatan;

use App\Models\Desa;
use App\Models\Kegiatan;
use App\Models\LamanHero;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kegiatan meniru pola Pengumuman (#17): URL slug-saja, kedaluarsa =
 * hilang dari publik. Plus hero laman yang diatur admin.
 */
class KegiatanPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    private function makePublished(Desa $desa, string $slug, string $judul = 'Judul', ?string $expiredAt = null): Kegiatan
    {
        return Kegiatan::create([
            'desa_id' => $desa->id,
            'judul' => $judul,
            'slug' => $slug,
            'isi' => '<p>Isi</p>',
            'status' => Kegiatan::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'expired_at' => $expiredAt,
        ]);
    }

    public function test_index_hanya_tayang_yang_tayang(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $this->makePublished($desa, 'tayang-a', 'Tayang A');
        $this->makePublished($desa, 'basi', 'Basi', now()->subDay()->toDateTimeString());
        Kegiatan::create([
            'desa_id' => $desa->id, 'judul' => 'Draft', 'slug' => 'draft-x',
            'isi' => '<p>Draft</p>', 'status' => Kegiatan::STATUS_DRAFT,
        ]);

        $props = $this->get('/kegiatan')->inertiaProps();
        $slugs = collect($props['kegiatan']['data'])->pluck('slug')->all();

        $this->assertContains('tayang-a', $slugs);
        $this->assertNotContains('basi', $slugs);
        $this->assertNotContains('draft-x', $slugs);
    }

    public function test_detail_tayang_dan_draft_kedaluarsa_404(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $this->makePublished($desa, 'kerja-bakti', 'Kerja Bakti');
        Kegiatan::create([
            'desa_id' => $desa->id, 'judul' => 'Draft', 'slug' => 'draft-x',
            'isi' => '<p>Draft</p>', 'status' => Kegiatan::STATUS_DRAFT,
        ]);
        $this->makePublished($desa, 'basi', 'Basi', now()->subDay()->toDateTimeString());

        $this->get('/kegiatan/kerja-bakti')
            ->assertOk()
            ->assertInertia(function ($page) use ($desa): void {
                $page->component('kegiatan/detail');
                $page->where('kegiatan.judul', 'Kerja Bakti');
                $page->where('meta.title', "Kerja Bakti ({$desa->name})");
            });

        $this->get('/kegiatan/draft-x')->assertNotFound();
        $this->get('/kegiatan/basi')->assertNotFound();
    }

    public function test_kegiatan_masuk_tab_informasi_dan_dropdown_nav(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $this->makePublished($desa, 'posyandu', 'Posyandu');

        $tab = $this->get('/informasi?tab=kegiatan')->inertiaProps();
        $this->assertSame('kegiatan', $tab['tab']);
        $this->assertContains('posyandu', collect($tab['kegiatan']['data'])->pluck('slug')->all());
        $this->assertSame(1, $tab['counts']['kegiatan']);

        $props = $this->get('/')->inertiaProps();
        $hrefs = collect($props['site']['nav'] ?? [])
            ->flatMap(fn (array $item): array => array_merge(
                [$item['href'] ?? ''],
                array_column($item['children'] ?? [], 'href')
            ))
            ->all();

        $this->assertContains('/kegiatan', $hrefs);
    }

    public function test_alias_lama_kegiatan_diteruskan_ke_native(): void
    {
        $this->get('/segera-hadir/kegiatan')->assertStatus(301)->assertRedirect('/kegiatan');
    }

    public function test_hero_laman_admin_masuk_props_bersama(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        LamanHero::create([
            'desa_id' => $desa->id,
            'slug' => 'berita',
            'gambar_path' => 'hero-laman/berita.webp',
        ]);

        $props = $this->get('/berita')->inertiaProps();

        $this->assertStringEndsWith(
            '/storage/hero-laman/berita.webp',
            (string) $props['site']['hero_laman']['berita']
        );
    }
}
