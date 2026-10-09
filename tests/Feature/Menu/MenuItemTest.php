<?php

namespace Tests\Feature\Menu;

use App\Models\Desa;
use App\Models\MenuItem;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #19 checklist 3: menu dinamis. Tambah menu muncul di nav,
 * ubah urutan nav ikut berubah, anak satu tingkat jadi dropdown.
 *
 * Seam: HTTP Feature only (spec-002 Testing Decisions).
 */
class MenuItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    /** @return array<int, array<string, mixed>> */
    private function navProps(): array
    {
        $props = $this->get('/')->assertOk()->inertiaProps();

        /** @var array<int, array<string, mixed>> */
        return $props['site']['nav'];
    }

    public function test_tambah_menu_muncul_di_nav_setelah_menu_bawaan(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        MenuItem::create([
            'desa_id' => $desa->id,
            'label' => 'BUMDes',
            'url' => '/bumdes',
            'urutan' => 0,
        ]);

        $nav = $this->navProps();
        $last = $nav[array_key_last($nav)];

        $this->assertSame('BUMDes', $last['label']);
        $this->assertSame('/bumdes', $last['href']);
        $this->assertSame('Beranda', $nav[0]['label']);
    }

    public function test_ubah_urutan_nav_ikut_berubah(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $a = MenuItem::create(['desa_id' => $desa->id, 'label' => 'Menu A', 'url' => '/a', 'urutan' => 0]);
        $b = MenuItem::create(['desa_id' => $desa->id, 'label' => 'Menu B', 'url' => '/b', 'urutan' => 1]);

        $labels = fn (): array => array_column(array_filter(
            $this->navProps(),
            fn (array $item): bool => in_array($item['label'], ['Menu A', 'Menu B'], true),
        ), 'label');

        $this->assertSame(['Menu A', 'Menu B'], array_values($labels()));

        $a->update(['urutan' => 5]);
        $b->update(['urutan' => 1]);

        $this->assertSame(['Menu B', 'Menu A'], array_values($labels()));
    }

    public function test_anak_satu_tingkat_jadi_dropdown(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $induk = MenuItem::create(['desa_id' => $desa->id, 'label' => 'Induk', 'url' => '/induk', 'urutan' => 0]);
        MenuItem::create([
            'desa_id' => $desa->id,
            'label' => 'Anak',
            'url' => 'anak',
            'urutan' => 0,
            'parent_id' => $induk->id,
        ]);

        $nav = $this->navProps();
        $entry = $nav[array_key_last($nav)];

        $this->assertSame('Induk', $entry['label']);
        $this->assertSame([['label' => 'Anak', 'href' => '/anak']], $entry['children']);
    }
}
