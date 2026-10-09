<?php

namespace Tests\Feature\Filament;

use App\Models\Desa;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    public function test_guest_redirected_to_login_when_accessing_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_editor_can_access_panel_but_cannot_publish(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();
        $editor = User::factory()->create([
            'desa_id' => $desa->id,
            'role' => 'editor',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($editor, 'web');
        $response = $this->get('/admin');

        $response->assertOk();

        $this->assertFalse($editor->canPublish());
    }

    public function test_admin_desa_can_access_panel_and_publish(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();
        $admin = User::factory()->create([
            'desa_id' => $desa->id,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'web');
        $response = $this->get('/admin');

        $response->assertOk();

        $this->assertTrue($admin->canPublish());
    }

    public function test_techade_can_access_panel(): void
    {
        $techade = User::factory()->create([
            'desa_id' => null,
            'role' => 'techade',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($techade, 'web');
        $response = $this->get('/admin');

        $response->assertOk();
    }

    public function test_unverified_user_cannot_access_admin_panel(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();
        $user = User::factory()->create([
            'desa_id' => $desa->id,
            'role' => 'admin_desa',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user, 'web');
        $response = $this->get('/admin');

        // Filament returns 403 for users who cannot access panel (unverified)
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }
}
