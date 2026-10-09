<?php

namespace Tests\Feature\Filament;

use App\Models\Asset;
use App\Models\Desa;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);

        Storage::fake('local');
        Storage::fake('public');

        // RefreshDatabase never commits, so `->afterCommit()` conversion jobs
        // would never dispatch. The production default stays `true`; tests run
        // the same queued job inline (QUEUE_CONNECTION=sync).
        config(['media-library.queue_conversions_after_database_commit' => false]);
    }

    public function test_oversized_upload_rejected(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();
        $admin = User::factory()->create([
            'desa_id' => $desa->id,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'web');

        $file = UploadedFile::fake()->create('large.jpg', 10240, 'image/jpeg');

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/admin/media/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_valid_image_upload_generates_conversions(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();
        $admin = User::factory()->create([
            'desa_id' => $desa->id,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'web');

        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/admin/media/upload', [
            'file' => $file,
        ]);

        $response->assertOk();

        $asset = Asset::latest()->first();
        $this->assertNotNull($asset);

        $media = $asset->getMedia('uploads')->first();
        $this->assertNotNull($media);

        $media->refresh();

        // Queued conversion job generated the WebP variants on the public/CDN disk
        $this->assertTrue($media->hasGeneratedConversion('thumb'));
        $this->assertTrue($media->hasGeneratedConversion('preview'));
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot('thumb'));
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot('preview'));

        // The original itself never lands on the public disk
        Storage::disk('public')->assertMissing($media->getPathRelativeToRoot());
        Storage::disk('local')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_svg_upload_rejected_unless_sanitized(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();
        $admin = User::factory()->create([
            'desa_id' => $desa->id,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'web');

        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';
        $file = UploadedFile::fake()->createWithContent('test.svg', $svgContent);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/admin/media/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_original_file_never_served_publicly(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();
        $admin = User::factory()->create([
            'desa_id' => $desa->id,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'web');

        $file = UploadedFile::fake()->image('original.jpg', 640, 480);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/admin/media/upload', [
            'file' => $file,
        ]);

        $response->assertOk();

        $asset = Asset::latest()->first();
        $this->assertNotNull($asset);

        $media = $asset->getMedia('uploads')->first();
        $this->assertNotNull($media);

        // The original file lives only on the private disk: there is no
        // publicly reachable URL for it (the disk's serve route requires a
        // signed URL, and the file is absent from the public disk entirely).
        Storage::disk('local')->assertExists($media->getPathRelativeToRoot());
        Storage::disk('public')->assertMissing($media->getPathRelativeToRoot());
    }

    public function test_upload_without_any_desa_is_rejected_clearly(): void
    {
        Desa::query()->delete();

        $admin = User::factory()->create([
            'desa_id' => null,
            'role' => 'admin_desa',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'web');

        $file = UploadedFile::fake()->image('nodesa.jpg', 640, 480);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/admin/media/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Upload ditolak: tidak ada desa yang terdaftar untuk akun ini.');
    }
}
