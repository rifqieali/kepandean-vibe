<?php

namespace Tests\Unit;

use App\Models\Desa;
use App\Models\Kategori;
use App\Models\Statistik;
use App\Models\User;
use App\Support\Filament\DesaScoping;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Unique;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Seam test for the deepened desa-scoping module (candidate 1).
 * One interface, tested directly — no HTTP needed.
 */
class DesaScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    private function user(string $role, ?Desa $desa): User
    {
        return User::factory()->create([
            'desa_id' => $desa?->id,
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    public function test_scope_for_admin_returns_empty_for_guest(): void
    {
        $sql = DesaScoping::scopeForAdmin(Statistik::query(), null)->toSql();

        $this->assertStringContainsString('1 = 0', $sql);
    }

    public function test_scope_for_admin_returns_all_for_developer(): void
    {
        $developer = $this->user('developer', null);

        $sql = DesaScoping::scopeForAdmin(Statistik::query(), $developer)->toSql();

        $this->assertStringNotContainsString('1 = 0', $sql);
        $this->assertStringNotContainsString('desa_id', $sql);
    }

    public function test_scope_for_admin_locks_to_own_desa(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $other = Desa::where('slug', 'desa-b')->firstOrFail();
        Statistik::seedDefaults($desa);
        Statistik::seedDefaults($other);
        $admin = $this->user('admin_desa', $desa);

        $ids = DesaScoping::scopeForAdmin(Statistik::query(), $admin)->pluck('desa_id')->all();

        // Query itself carries the lock; only own desa rows match.
        $this->assertNotEmpty($ids);
        $this->assertTrue(collect($ids)->every(fn ($id) => (int) $id === $desa->id));
    }

    public function test_scope_for_admin_returns_empty_when_admin_has_no_desa(): void
    {
        $admin = $this->user('admin_desa', null);

        $sql = DesaScoping::scopeForAdmin(Statistik::query(), $admin)->toSql();

        $this->assertStringContainsString('1 = 0', $sql);
    }

    public function test_resolve_desa_id_for_create_locks_non_developer(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $admin = $this->user('admin_desa', $desa);

        $data = DesaScoping::resolveDesaIdForCreate(['judul' => 'x', 'desa_id' => 9999], $admin);

        $this->assertSame($desa->id, $data['desa_id']);
    }

    public function test_resolve_desa_id_for_create_rejects_guest_and_empty_developer_pick(): void
    {
        try {
            DesaScoping::resolveDesaIdForCreate(['judul' => 'x'], null);
            $this->fail('Guest harus ditolak (403).');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_resolve_desa_id_for_create_requires_developer_pick(): void
    {
        $developer = $this->user('developer', null);

        try {
            DesaScoping::resolveDesaIdForCreate(['judul' => 'x'], $developer);
            $this->fail('Developer tanpa desa_id harus ditolak (422).');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }

    public function test_lock_desa_id_for_save_keeps_developer_choice(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->firstOrFail();
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        $developer = $this->user('developer', null);

        $data = DesaScoping::lockDesaIdForSave(['desa_id' => $desaB->id], $developer);

        $this->assertSame($desaB->id, $data['desa_id']);
        $this->assertSame($desaA->id, Desa::where('slug', 'kepandean')->firstOrFail()->id);
    }

    public function test_unique_in_desa_scopes_rule_to_resolved_desa(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $admin = $this->user('admin_desa', $desa);

        $rule = DesaScoping::uniqueInDesa(new Unique('statistiks', 'kunci'), DesaScoping::desaIdForUnique(null, $admin));

        // Rule string must carry the desa constraint (proves scoping without HTTP).
        $this->assertStringContainsString((string) $desa->id, (string) $rule);
    }

    public function test_kategori_options_scope_to_selected_desa_for_developer(): void
    {
        $desaA = Desa::where('slug', 'kepandean')->firstOrFail();
        $desaB = Desa::where('slug', 'desa-b')->firstOrFail();
        $developer = $this->user('developer', null);

        Kategori::create(['desa_id' => $desaA->id, 'nama' => 'A1', 'slug' => 'a1']);
        Kategori::create(['desa_id' => $desaB->id, 'nama' => 'B1', 'slug' => 'b1']);

        $scoped = DesaScoping::kategoriOptions($developer, $desaA->id);

        $this->assertSame(['A1'], array_values($scoped));
    }

    public function test_same_desa_guards_policy_tail(): void
    {
        $desa = Desa::where('slug', 'kepandean')->firstOrFail();
        $other = Desa::where('slug', 'desa-b')->firstOrFail();
        $admin = $this->user('admin_desa', $desa);

        $this->assertTrue(DesaScoping::sameDesa($admin, $desa->id));
        $this->assertFalse(DesaScoping::sameDesa($admin, $other->id));
        $this->assertFalse(DesaScoping::sameDesa(null, $desa->id));
    }
}
