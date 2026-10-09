<?php

namespace Tests\Feature\Aduan;

use App\Models\Aduan;
use App\Models\Desa;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Issue #19 slice: form Aduan sederhana (istilah kanonis `Aduan`).
 *
 * Keputusan build (tertulis di AduanController): MVP menyimpan sederhana
 * ke tabel aduans, TANPA workflow disposisi. Foto ikut aturan media #13
 * (tipe gambar, batas 5MB). Seam: HTTP Feature only.
 */
class AduanFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
        Storage::fake('public');
    }

    public function test_empty_pesan_is_rejected(): void
    {
        $response = $this->post('/aduan', [
            'nama' => 'Warga',
            'kontak' => '0812',
            'pesan' => '',
            'lokasi' => 'RT 01',
        ]);

        $response->assertSessionHasErrors('pesan');
        $this->assertSame(0, Aduan::count());
    }

    public function test_missing_lokasi_is_rejected(): void
    {
        $response = $this->post('/aduan', ['pesan' => 'Jalan rusak.']);

        $response->assertSessionHasErrors('lokasi');
        $this->assertSame(0, Aduan::count());
    }

    public function test_valid_submit_is_stored_scoped_to_current_desa(): void
    {
        $response = $this->post('/aduan', [
            'nama' => 'Warga Uji',
            'kontak' => '0812000000',
            'pesan' => 'Lampu jalan mati di gang 2.',
            'lokasi' => 'RT 02/RW 03',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $aduan = Aduan::firstOrFail();
        $this->assertSame('Lampu jalan mati di gang 2.', $aduan->pesan);
        $this->assertSame(
            Desa::where('slug', 'kepandean')->firstOrFail()->id,
            $aduan->desa_id
        );
    }

    public function test_oversize_photo_is_rejected(): void
    {
        $big = UploadedFile::fake()->image('foto.jpg')->size(6 * 1024); // 6MB

        $response = $this->post('/aduan', [
            'pesan' => 'Selokan mampet.',
            'lokasi' => 'RT 01',
            'foto' => $big,
        ]);

        $response->assertSessionHasErrors('foto');
        $this->assertSame(0, Aduan::count());
    }

    public function test_weird_file_type_is_rejected(): void
    {
        $exe = UploadedFile::fake()->create('jahat.exe', 100, 'application/x-msdownload');

        $response = $this->post('/aduan', [
            'pesan' => 'Uji tipe.',
            'lokasi' => 'RT 01',
            'foto' => $exe,
        ]);

        $response->assertSessionHasErrors('foto');
        $this->assertSame(0, Aduan::count());
    }
}
