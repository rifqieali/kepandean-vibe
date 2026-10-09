<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Satu-satunya seam favicon publik.
 *
 * Ikon tab browser selalu dirender persegi oleh browser: berkas logo
 * non-persegi (misal Logo Desa yang potrait) akan di-stretch bila
 * langsung dipakai sebagai favicon. Kelas ini menurunkan varian
 * persegi (`profil/favicon.png`): logo ditempel utuh di tengah kanvas
 * transparan (contain, bukan cover), sehingga tidak pernah stretch,
 * lalu dikecilkan sampai sisi terpanjang 256px agar ringan diunduh.
 *
 * Murni PHP tanpa ekstensi GD: PNG 8-bit (gray/RGB/gray-alpha/RGBA,
 * non-interlaced) di-decode langsung; format lain dicoba lewat GD bila
 * tersedia, selain itu varian tidak dibuat (fallback favicon statis).
 * Regenerasi malas: varian dibuat ulang hanya bila belum ada atau lebih
 * tua dari logo, jadi request harian hanya dua stat berkas.
 */
final class Favicon
{
    public const VARIANT = 'profil/favicon.png';

    public const MAX_SIDE = 256;

    /**
     * URL varian favicon untuk satu path logo, atau null bila tidak ada
     * logo yang bisa dijadikan favicon (favicon statis yang dipakai).
     */
    public static function url(?string $logoPath): ?string
    {
        $disk = Storage::disk(Media::DISK);

        if (! is_string($logoPath) || $logoPath === '' || ! $disk->exists($logoPath)) {
            self::forget();

            return null;
        }

        try {
            $logoTime = $disk->lastModified($logoPath);
            $fresh = $disk->exists(self::VARIANT) && $disk->lastModified(self::VARIANT) >= $logoTime;
        } catch (\Throwable) {
            $fresh = false;
        }

        if (! $fresh) {
            $variant = self::build((string) $disk->get($logoPath));

            if ($variant === null) {
                return null;
            }

            $disk->put(self::VARIANT, $variant);
        }

        return Media::url(self::VARIANT);
    }

    public static function forget(): void
    {
        $disk = Storage::disk(Media::DISK);

        if ($disk->exists(self::VARIANT)) {
            $disk->delete(self::VARIANT);
        }
    }

    /**
     * Bangun biner PNG persegi dari biner logo, atau null bila format
     * tidak didukung di environment ini.
     */
    public static function build(string $bytes): ?string
    {
        $image = self::decodePng($bytes) ?? self::decodeGd($bytes);

        if ($image === null) {
            return null;
        }

        [$srcW, $srcH, $pixels] = $image;
        $scale = min(1.0, self::MAX_SIDE / max($srcW, $srcH));
        $dstW = max(1, (int) round($srcW * $scale));
        $dstH = max(1, (int) round($srcH * $scale));
        $side = max($dstW, $dstH);
        $offX = (int) floor(($side - $dstW) / 2);
        $offY = (int) floor(($side - $dstH) / 2);

        $raw = '';

        for ($y = 0; $y < $side; $y++) {
            $raw .= "\x00";

            for ($x = 0; $x < $side; $x++) {
                $lx = $x - $offX;
                $ly = $y - $offY;

                if ($lx < 0 || $ly < 0 || $lx >= $dstW || $ly >= $dstH) {
                    $raw .= "\x00\x00\x00\x00";

                    continue;
                }

                // Bilinear dari koordinat sumber (alpha PNG: 0 transparan).
                $fx = (($lx + 0.5) / $dstW) * $srcW - 0.5;
                $fy = (($ly + 0.5) / $dstH) * $srcH - 0.5;
                $x0 = (int) floor($fx);
                $y0 = (int) floor($fy);
                $tx = $fx - $x0;
                $ty = $fy - $y0;

                $r = $g = $b = $a = 0.0;

                foreach ([[0, 0, (1 - $tx) * (1 - $ty)], [1, 0, $tx * (1 - $ty)], [0, 1, (1 - $tx) * $ty], [1, 1, $tx * $ty]] as [$dx, $dy, $w]) {
                    $px = min($srcW - 1, max(0, $x0 + $dx));
                    $py = min($srcH - 1, max(0, $y0 + $dy));
                    $p = unpack('C4', substr($pixels, ($py * $srcW + $px) * 4, 4));
                    $r += $p[1] * $w;
                    $g += $p[2] * $w;
                    $b += $p[3] * $w;
                    $a += $p[4] * $w;
                }

                $raw .= pack('C4', (int) round($r), (int) round($g), (int) round($b), (int) round($a));
            }
        }

        return self::png(self::chunk('IHDR', pack('NNCCCCC', $side, $side, 8, 6, 0, 0, 0))
            .self::chunk('IDAT', (string) gzcompress($raw))
            .self::chunk('IEND', ''));
    }

    /**
     * Decode PNG 8-bit non-interlaced (tipe 0/2/4/6) menjadi string
     * biner RGBA (alpha PNG: 0 transparan, 255 opaque).
     *
     * @return array{0: int, 1: int, 2: string}|null
     */
    public static function decodePng(string $bytes): ?array
    {
        if (substr($bytes, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            return null;
        }

        $pos = 8;
        $len = strlen($bytes);
        $ihdr = null;
        $idat = '';

        while ($pos + 8 <= $len) {
            $size = unpack('N', substr($bytes, $pos, 4))[1];
            $type = substr($bytes, $pos + 4, 4);
            $data = substr($bytes, $pos + 8, $size);

            if ($type === 'IHDR') {
                $ihdr = unpack('Nw/Nh/Cdepth/Cct/Ccomp/Cfilter/Cinterlace', $data);
            } elseif ($type === 'IDAT') {
                $idat .= $data;
            } elseif ($type === 'IEND') {
                break;
            }

            $pos += 12 + $size;
        }

        if ($ihdr === null || $idat === '') {
            return null;
        }

        $w = $ihdr['w'];
        $h = $ihdr['h'];

        if ($w < 1 || $h < 1 || $w > 4096 || $h > 4096
            || $ihdr['depth'] !== 8 || $ihdr['interlace'] !== 0
            || ! in_array($ihdr['ct'], [0, 2, 4, 6], true)) {
            return null;
        }

        $bpp = [0 => 1, 2 => 3, 4 => 2, 6 => 4][$ihdr['ct']];
        $raw = @gzuncompress($idat);

        if (! is_string($raw) || strlen($raw) !== $h * (1 + $w * $bpp)) {
            return null;
        }

        $pixels = '';
        $prev = array_fill(0, $w * $bpp, 0);

        for ($y = 0; $y < $h; $y++) {
            $row = substr($raw, $y * (1 + $w * $bpp), 1 + $w * $bpp);
            $filter = ord($row[0]);
            $cur = array_values(unpack('C*', substr($row, 1)));

            for ($i = 0; $i < $w * $bpp; $i++) {
                $a = $i >= $bpp ? $cur[$i - $bpp] : 0;
                $b = $prev[$i];
                $c = $i >= $bpp ? $prev[$i - $bpp] : 0;

                $cur[$i] = ($cur[$i] + match ($filter) {
                    0 => 0,
                    1 => $a,
                    2 => $b,
                    3 => ($a + $b) >> 1,
                    4 => self::paeth($a, $b, $c),
                    default => 0,
                }) & 0xFF;
            }

            for ($x = 0; $x < $w; $x++) {
                $o = $x * $bpp;

                $pixels .= match ($ihdr['ct']) {
                    0 => pack('C4', $cur[$o], $cur[$o], $cur[$o], 255),
                    2 => pack('C4', $cur[$o], $cur[$o + 1], $cur[$o + 2], 255),
                    4 => pack('C4', $cur[$o], $cur[$o], $cur[$o], $cur[$o + 1]),
                    default => pack('C4', $cur[$o], $cur[$o + 1], $cur[$o + 2], $cur[$o + 3]),
                };
            }

            $prev = $cur;
        }

        return [$w, $h, $pixels];
    }

    private static function paeth(int $a, int $b, int $c): int
    {
        $p = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);

        return $pa <= $pb && $pa <= $pc ? $a : ($pb <= $pc ? $b : $c);
    }

    /**
     * Decode format non-PNG lewat GD bila tersedia.
     *
     * @return array{0: int, 1: int, 2: string}|null
     */
    private static function decodeGd(string $bytes): ?array
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $img = @imagecreatefromstring($bytes);

        if (! $img instanceof \GdImage) {
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        if ($w < 1 || $h < 1 || $w > 4096 || $h > 4096) {
            return null;
        }

        $pixels = '';

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($img, $x, $y);
                // Alpha GD 0 (opaque)..127 -> alpha PNG 255..0.
                $pixels .= pack('C4', ($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF, 255 - (($c >> 24) & 0x7F) * 2);
            }
        }

        return [$w, $h, $pixels];
    }

    private static function png(string $chunks): string
    {
        return "\x89PNG\r\n\x1a\n".$chunks;
    }

    private static function chunk(string $type, string $data): string
    {
        return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    }
}
