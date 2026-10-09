<?php

namespace Tests\Feature\Filament;

use App\Models\Desa;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    private function makeUser(string $role, string $desaSlug, bool $verified = true): User
    {
        $desa = Desa::where('slug', $desaSlug)->firstOrFail();

        return User::factory()->create([
            'desa_id' => $desa->id,
            'role' => $role,
            'email_verified_at' => $verified ? now() : null,
        ]);
    }

    public function test_admin_desa_sees_only_own_desa_users(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');
        $teammate = $this->makeUser('editor', 'kepandean', true);
        $teammate->update(['name' => 'Teammate Kepandean']);
        $outsider = $this->makeUser('editor', 'desa-b', true);
        $outsider->update(['name' => 'Outsider Desa B']);

        $response = $this->actingAs($admin, 'web')->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Teammate Kepandean');
        $response->assertDontSee('Outsider Desa B');
    }

    public function test_editor_is_forbidden_from_user_pages(): void
    {
        $editor = $this->makeUser('editor', 'kepandean');

        $this->actingAs($editor, 'web')->get('/admin/users')->assertForbidden();
        $this->actingAs($editor, 'web')->get('/admin/users/create')->assertForbidden();
    }

    public function test_admin_cannot_edit_user_from_other_desa(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');
        $outsider = $this->makeUser('editor', 'desa-b', true);

        // Cross-desa records are invisible in the panel: the scoped query
        // resolves nothing, so the page is a 404, never a leak.
        $this->actingAs($admin, 'web')
            ->get("/admin/users/{$outsider->id}/edit")
            ->assertNotFound();
    }

    public function test_admin_can_open_edit_page_for_own_desa_user(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');
        $teammate = $this->makeUser('editor', 'kepandean', true);

        $this->actingAs($admin, 'web')
            ->get("/admin/users/{$teammate->id}/edit")
            ->assertOk();
    }

    public function test_publish_and_manage_users_gates(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');
        $editor = $this->makeUser('editor', 'kepandean');
        $developer = $this->makeUser('developer', 'kepandean');

        $this->assertTrue(Gate::forUser($admin)->allows('publish-content'));
        $this->assertTrue(Gate::forUser($admin)->allows('manage-users'));

        $this->assertTrue(Gate::forUser($editor)->denies('publish-content'));
        $this->assertTrue(Gate::forUser($editor)->denies('manage-users'));

        // Developer mirip admin_desa tapi lintas-desa.
        $this->assertTrue(Gate::forUser($developer)->allows('publish-content'));
        $this->assertTrue(Gate::forUser($developer)->allows('manage-users'));
    }

    public function test_developer_sees_users_from_all_desa(): void
    {
        $developer = $this->makeUser('developer', 'kepandean');
        $teammate = $this->makeUser('editor', 'kepandean', true);
        $teammate->update(['name' => 'Teammate Kepandean']);
        $outsider = $this->makeUser('editor', 'desa-b', true);
        $outsider->update(['name' => 'Outsider Desa B']);

        $response = $this->actingAs($developer, 'web')->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Teammate Kepandean');
        $response->assertSee('Outsider Desa B');
    }

    public function test_developer_can_edit_user_from_other_desa(): void
    {
        $developer = $this->makeUser('developer', 'kepandean');
        $outsider = $this->makeUser('editor', 'desa-b', true);

        $this->actingAs($developer, 'web')
            ->get("/admin/users/{$outsider->id}/edit")
            ->assertOk();
    }
}
