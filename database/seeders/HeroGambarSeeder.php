<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\HeroSlide;
use App\Models\LamanHero;
use App\Models\Profil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Gambar hero dari `database/data/images`: 5 slide Beranda + hero tiap
 * laman. Laman dropdown mengikuti gambar induknya (berita/pengumuman/
 * kegiatan ikut Informasi; struktur ikut Profil; lembaga-desa/produk-
 * hukum/laporan ikut Pemerintahan; layanan ikut Layanan Warga).
 *
 * Idempoten: berkas disalin ulang tiap jalan, baris di-upsert per
 * (desa, urutan) untuk slide dan (desa, slug) untuk laman, sehingga
 * upload admin di luar daftar ini tidak tersentuh. Berkas sumber yang
 * hilang hanya dilewati dengan peringatan, bukan gagal.
 *
 * Media profil (logo + foto sejarah) hanya diisi bila masih kosong:
 * logo dan foto hasil upload admin tidak pernah ditimpa seeder ini.
 */
class HeroGambarSeeder extends Seeder
{
    /** @var array<int, array{berkas: string, judul: string, subjudul: string|null}> */
    private const SLIDES = [
        [
            'berkas' => 'slide-1.png',
            'judul' => 'Selamat Datang di Desa Kepandean',
            'subjudul' => 'Kecamatan Dukuhturi, Kabupaten Tegal',
        ],
        [
            'berkas' => 'slide-2.png',
            'judul' => 'Gerbang Desa Kepandean',
            'subjudul' => 'Kec. Dukuhturi, Kab. Tegal',
        ],
        [
            'berkas' => 'slide-3.png',
            'judul' => "Masjid Jami' Al-Hikmah",
            'subjudul' => 'Kepandean, Dukuhturi, Tegal',
        ],
        [
            'berkas' => 'slide-4.png',
            'judul' => 'SD Negeri Kepandean 03',
            'subjudul' => 'Kec. Dukuhturi, Kab. Tegal',
        ],
        [
            'berkas' => 'slide-5.png',
            'judul' => 'UMKM Kudapan Latopia',
            'subjudul' => 'Potensi ekonomi lokal Desa Kepandean',
        ],
    ];

    /** @var array<string, string> slug laman => berkas sumber */
    private const LAMAN = [
        'informasi' => 'HERO-INFORMASI.png',
        'berita' => 'HERO-INFORMASI.png',
        'pengumuman' => 'HERO-INFORMASI.png',
        'kegiatan' => 'HERO-INFORMASI.png',
        'profil' => 'HERO-PROFIL DESA.png',
        'struktur' => 'HERO-PROFIL DESA.png',
        'pemerintahan' => 'HERO-PEMERINTAHAN.png',
        'lembaga-desa' => 'HERO-PEMERINTAHAN.png',
        'produk-hukum' => 'HERO-PEMERINTAHAN.png',
        'laporan' => 'HERO-PEMERINTAHAN.png',
        'layanan-warga' => 'HERO-LAYANAN.png',
        'layanan' => 'HERO-LAYANAN.png',
        'potensi-galeri' => 'HERO-POTENSI.png',
    ];

    public function run(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();

        if (! $desa instanceof Desa) {
            $this->command?->warn('HeroGambarSeeder: desa kepandean tidak ada, dilewati.');

            return;
        }

        foreach (self::SLIDES as $i => $slide) {
            $urutan = $i + 1;
            $path = $this->salin($slide['berkas'], 'hero/'.mb_strtolower($slide['berkas']));

            if ($path === null) {
                continue;
            }

            HeroSlide::withoutGlobalScope('desa')->updateOrCreate(
                ['desa_id' => $desa->id, 'urutan' => $urutan],
                [
                    'gambar_path' => $path,
                    'judul' => $slide['judul'],
                    'subjudul' => $slide['subjudul'],
                    'tautan_label' => $urutan === 1 ? 'Jelajahi Desa' : null,
                    'tautan_url' => $urutan === 1 ? '#layanan' : null,
                    'aktif' => true,
                ]
            );
        }

        foreach (self::LAMAN as $slug => $berkas) {
            $namaTarget = mb_strtolower(str_replace(' ', '-', $berkas));
            $path = $this->salin($berkas, 'hero-laman/'.$namaTarget);

            if ($path === null) {
                continue;
            }

            LamanHero::withoutGlobalScope('desa')->updateOrCreate(
                ['desa_id' => $desa->id, 'slug' => $slug],
                ['gambar_path' => $path]
            );
        }

        // Media profil: logo + foto sejarah dari database/data/images.
        // Hanya mengisi yang masih kosong supaya upload admin tidak
        // pernah ditimpa; kolom teks (sejarah/visi/misi) tidak disentuh.
        $profil = Profil::withoutGlobalScope('desa')->firstOrNew(['desa_id' => $desa->id]);
        $profil->foto_path = $this->mediaAktif($profil->foto_path, 'foto-sejarah.jpeg', 'profil/foto-sejarah.jpeg');
        $profil->logo_path = $this->mediaAktif($profil->logo_path, 'Logo.png', 'profil/logo-desa.png');

        if ($profil->isDirty()) {
            $profil->save();
        }
    }

    /**
     * Kembalikan path aktif untuk satu media profil: pertahankan nilai
     * lama bila berkasnya masih ada di disk (milik admin), selain itu
     * salin berkas sumber dan kembalikan path barunya (atau nilai lama
     * bila sumber hilang).
     */
    private function mediaAktif(?string $saatIni, string $berkas, string $tujuan): ?string
    {
        if (is_string($saatIni) && $saatIni !== '' && Storage::disk('public')->exists($saatIni)) {
            return $saatIni;
        }

        return $this->salin($berkas, $tujuan) ?? $saatIni;
    }

    /**
     * Salin satu berkas sumber ke disk publik. Kembalikan path relatif
     * atau null bila sumber hilang (diperingatkan, tidak digagalkan).
     */
    private function salin(string $berkas, string $tujuan): ?string
    {
        $sumber = database_path('data/images/'.$berkas);

        if (! is_file($sumber)) {
            $this->command?->warn("HeroGambarSeeder: sumber {$berkas} tidak ada, dilewati.");

            return null;
        }

        Storage::disk('public')->put($tujuan, (string) file_get_contents($sumber));

        return $tujuan;
    }
}
