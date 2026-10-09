<?php

namespace Tests\Feature\Informasi;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kategori;
use App\Models\Pengumuman;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    private function publishBerita(Desa $desa, string $slug): Berita
    {
        $kategori = Kategori::create(['desa_id' => $desa->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan-'.$slug]);

        return Berita::create([
            'desa_id' => $desa->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Berita '.$slug,
            'slug' => $slug,
            'isi' => '<p>Isi '.$slug.'</p>',
            'status' => Berita::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    private function publishPengumuman(Desa $desa, string $slug): Pengumuman
    {
        return Pengumuman::create([
            'desa_id' => $desa->id,
            'judul' => 'Pengumuman '.$slug,
            'slug' => $slug,
            'isi' => '<p>Isi '.$slug.'</p>',
            'status' => Pengumuman::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function test_informasi_agregat_berita_dan_pengumuman_dengan_tab_terpisah(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $this->publishBerita($desa, 'berita-a');
        $this->publishPengumuman($desa, 'pengumuman-a');

        $beritaTab = $this->get('/informasi?tab=berita')->inertiaProps();
        $this->assertSame('berita', $beritaTab['tab']);
        $this->assertContains(
            'berita-a',
            collect($beritaTab['berita']['data'])->pluck('slug')->all()
        );

        $pengumumanTab = $this->get('/informasi?tab=pengumuman')->inertiaProps();
        $this->assertSame('pengumuman', $pengumumanTab['tab']);
        $this->assertContains(
            'pengumuman-a',
            collect($pengumumanTab['pengumuman']['data'])->pluck('slug')->all()
        );

        $this->get('/informasi')
            ->assertOk()
            ->assertInertia(function ($page): void {
                $page->component('informasi/index');
                $page->has('berita.data');
                $page->has('pengumuman.data');
                $page->has('counts.berita');
                $page->has('counts.pengumuman');
            });
    }

    public function test_informasi_tidak_memuat_draft_dan_hanya_desa_aktif(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->firstOrFail();
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();

        $kategori = Kategori::create(['desa_id' => $desaA->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
        Berita::create([
            'desa_id' => $desaA->id, 'kategori_id' => $kategori->id, 'judul' => 'Draft',
            'slug' => 'draft-x', 'isi' => '<p>Draft</p>', 'status' => Berita::STATUS_DRAFT,
        ]);
        $this->publishBerita($desaB, 'rahasia-b');

        $props = $this->get('http://kepandean.test/informasi')->inertiaProps();
        $slugs = collect($props['berita']['data'])->pluck('slug')->all();

        $this->assertNotContains('draft-x', $slugs);
        $this->assertNotContains('rahasia-b', $slugs);
    }

    public function test_informasi_nav_memakai_url_kanonis(): void
    {
        $props = $this->get('/')->inertiaProps();

        $hrefs = collect($props['site']['nav'] ?? [])
            ->flatMap(fn (array $item): array => array_merge(
                [$item['href'] ?? ''],
                array_column($item['children'] ?? [], 'href')
            ))
            ->all();

        $this->assertContains('/informasi', $hrefs);
        $this->assertNotContains('/segera-hadir/informasi', $hrefs);
    }
}
