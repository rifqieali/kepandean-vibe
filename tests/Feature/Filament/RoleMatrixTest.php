<?php

namespace Tests\Feature\Filament;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kategori;
use App\Models\Pejabat;
use App\Models\Statistik;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Matriks peran panel (revisi #16 + peran developer):
 * - editor: hanya konten (Berita/Kategori), tanpa hapus, tanpa Pejabat/Statistik/Users.
 * - admin_desa: semua dalam desanya + publish + kelola user.
 * - developer: semua lintas desa.
 */
class RoleMatrixTest extends TestCase
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

    public function test_editor_sees_only_content_resources(): void
    {
        $editor = $this->makeUser('editor', 'kepandean');

        $this->actingAs($editor, 'web')->get('/admin/beritas')->assertOk();
        $this->actingAs($editor, 'web')->get('/admin/kategoris')->assertOk();
        $this->actingAs($editor, 'web')->get('/admin/pengumumans')->assertOk();

        $this->actingAs($editor, 'web')->get('/admin/pejabats')->assertForbidden();
        $this->actingAs($editor, 'web')->get('/admin/statistiks')->assertForbidden();
        $this->actingAs($editor, 'web')->get('/admin/users')->assertForbidden();
    }

    public function test_editor_cannot_delete_anything(): void
    {
        $editor = $this->makeUser('editor', 'kepandean');
        $desa = $editor->desa;

        $berita = Berita::create([
            'desa_id' => $desa->id,
            'kategori_id' => Kategori::create(['desa_id' => $desa->id, 'nama' => 'Kegiatan', 'slug' => 'kegiatan'])->id,
            'judul' => 'Draft',
            'slug' => 'draft',
            'isi' => '<p>isi</p>',
            'status' => Berita::STATUS_DRAFT,
        ]);
        $pejabat = Pejabat::create([
            'desa_id' => $desa->id, 'nama' => 'A', 'jabatan' => 'B',
            'kelompok' => Pejabat::KELOMPOK_PERANGKAT, 'urutan' => 0,
        ]);
        // NB: kunci default (total_jiwa, laki_laki, ...) sudah di-seed; pakai kunci bebas.
        $statistik = Statistik::create(['desa_id' => $desa->id, 'kunci' => 'uji_matrix', 'nilai' => '1']);

        $this->assertTrue(Gate::forUser($editor)->denies('delete', $berita));
        $this->assertTrue(Gate::forUser($editor)->denies('delete', $pejabat));
        $this->assertTrue(Gate::forUser($editor)->denies('delete', $statistik));
        $this->assertTrue(Gate::forUser($editor)->denies('deleteAny', Berita::class));

        // Editor tetap boleh tulis draft di desanya.
        $this->assertTrue(Gate::forUser($editor)->allows('create', Berita::class));
        $this->assertTrue(Gate::forUser($editor)->allows('update', $berita));
    }

    public function test_admin_desa_full_in_own_desa_only(): void
    {
        $admin = $this->makeUser('admin_desa', 'kepandean');
        $otherDesa = Desa::where('slug', 'desa-b')->firstOrFail();

        $this->actingAs($admin, 'web')->get('/admin/pejabats')->assertOk();
        $this->actingAs($admin, 'web')->get('/admin/statistiks')->assertOk();

        $outsider = Pejabat::create([
            'desa_id' => $otherDesa->id, 'nama' => 'X', 'jabatan' => 'Y',
            'kelompok' => Pejabat::KELOMPOK_PERANGKAT, 'urutan' => 0,
        ]);

        $this->assertTrue(Gate::forUser($admin)->denies('view', $outsider));
        $this->assertTrue(Gate::forUser($admin)->denies('delete', $outsider));
    }

    public function test_developer_cross_desa_everywhere(): void
    {
        $developer = $this->makeUser('developer', 'kepandean');
        $otherDesa = Desa::where('slug', 'desa-b')->firstOrFail();

        $this->actingAs($developer, 'web')->get('/admin/pejabats')->assertOk();
        $this->actingAs($developer, 'web')->get('/admin/statistiks')->assertOk();

        $outsider = Pejabat::create([
            'desa_id' => $otherDesa->id, 'nama' => 'X', 'jabatan' => 'Y',
            'kelompok' => Pejabat::KELOMPOK_PERANGKAT, 'urutan' => 0,
        ]);

        $this->assertTrue(Gate::forUser($developer)->allows('view', $outsider));
        $this->assertTrue(Gate::forUser($developer)->allows('delete', $outsider));
        $this->assertTrue(Gate::forUser($developer)->allows('deleteAny', Berita::class));
    }
}
