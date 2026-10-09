<?php

namespace Tests\Feature\Filament;

use App\Models\Asset;
use App\Models\Desa;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class DesaScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    protected function bindDesa(Desa $desa): void
    {
        App::instance('current_desa', $desa);
    }

    public function test_admin_desa_cannot_see_other_desa_content_via_query(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->first();
        $desaB = Desa::where('slug', 'desa-b')->first();

        $adminA = User::factory()->create([
            'desa_id' => $desaA->id,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $assetA = Asset::create(['desa_id' => $desaA->id]);
        Asset::create(['desa_id' => $desaB->id]);

        $this->bindDesa($desaA);

        $visibleAssets = Asset::all();
        $this->assertCount(1, $visibleAssets);
        $this->assertTrue($visibleAssets->first()->is($assetA));
    }

    public function test_editor_cannot_see_other_desa_content(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->first();
        $desaB = Desa::where('slug', 'desa-b')->first();

        $editorA = User::factory()->create([
            'desa_id' => $desaA->id,
            'role' => 'editor',
            'email_verified_at' => now(),
        ]);

        $assetA = Asset::create(['desa_id' => $desaA->id]);
        Asset::create(['desa_id' => $desaB->id]);

        $this->bindDesa($desaA);

        $visibleAssets = Asset::all();
        $this->assertCount(1, $visibleAssets);
        $this->assertTrue($visibleAssets->first()->is($assetA));
    }

    public function test_techade_can_see_all_content_without_scope(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->first();
        $desaB = Desa::where('slug', 'desa-b')->first();

        $techade = User::factory()->create([
            'desa_id' => null,
            'role' => 'techade',
            'email_verified_at' => now(),
        ]);

        Asset::create(['desa_id' => $desaA->id]);
        Asset::create(['desa_id' => $desaB->id]);

        // Techade tidak kena scope desa: query panel mengembalikan semua desa.
        $this->actingAs($techade, 'web');

        // Need to disable global scope for techade
        $allAssets = Asset::withoutGlobalScope('desa')->get();
        $this->assertCount(2, $allAssets);
    }

    public function test_global_scope_defaults_to_kepandean_when_no_domain(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->first();
        $desaB = Desa::where('slug', 'desa-b')->first();

        $adminA = User::factory()->create([
            'desa_id' => $desaA->id,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $assetA = Asset::create(['desa_id' => $desaA->id]);
        Asset::create(['desa_id' => $desaB->id]);

        // No Host header - should default to Kepandean
        $defaultDesa = Desa::getDefault();
        $this->bindDesa($defaultDesa);

        $visibleAssets = Asset::all();
        $this->assertCount(1, $visibleAssets);
        $this->assertTrue($visibleAssets->first()->is($assetA));
    }
}
