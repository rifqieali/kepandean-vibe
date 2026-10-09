<?php

namespace Tests\Feature\Pengumuman;

use App\Filament\Resources\Pengumumans\Pages\CreatePengumuman;
use App\Models\Desa;
use App\Models\Pengumuman;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Issue #17: Pengumuman meniru pola Berita (#16), versi ringan (tanpa kategori).
 * Aturan kedaluarsa yang dipilih: kedaluarsa = hilang (tak tampil di index,
 * detail 404) — bukan berlabel.
 */
class PengumumanPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
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

    private function makePublished(Desa $desa, string $slug, string $judul = 'Judul', ?string $expiredAt = null): Pengumuman
    {
        return Pengumuman::create([
            'desa_id' => $desa->id,
            'judul' => $judul,
            'slug' => $slug,
            'isi' => '<p>Isi</p>',
            'status' => Pengumuman::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'expired_at' => $expiredAt,
        ]);
    }

    public function test_slug_duplikat_satu_desa_ditolak(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $this->makePublished($desa, 'jadwal-posyandu');

        $this->expectException(QueryException::class);

        Pengumuman::create([
            'desa_id' => $desa->id,
            'judul' => 'Duplikat',
            'slug' => 'jadwal-posyandu',
            'isi' => '<p>Duplikat</p>',
            'status' => Pengumuman::STATUS_DRAFT,
        ]);
    }

    public function test_slug_sama_beda_desa_lolos(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->firstOrFail();
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();

        $this->makePublished($desaA, 'jadwal-posyandu');

        $pengumumanB = Pengumuman::create([
            'desa_id' => $desaB->id,
            'judul' => 'Jadwal Posyandu',
            'slug' => 'jadwal-posyandu',
            'isi' => '<p>Isi B</p>',
            'status' => Pengumuman::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->assertSame('jadwal-posyandu', $pengumumanB->slug);
    }

    public function test_index_hanya_tayang_yang_tayang(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $this->makePublished($desa, 'tayang-a', 'Tayang A');
        $this->makePublished($desa, 'basi', 'Basi', now()->subDay()->toDateTimeString());
        Pengumuman::create([
            'desa_id' => $desa->id, 'judul' => 'Draft', 'slug' => 'draft-x',
            'isi' => '<p>Draft</p>', 'status' => Pengumuman::STATUS_DRAFT,
        ]);

        $props = $this->get('/pengumuman')->inertiaProps();
        $slugs = collect($props['pengumuman']['data'])->pluck('slug')->all();

        $this->assertContains('tayang-a', $slugs);
        $this->assertNotContains('basi', $slugs);
        $this->assertNotContains('draft-x', $slugs);
    }

    public function test_detail_tayang_dengan_meta_unik_dan_isi_bersih(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $pengumuman = Pengumuman::create([
            'desa_id' => $desa->id, 'judul' => 'Jadwal Posyandu',
            'slug' => 'jadwal-posyandu', 'isi' => '<p>Jadwal</p><script>alert(1)</script>',
            'status' => Pengumuman::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $this->get('/pengumuman/jadwal-posyandu')
            ->assertOk()
            ->assertInertia(function ($page) use ($pengumuman) {
                $page->component('pengumuman/detail');
                $page->where('pengumuman.judul', 'Jadwal Posyandu');
                $page->where('meta.title', "Jadwal Posyandu ({$pengumuman->desa->name})");
                $page->has('meta.description');
            });

        $props = $this->get('/pengumuman/jadwal-posyandu')->inertiaProps();

        $this->assertStringNotContainsString('<script', (string) $props['pengumuman']['isi']);
        $this->assertStringContainsString('Jadwal', (string) $props['pengumuman']['isi']);
    }

    public function test_detail_draft_dan_kedaluarsa_404(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        Pengumuman::create([
            'desa_id' => $desa->id, 'judul' => 'Draft', 'slug' => 'draft-x',
            'isi' => '<p>Draft</p>', 'status' => Pengumuman::STATUS_DRAFT,
        ]);
        $this->makePublished($desa, 'basi', 'Basi', now()->subDay()->toDateTimeString());

        $this->get('/pengumuman/draft-x')->assertNotFound();
        $this->get('/pengumuman/basi')->assertNotFound();
        $this->get('/pengumuman')->assertOk();
    }

    public function test_pengumuman_desa_lain_tidak_bocor(): void
    {
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();

        $this->makePublished($desaB, 'rahasia-b', 'Rahasia B');

        $props = $this->get('http://kepandean.test/pengumuman')->inertiaProps();
        $slugs = collect($props['pengumuman']['data'])->pluck('slug')->all();

        $this->assertNotContains('rahasia-b', $slugs);
        $this->get('http://kepandean.test/pengumuman/rahasia-b')->assertNotFound();
    }

    public function test_sitemap_memuat_yang_tayang_saja(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $this->makePublished($desa, 'tayang', 'Tayang');
        $this->makePublished($desa, 'basi', 'Basi', now()->subDay()->toDateTimeString());
        Pengumuman::create([
            'desa_id' => $desa->id, 'judul' => 'Draft', 'slug' => 'draft-x',
            'isi' => '<p>Draft</p>', 'status' => Pengumuman::STATUS_DRAFT,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $body = $response->getContent();

        $this->assertStringContainsString('/pengumuman/tayang', (string) $body);
        $this->assertStringNotContainsString('draft-x', (string) $body);
        $this->assertStringNotContainsString('/pengumuman/basi', (string) $body);
    }

    public function test_editor_bisa_simpan_draft_tapi_publish_ditolak(): void
    {
        $editor = $this->makeUser('editor', 'kepandean');

        $this->actingAs($editor, 'web');

        Livewire::test(CreatePengumuman::class)
            ->fillForm([
                'judul' => 'Draft Editor',
                'slug' => 'draft-editor',
                'isi' => '<p>Draft</p>',
                'status' => Pengumuman::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(1, Pengumuman::forDesa($editor->desa)->where('slug', 'draft-editor')->count());

        Livewire::test(CreatePengumuman::class)
            ->fillForm([
                'judul' => 'Coba Publish',
                'slug' => 'coba-publish',
                'isi' => '<p>Isi</p>',
                'status' => Pengumuman::STATUS_PUBLISHED,
            ])
            ->call('create')
            ->assertForbidden();

        $this->assertSame(0, Pengumuman::forDesa($editor->desa)->where('slug', 'coba-publish')->count());
    }

    public function test_admin_bisa_publish_dan_editor_tidak_bisa_hapus(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');
        $editor = $this->makeUser('editor', 'kepandean');

        $this->actingAs($admin, 'web');

        Livewire::test(CreatePengumuman::class)
            ->fillForm([
                'judul' => 'Rilis Admin',
                'slug' => 'rilis-admin',
                'isi' => '<p>Rilis</p><script>alert(1)</script>',
                'status' => Pengumuman::STATUS_PUBLISHED,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $pengumuman = Pengumuman::forDesa($admin->desa)->where('slug', 'rilis-admin')->firstOrFail();

        $this->assertTrue($pengumuman->isPublished());
        $this->assertStringNotContainsString('<script', (string) $pengumuman->isi);

        $this->assertTrue(Gate::forUser($editor)->denies('delete', $pengumuman));
        $this->assertTrue(Gate::forUser($editor)->denies('deleteAny', Pengumuman::class));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $pengumuman));
    }

    public function test_admin_bisa_buka_halaman_pengumumans(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');

        $this->actingAs($admin, 'web')->get('/admin/pengumumans')->assertOk();
        $this->actingAs($admin, 'web')->get('/admin/pengumumans/create')->assertOk();
    }
}
