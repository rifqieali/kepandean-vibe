<?php

namespace Tests\Feature\Pengumuman;

use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #17 checklist 2: semua kartu Layanan Publik + nav + top-bar
 * WAJIB mendarat di tempat nyata, tidak ada jalan buntu.
 *
 * Aturan: href internal → GET dan harus < 400 (mengikuti redirect);
 * href outbound → host-nya harus terdaftar di allowlist (tidak ada
 * request keluar saat test). Tidak ada href="#", IP intranet, placeholder.
 */
class LayananLinksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    /** @return array<int, string> */
    private function allowlistedHosts(): array
    {
        // wa.me: CTA "Hubungi via WhatsApp" pada banner Hotline Aduan
        // Beranda (outbound resmi terdaftar, DESIGN.md §2).
        return ['www.facebook.com', 'maps.app.goo.gl', 'wa.me'];
    }

    public function test_semua_tautan_halaman_utama_tidak_buntu(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertIsString($html);
        $this->assertStringNotContainsString('href="#"', $html);
        $this->assertStringNotContainsString('192.168.', $html);
        $this->assertStringNotContainsString('11111', $html);

        preg_match_all('/<a[^>]+href="([^"]+)"/', $html, $matches);

        $hrefs = array_unique($matches[1]);
        $this->assertNotEmpty($hrefs);

        foreach ($hrefs as $href) {
            // Jangkar sehalaman (kartu Aduan → #aduan): target id-nya harus ada.
            if (str_starts_with($href, '#')) {
                $this->assertStringContainsString(
                    'id="'.substr($href, 1).'"',
                    $html,
                    "Jangkar {$href} tidak punya target di halaman."
                );

                continue;
            }

            if (str_starts_with($href, 'http')) {
                $host = (string) parse_url($href, PHP_URL_HOST);
                $appHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

                // URL absolut satu aplikasi (route() tanpa false) → uji sebagai internal.
                if (in_array($host, ['localhost', '127.0.0.1', $appHost], true)) {
                    $href = (string) parse_url($href, PHP_URL_PATH);
                } else {
                    $this->assertContains(
                        $host,
                        $this->allowlistedHosts(),
                        "Outbound {$href} belum terdaftar di allowlist."
                    );

                    continue;
                }
            }

            $path = strtok($href, '#?') ?: '/';

            $status = $this->get($path)->getStatusCode();

            $this->assertContains(
                $status,
                [200, 301, 302],
                "Tautan buntu: {$href} (status {$status})."
            );
        }
    }

    public function test_tautan_topbar_hidup(): void
    {
        $this->get('/layanan-warga')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_alias_lama_pengumuman_diteruskan_ke_rute_native(): void
    {
        $this->get('/segera-hadir/pengumuman')->assertRedirect('/pengumuman');
        $this->get('/pengumuman')->assertOk();
    }

    public function test_halaman_pengumuman_tanpa_sidebar_default(): void
    {
        foreach (['/pengumuman', '/berita', '/informasi'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertIsString($html);
            $this->assertStringNotContainsString('sidebar-wrapper', $html, "{$url} bocor ke layout sidebar.");
            $this->assertStringContainsString('Navigasi utama', $html);
        }
    }
}
