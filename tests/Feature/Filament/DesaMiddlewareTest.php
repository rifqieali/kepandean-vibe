<?php

namespace Tests\Feature\Filament;

use App\Models\Desa;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesaMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    public function test_unknown_domain_resolves_default_desa_on_public_routes(): void
    {
        $this->get('http://unknown-domain.test/')->assertOk();

        $desa = app('current_desa');

        $this->assertInstanceOf(Desa::class, $desa);
        $this->assertSame('kepandean', $desa->slug);
    }

    public function test_known_domain_resolves_matching_desa(): void
    {
        $this->get('http://desa-b.test/')->assertOk();

        $desa = app('current_desa');

        $this->assertInstanceOf(Desa::class, $desa);
        $this->assertSame('desa-b', $desa->slug);
    }
}
