<?php

namespace Tests\Feature\Berita;

use App\Filament\Resources\Beritas\Pages\CreateBerita;
use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kategori;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BeritaPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    public function test_slug_duplikat_satu_desa_ditolak(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = Kategori::create(['desa_id' => $desa->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);

        Berita::create([
            'desa_id' => $desa->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Panen Raya',
            'slug' => 'panen-raya',
            'isi' => '<p>Panen</p>',
            'status' => Berita::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        Berita::create([
            'desa_id' => $desa->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Panen Raya Lagi',
            'slug' => 'panen-raya',
            'isi' => '<p>Duplikat</p>',
            'status' => Berita::STATUS_DRAFT,
        ]);
    }

    public function test_slug_sama_beda_desa_lolos(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->firstOrFail();
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();

        $kategoriA = Kategori::create(['desa_id' => $desaA->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
        $kategoriB = Kategori::create(['desa_id' => $desaB->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);

        Berita::create([
            'desa_id' => $desaA->id,
            'kategori_id' => $kategoriA->id,
            'judul' => 'Panen Raya',
            'slug' => 'panen-raya',
            'isi' => '<p>Panen</p>',
            'status' => Berita::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $beritaB = Berita::create([
            'desa_id' => $desaB->id,
            'kategori_id' => $kategoriB->id,
            'judul' => 'Panen Raya',
            'slug' => 'panen-raya',
            'isi' => '<p>Panen B</p>',
            'status' => Berita::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->assertSame('panen-raya', $beritaB->slug);
    }

    private function makeUser(string $role, string $desaSlug): User
    {
        $desa = Desa::where('slug', $desaSlug)->firstOrFail();

        return User::factory()->create([
            'desa_id' => $desa->id,
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    private function makeKategori(Desa $desa): Kategori
    {
        return Kategori::create(['desa_id' => $desa->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
    }

    public function test_editor_bisa_simpan_draft(): void
    {
        $editor = $this->makeUser('editor', 'kepandean');
        $kategori = $this->makeKategori($editor->desa);

        $this->actingAs($editor, 'web');

        Livewire::test(CreateBerita::class)
            ->fillForm([
                'kategori_id' => $kategori->id,
                'judul' => 'Draft Editor',
                'slug' => 'draft-editor',
                'isi' => '<p>Draft</p>',
                'status' => Berita::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(1, Berita::forDesa($editor->desa)->where('slug', 'draft-editor')->count());
    }

    public function test_editor_publish_ditolak(): void
    {
        $editor = $this->makeUser('editor', 'kepandean');
        $kategori = $this->makeKategori($editor->desa);

        $this->actingAs($editor, 'web');

        Livewire::test(CreateBerita::class)
            ->fillForm([
                'kategori_id' => $kategori->id,
                'judul' => 'Coba Publish',
                'slug' => 'coba-publish',
                'isi' => '<p>Isi</p>',
                'status' => Berita::STATUS_PUBLISHED,
            ])
            ->call('create')
            ->assertForbidden();

        $this->assertSame(0, Berita::forDesa($editor->desa)->where('slug', 'coba-publish')->count());
    }

    public function test_admin_bisa_publish(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');
        $kategori = $this->makeKategori($admin->desa);

        $this->actingAs($admin, 'web');

        Livewire::test(CreateBerita::class)
            ->fillForm([
                'kategori_id' => $kategori->id,
                'judul' => 'Rilis Admin',
                'slug' => 'rilis-admin',
                'isi' => '<p>Rilis</p><script>alert(1)</script>',
                'status' => Berita::STATUS_PUBLISHED,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $berita = Berita::forDesa($admin->desa)->where('slug', 'rilis-admin')->firstOrFail();

        $this->assertTrue($berita->isPublished());
        $this->assertStringNotContainsString('<script', (string) $berita->isi);
    }

    private function makePublished(Desa $desa, Kategori $kategori, string $slug, string $judul = 'Judul'): Berita
    {
        return Berita::create([
            'desa_id' => $desa->id,
            'kategori_id' => $kategori->id,
            'judul' => $judul,
            'slug' => $slug,
            'isi' => '<p>Isi</p>',
            'status' => Berita::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    private function detailUrl(Berita $berita): string
    {
        $date = $berita->published_at ?? $berita->created_at ?? now();

        return "/berita/{$date->format('Y/m/d')}/{$berita->slug}";
    }

    public function test_index_hanya_tayang_published_dengan_filter_dan_paginasi(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kegiatan = Kategori::create(['desa_id' => $desa->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
        $info = Kategori::create(['desa_id' => $desa->id, 'nama' => 'Info', 'slug' => 'info']);

        $this->makePublished($desa, $kegiatan, 'berita-a', 'Berita A');
        $this->makePublished($desa, $info, 'berita-b', 'Berita B');
        Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kegiatan->id, 'judul' => 'Draft', 'slug' => 'draft-x',
            'isi' => '<p>Draft</p>', 'status' => Berita::STATUS_DRAFT,
        ]);

        $props = $this->get('/berita')->inertiaProps();
        $slugs = collect($props['berita']['data'])->pluck('slug')->all();

        $this->assertContains('berita-a', $slugs);
        $this->assertContains('berita-b', $slugs);
        $this->assertNotContains('draft-x', $slugs);

        $filtered = $this->get('/berita?kategori=kegiatan')->inertiaProps();
        $filteredSlugs = collect($filtered['berita']['data'])->pluck('slug')->all();

        $this->assertContains('berita-a', $filteredSlugs);
        $this->assertNotContains('berita-b', $filteredSlugs);
    }

    public function test_detail_tanggal_slug_dengan_meta_unik_dan_isi_bersih(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = $this->makeKategori($desa);
        $berita = Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id, 'judul' => 'Panen Raya',
            'slug' => 'panen-raya', 'isi' => '<p>Panen</p><script>alert(1)</script>',
            'status' => Berita::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $this->get($this->detailUrl($berita))
            ->assertOk()
            ->assertInertia(function ($page) use ($berita) {
                $page->component('berita/detail');
                $page->where('berita.judul', 'Panen Raya');
                $page->where('meta.title', "Panen Raya ({$berita->desa->name})");
                $page->has('meta.description');
            });

        $props = $this->get($this->detailUrl($berita))->inertiaProps();

        $this->assertStringNotContainsString('<script', (string) $props['berita']['isi']);
        $this->assertStringContainsString('Panen', (string) $props['berita']['isi']);
    }

    public function test_detail_draft_404(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = $this->makeKategori($desa);
        $berita = Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id, 'judul' => 'Draft',
            'slug' => 'draft-x', 'isi' => '<p>Draft</p>', 'status' => Berita::STATUS_DRAFT,
        ]);

        $this->get($this->detailUrl($berita))->assertNotFound();
        $this->get('/berita')->assertOk();
    }

    public function test_berita_desa_lain_tidak_bocor(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->firstOrFail();
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        $kategoriA = Kategori::create(['desa_id' => $desaA->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);
        $kategoriB = Kategori::create(['desa_id' => $desaB->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan']);

        $this->makePublished($desaB, $kategoriB, 'rahasia-b', 'Rahasia B');
        $rahasia = Berita::forDesa($desaB)->where('slug', 'rahasia-b')->firstOrFail();

        $props = $this->get('http://kepandean.test/berita')->inertiaProps();
        $slugs = collect($props['berita']['data'])->pluck('slug')->all();

        $this->assertNotContains('rahasia-b', $slugs);
        $this->get('http://kepandean.test'.$this->detailUrl($rahasia))->assertNotFound();
    }

    public function test_sitemap_memuat_yang_terbit_saja(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $kategori = $this->makeKategori($desa);
        $terbit = $this->makePublished($desa, $kategori, 'tayang', 'Tayang');
        Berita::create([
            'desa_id' => $desa->id, 'kategori_id' => $kategori->id, 'judul' => 'Draft',
            'slug' => 'draft-x', 'isi' => '<p>Draft</p>', 'status' => Berita::STATUS_DRAFT,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $body = $response->getContent();

        $this->assertStringContainsString($this->detailUrl($terbit), (string) $body);
        $this->assertStringNotContainsString('draft-x', (string) $body);
    }

    public function test_admin_bisa_buka_halaman_beritas_dan_kategoris(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');

        $this->actingAs($admin, 'web')->get('/admin/beritas')->assertOk();
        $this->actingAs($admin, 'web')->get('/admin/kategoris')->assertOk();
        $this->actingAs($admin, 'web')->get('/admin/beritas/create')->assertOk();
    }

    public function test_editor_bisa_buka_form_berita_untuk_draft(): void
    {
        $editor = $this->makeUser('editor', 'kepandean');

        $this->actingAs($editor, 'web')->get('/admin/beritas')->assertOk();
        $this->actingAs($editor, 'web')->get('/admin/beritas/create')->assertOk();
    }
}
