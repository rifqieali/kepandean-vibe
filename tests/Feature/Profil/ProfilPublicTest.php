<?php

namespace Tests\Feature\Profil;

use App\Models\Desa;
use App\Models\Profil;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    public function test_sejarah_visi_misi_page_renders_combined_with_unique_meta(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Profil::forDesa($desa)->update([
            'sejarah' => '<p>Sejarah Kepandean</p>',
            'visi' => '<p>Visi</p>',
            'misi' => '<p>Misi</p>',
        ]);

        $this->get('/profil/sejarah-visi-misi')
            ->assertOk()
            ->assertInertia(function ($page) {
                $page->component('profil/sejarah-visi-misi');
                $page->has('profil');
                $page->where('profil.visi', '<p>Visi</p>');
                $page->where('profil.misi', '<p>Misi</p>');
                $page->where('meta.title', 'Sejarah & Visi Misi Desa Kepandean');
                $page->has('meta.description');
            });
    }

    public function test_legacy_profil_urls_redirect_to_combined_page(): void
    {
        $this->get('/profil/sejarah')->assertRedirect('/profil/sejarah-visi-misi');
        $this->get('/profil/visi-misi')->assertRedirect('/profil/sejarah-visi-misi');
    }

    public function test_sejarah_foto_from_filament_reaches_public_page(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Profil::forDesa($desa)->update(['foto_path' => 'profil/sejarah.jpg']);

        $props = $this->get('/profil/sejarah-visi-misi')->inertiaProps();

        $this->assertStringContainsString('storage/profil/sejarah.jpg', (string) ($props['profil']['foto_url'] ?? ''));
    }

    public function test_sejarah_foto_empty_without_upload(): void
    {
        $props = $this->get('/profil/sejarah-visi-misi')->inertiaProps();

        $this->assertNull($props['profil']['foto_url'] ?? null);
    }

    public function test_malicious_html_never_reaches_public_props(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Profil::forDesa($desa)->update([
            'sejarah' => '<p>Ok</p><script>alert(1)</script>',
        ]);

        $response = $this->get('/profil/sejarah-visi-misi');

        $response->assertOk();

        $props = $response->inertiaProps();
        $sejarah = $props['profil']['sejarah'] ?? '';

        $this->assertStringNotContainsString('<script', (string) $sejarah);
        $this->assertStringContainsString('Ok', (string) $sejarah);
    }

    public function test_other_desa_profil_never_shows_on_this_domain(): void
    {
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        Profil::forDesa($desaB)->update([
            'sejarah' => '<p>Rahasia Desa B</p>',
        ]);

        $response = $this->get('http://kepandean.test/profil/sejarah-visi-misi');

        $response->assertOk();

        $props = $response->inertiaProps();
        $sejarah = $props['profil']['sejarah'] ?? '';

        $this->assertStringNotContainsString('Rahasia Desa B', (string) $sejarah);
    }

    public function test_empty_profil_renders_honest_empty_state(): void
    {
        $this->get('/profil/sejarah-visi-misi')
            ->assertOk()
            ->assertInertia(function ($page) {
                $page->component('profil/sejarah-visi-misi');
                $page->where('profil.sejarah', '');
                $page->where('profil.isEmpty', true);
            });
    }

    public function test_unknown_domain_serves_default_desa_profil(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Profil::forDesa($desa)->update([
            'sejarah' => '<p>Sejarah Default</p>',
        ]);

        // Unknown domains fall back to the default desa (issue 13 rule),
        // so the public page shows the default desa profil, never a leak.
        $this->get('http://unknown-domain.test/profil/sejarah-visi-misi')
            ->assertOk()
            ->assertInertia(function ($page) {
                $page->component('profil/sejarah-visi-misi');
                $page->where('profil.sejarah', '<p>Sejarah Default</p>');
                $page->where('meta.title', 'Sejarah & Visi Misi Desa Kepandean');
            });
    }

    public function test_home_shares_profil_excerpt_for_beranda(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Profil::forDesa($desa)->update([
            'sejarah' => '<p>Cuplikan sejarah desa untuk beranda.</p>',
        ]);

        $canonical = route('profil.sejarah-visi-misi', [], false);

        $this->get('/')
            ->assertOk()
            ->assertInertia(function ($page) use ($canonical) {
                $page->component('welcome');
                $page->where('profilExcerpt.sejarah', 'Cuplikan sejarah desa untuk beranda.');
                $page->where('profilExcerpt.urls.sejarah', $canonical.'#sejarah');
                $page->where('profilExcerpt.urls.visiMisi', $canonical.'#visi-misi');
            });
    }

    public function test_home_excerpt_never_leaks_script_text(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Profil::forDesa($desa)->update([
            'sejarah' => '<p>Cuplikan</p><script>alert(1)</script>',
        ]);

        $response = $this->get('/');

        $response->assertOk();

        $props = $response->inertiaProps();
        $excerpt = (string) ($props['profilExcerpt']['sejarah'] ?? '');

        $this->assertStringContainsString('Cuplikan', $excerpt);
        $this->assertStringNotContainsString('alert', $excerpt);
        $this->assertStringNotContainsString('<script', $excerpt);
    }
}
