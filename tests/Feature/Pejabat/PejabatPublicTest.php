<?php

namespace Tests\Feature\Pejabat;

use App\Models\Desa;
use App\Models\Pejabat;
use Database\Seeders\DesaSeeder;
use Database\Seeders\PejabatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PejabatPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    public function test_struktur_page_renders_grouped_with_unique_meta(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Pejabat::create(['desa_id' => $desa->id,
            'nama' => 'Wastedjo',
            'jabatan' => 'Kepala Desa',
            'kelompok' => Pejabat::KELOMPOK_PIMPINAN,
            'urutan' => 1,
        ]);

        $this->get('/profil/struktur-organisasi')
            ->assertOk()
            ->assertInertia(function ($page) {
                $page->component('struktur');
                $page->has('groups', 3);
                $page->where('meta.title', 'Struktur Organisasi Desa Kepandean');
                $page->has('meta.description');
            });
    }

    public function test_legacy_struktur_url_redirects_to_profil_canonical(): void
    {
        $this->get('/struktur-pemerintahan')->assertRedirect('/profil/struktur-organisasi');
    }

    public function test_ordering_reflects_publicly(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Pejabat::create(['desa_id' => $desa->id,
            'nama' => 'Belakangan', 'jabatan' => 'Staf', 'kelompok' => Pejabat::KELOMPOK_PERANGKAT, 'urutan' => 9,
        ]);
        Pejabat::create(['desa_id' => $desa->id,
            'nama' => 'Duluan', 'jabatan' => 'Sekretaris', 'kelompok' => Pejabat::KELOMPOK_PERANGKAT, 'urutan' => 1,
        ]);

        $props = $this->get('/profil/struktur-organisasi')->inertiaProps();
        $items = collect($props['groups'])->firstWhere('key', Pejabat::KELOMPOK_PERANGKAT)['items'];

        $this->assertSame('Duluan', $items[0]['nama']);
        $this->assertSame('Belakangan', $items[1]['nama']);
    }

    public function test_search_by_name_and_wilayah(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        Pejabat::create(['desa_id' => $desa->id,
            'nama' => 'Budi Santoso', 'jabatan' => 'Ketua RT', 'kelompok' => Pejabat::KELOMPOK_WILAYAH, 'wilayah_label' => 'RT 01 / RW 05', 'urutan' => 1,
        ]);

        $props = $this->get('/profil/struktur-organisasi')->inertiaProps();
        $items = collect($props['groups'])->firstWhere('key', Pejabat::KELOMPOK_WILAYAH)['items'];

        $this->assertCount(1, $items);
        $this->assertSame('Budi Santoso', $items[0]['nama']);
        $this->assertSame('RT 01 / RW 05', $items[0]['wilayah_label']);
    }

    public function test_other_desa_pejabat_never_leaks(): void
    {
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        Pejabat::create(['desa_id' => $desaB->id,
            'nama' => 'Rahasia B', 'jabatan' => 'Kades B', 'kelompok' => Pejabat::KELOMPOK_PIMPINAN, 'urutan' => 1,
        ]);

        $props = $this->get('http://kepandean.test/profil/struktur-organisasi')->inertiaProps();
        $names = collect($props['groups'])->flatMap(fn ($g) => $g['items'])->pluck('nama')->all();

        $this->assertNotContains('Rahasia B', $names);
    }

    public function test_pejabat_seeder_parity_five_rows_two_wilayah_idempotent(): void
    {
        $this->seed(PejabatSeeder::class);

        $desa = Desa::where('slug', 'kepandean')->firstOrFail();

        $this->assertSame(5, Pejabat::forDesa($desa)->count());
        $this->assertSame(2, Pejabat::forDesa($desa)->where('kelompok', Pejabat::KELOMPOK_WILAYAH)->count());

        $this->seed(PejabatSeeder::class);

        $this->assertSame(5, Pejabat::forDesa($desa)->count());
    }
}
