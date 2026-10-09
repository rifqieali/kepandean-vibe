<?php

namespace Tests\Feature\Seo;

use App\Support\Favicon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Varian favicon harus persegi dengan logo utuh di tengah (contain),
 * bukan di-stretch mengisi kanvas.
 *
 * Decode di test ini independen (hanya mengerti filter-0 yang selalu
 * dipakai encoder Favicon), sehingga bukan verifikasi melingkar.
 */
class FaviconTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<list<array{0: int, 1: int, 2: int, 3: int}>> */
    private function decode(string $png): array
    {
        $info = getimagesizefromstring($png);
        $this->assertSame('image/png', $info['mime'] ?? null);

        $pos = 8;
        $idat = '';

        while ($pos + 8 <= strlen($png)) {
            $size = unpack('N', substr($png, $pos, 4))[1];
            $type = substr($png, $pos + 4, 4);

            if ($type === 'IDAT') {
                $idat .= substr($png, $pos + 8, $size);
            } elseif ($type === 'IEND') {
                break;
            }

            $pos += 12 + $size;
        }

        $raw = gzuncompress($idat);
        $w = $info[0];
        $h = $info[1];
        $rows = [];

        for ($y = 0; $y < $h; $y++) {
            $row = substr($raw, $y * (1 + 4 * $w), 1 + 4 * $w);
            $this->assertSame(0, ord($row[0]), 'Encoder favicon harus selalu filter-0.');
            $pixels = [];

            for ($x = 0; $x < $w; $x++) {
                $pixels[] = array_values(unpack('C4', substr($row, 1 + 4 * $x, 4)));
            }

            $rows[] = $pixels;
        }

        return $rows;
    }

    private function png(int $w, int $h, array $rgba): string
    {
        $raw = '';

        for ($y = 0; $y < $h; $y++) {
            $raw .= "\x00".str_repeat(pack('C4', ...$rgba), $w);
        }

        $chunk = fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

        return "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $w, $h, 8, 6, 0, 0, 0))
            .$chunk('IDAT', (string) gzcompress($raw))
            .$chunk('IEND', '');
    }

    public function test_logo_potrait_tidak_melar_vertikal(): void
    {
        $variant = Favicon::build($this->png(4, 8, [200, 30, 30, 255]));

        $this->assertNotNull($variant);

        $rows = $this->decode($variant);

        $this->assertCount(8, $rows);
        $this->assertCount(8, $rows[0]);

        // Kolom padding kiri-kanan harus transparan; bila di-stretch,
        // seluruh piksel akan merah.
        foreach ($rows as $y => $pixels) {
            foreach ([0, 1, 6, 7] as $x) {
                $this->assertSame(0, $pixels[$x][3], "Kolom {$x} baris {$y} harus padding transparan.");
            }

            foreach ([2, 3, 4, 5] as $x) {
                $this->assertSame([200, 30, 30, 255], $pixels[$x]);
            }
        }
    }

    public function test_logo_landskap_tidak_melar_horizontal(): void
    {
        $variant = Favicon::build($this->png(8, 4, [30, 120, 30, 255]));

        $this->assertNotNull($variant);

        $rows = $this->decode($variant);

        $this->assertCount(8, $rows);

        // Baris padding atas-bawah harus transparan; bila di-stretch,
        // seluruh piksel akan hijau.
        foreach ([0, 1, 6, 7] as $y) {
            foreach ($rows[$y] as $px) {
                $this->assertSame(0, $px[3], "Baris {$y} harus padding transparan.");
            }
        }

        foreach ([2, 3, 4, 5] as $y) {
            foreach ($rows[$y] as $px) {
                $this->assertSame([30, 120, 30, 255], $px);
            }
        }
    }

    public function test_logo_asli_desa_jadi_persegi_256px(): void
    {
        // Logo.png asli 1200x1489 (potrait): regresi bug stretch favicon.
        $bytes = (string) file_get_contents(database_path('data/images/Logo.png'));
        $variant = Favicon::build($bytes);

        $this->assertNotNull($variant);

        $info = getimagesizefromstring($variant);

        $this->assertSame(256, $info[0]);
        $this->assertSame(256, $info[1]);
    }

    public function test_bukan_gambar_menghasilkan_null(): void
    {
        $this->assertNull(Favicon::build('bukan-gambar'));

        Storage::fake('public');

        $this->assertNull(Favicon::url(null));
        $this->assertNull(Favicon::url('profil/tidak-ada.png'));

        Storage::disk('public')->put('profil/rusak.png', 'bukan-gambar');

        $this->assertNull(Favicon::url('profil/rusak.png'));
    }
}
