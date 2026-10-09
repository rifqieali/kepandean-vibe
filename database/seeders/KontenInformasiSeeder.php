<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Kategori;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Dummy konten halaman Informasi (Berita + Pengumuman + Kegiatan).
 *
 * Cover memakai ulang foto asli di `database/data/images` (disalin ke
 * disk publik), bukan aset generasian, sehingga tidak ada klaim visual
 * yang dikarang. Idempoten: kategori di-firstOrCreate per slug, terbitan
 * di-updateOrCreate per slug, sehingga upload admin di luar daftar ini
 * tidak tersentuh. Berkas sumber yang hilang hanya dilewati dengan
 * peringatan, bukan gagal.
 */
class KontenInformasiSeeder extends Seeder
{
    /** @var array<int, array{nama: string, slug: string}> */
    private const KATEGORIS = [
        ['nama' => 'Pembangunan', 'slug' => 'pembangunan'],
        ['nama' => 'Kegiatan Warga', 'slug' => 'kegiatan-warga'],
        ['nama' => 'Pemerintahan', 'slug' => 'pemerintahan'],
    ];

    /** @var array<int, array{slug: string, judul: string, kategori: string, cover: string, hari_lalu: int, isi: string}> */
    private const BERITAS = [
        [
            'slug' => 'musyawarah-dusun-rpjmdes-2026',
            'judul' => 'Musyawarah Dusun Bahas Prioritas Pembangunan 2026',
            'kategori' => 'pembangunan',
            'cover' => 'slide-2.png',
            'hari_lalu' => 1,
            'isi' => '<p>Pemerintah Desa Kepandean menggelar musyawarah dusun untuk menjaring usulan prioritas pembangunan tahun 2026. Warga mengusulkan perbaikan jalan lingkungan, drainase, dan penerangan jalan.</p><p>Hasil musyawarah dibawa ke musrenbangdes untuk dibahas bersama perangkat desa dan BPD.</p>',
        ],
        [
            'slug' => 'posyandu-balita-september',
            'judul' => 'Posyandu Balita Rutin Digelar di Tiga Dusun',
            'kategori' => 'kegiatan-warga',
            'cover' => 'slide-4.png',
            'hari_lalu' => 3,
            'isi' => '<p>Kader posyandu Desa Kepandean menggelar penimbangan dan pemeriksaan balita rutin di tiga dusun. Kegiatan meliputi penimbangan berat badan, pengukuran tinggi badan, dan pemberian makanan tambahan.</p><p>Orang tua diimbau membawa buku KIA setiap datang ke posyandu.</p>',
        ],
        [
            'slug' => 'pengajian-akbar-masjid-al-hikmah',
            'judul' => 'Pengajian Akbar di Masjid Al-Hikmah Berjalan Khidmat',
            'kategori' => 'kegiatan-warga',
            'cover' => 'slide-3.png',
            'hari_lalu' => 6,
            'isi' => '<p>Ratusan warga menghadiri pengajian akbar di Masjid Al-Hikmah Kepandean. Acara diisi tausiyah dan doa bersama untuk keselamatan desa.</p><p>Panitia berterima kasih atas partisipasi warga dan donatur yang mendukung acara.</p>',
        ],
        [
            'slug' => 'umkm-latopia-pasar-murah',
            'judul' => 'UMKM Kudapan Latopia Ramaikan Pasar Murah Desa',
            'kategori' => 'pembangunan',
            'cover' => 'slide-5.png',
            'hari_lalu' => 9,
            'isi' => '<p>Jika berkunjung ke Tegal, berburu kuliner khas tentu menjadi agenda yang tak boleh terlewatkan. Salah satu buah tangan legendaris yang selalu dicari wisatawan maupun warga lokal adalah latopia kue pastry tradisional khas Tegal berbentuk kotak/bulat dengan kulit garing berlapis dan isian padat, mirip bakpia namun memiliki tekstur yang lebih renyah.
            Di antara produsen yang konsisten menjaga kualitas rasa, Latopia Bintang dari Desa Kepandean RT 02/01, Kecamatan Dukuhturi, Tegal, Jawa Tengah, menjadi salah satu ikon yang sangat diperhitungkan. Berdiri sejak tahun 2010, Latopia Bintang merupakan bentuk kehangatan usaha keluarga yang dikelola oleh Bpk. Abdul Jamil bersama Ibu Ani. Berbekal komitmen untuk mempertahankan cita rasa autentik dan keaslian resep warisan berslogan "Manis... Enak... Gurih...", usaha ini terus berkembang tanpa kehilangan karakter khasnya. Produk ini juga terjamin kebersihannya serta sudah memiliki izin P-IRT (No. 2053328010524-28) dan sertifikasi Halal.
            Dibuat menggunakan bahan-bahan pilihan seperti tepung terigu, mentega, gula pasir, minyak sayur, dan kacang hijau, dapur produksi Latopia Bintang mampu menghasilkan kurang lebih 3.000 biji latopia setiap harinya. Tekstur kulitnya yang renyah berpadu dengan isian padat dan lembut, hadir dalam tiga varian rasa andalan: rasa original kacang ijo dengan perpaduan gurih manis yang pas, coklat bagi penikmat manis modern, hingga varian buah yang memberikan sensasi segar dan unik dalam setiap gigitan. Menariknya, jika pembeli datang langsung ke rumah produksinya di Kepandean, pelanggan bisa meminta (request) isian campur dari semua varian rasa dalam satu kemasan.
            Selain mengedepankan kualitas bahan dan fleksibilitas varian rasa, Latopia Bintang menawarkan harga yang sangat bersahabat, yaitu Rp10.000 per kemasan eceran isi 10 biji, serta membuka peluang kemitraan bagi para reseller dengan harga khusus Rp7.000 per kemasan. Untuk pembelian harian, latopia ini dikemas dalam kantong plastik yang praktis. Namun, bagi wisatawan atau warga yang ingin membawa buah tangan ke luar kota, tersedia opsi kemasan besek dari anyaman bambu yang dapat memperkuat kesan tradisional sekaligus menjaga kualitas latopia tetap aman dan tidak hancur selama perjalanan jauh. Saat ini, jangkauan penyalurannya telah merambah berbagai titik di wilayah Tegal dan sekitarnya. Untuk pemesanan maupun informasi kemitraan, Anda dapat menghubungi nomor 082313823240.</p>',
        ],
        [
            'slug' => 'sosialisasi-administrasi-kependudukan',
            'judul' => 'Sosialisasi Administrasi Kependudukan di Balai Desa',
            'kategori' => 'pemerintahan',
            'cover' => 'slide-1.png',
            'hari_lalu' => 12,
            'isi' => '<p>Perangkat desa memberikan sosialisasi pengurusan KTP, KK, dan surat pindah kepada warga di balai desa. Warga dilayani langsung dan mendapat penjelasan alur yang mudah diikuti.</p><p>Layanan administrasi buka setiap hari kerja pukul 08.00 sampai 14.00 WIB.</p>',
        ],
        [
            'slug' => 'kerja-bakti-saluran-air',
            'judul' => 'Kerja Bakti Normalisasi Saluran Air Libatkan Tiga RT',
            'kategori' => 'kegiatan-warga',
            'cover' => 'foto-sejarah.jpeg',
            'hari_lalu' => 15,
            'isi' => '<p>Warga tiga RT bergotong royong menormalisasi saluran air menjelang musim hujan. Tumpukan sampah dan lumpur dibersihkan agar aliran lancar.</p><p>Kegiatan ditutup dengan makan bersama di balai dusun.</p>',
        ],
    ];

    /** @var array<int, array{slug: string, judul: string, kategori: string, cover: string, tanggal: string, isi: string}> */
    private const ARTIKEL_ASLI = [
        [
            'slug' => 'pemerintah-desa-kepandean',
            'judul' => 'Pemerintah Desa Kepandean',
            'kategori' => 'pemerintahan',
            'cover' => 'artikel-pemerintah-desa.jpg',
            'tanggal' => '2025-03-25',
            'isi' => '<p>Pemerintah Desa Kepandean terletak di daerah iklim tropis berada di perbatasan Kabupaten Tegal dan Kota Tegal. Beralamat lengkap di Jalan Raya Kepandean Desa Kepandean Kecamatan Dukuhturi Kabupaten Tegal.</p>',
        ],
        [
            'slug' => 'musyawarah-desa-penetapan-apbdes-2025',
            'judul' => 'Musyawarah Desa Penetapan APBDES Tahun Anggaran 2025',
            'kategori' => 'pemerintahan',
            'cover' => 'artikel-musdes-apbdes-2025.jpg',
            'tanggal' => '2025-03-25',
            'isi' => '<p>Pada hari ini tanggal 25 Maret 2025 Pemerintah Desa Kepandean mengadakan musyawarah desa penetapan APBDes Tahun 2025 di Balai Desa Kepandean. Dihadiri oleh para Ketua RT dan RW se Desa Kepandean serta dipimpin oleh Kepala Desa Kepandean dan Ketua BPD Desa Kepandean berjalan dengan hikmat.</p>',
        ],
    ];

    /** @var array<int, array{slug: string, judul: string, cover: string, hari_lalu: int, kedaluarsa_hari: int|null, isi: string}> */
    private const PENGUMUMANS = [
        [
            'slug' => 'jadwal-posyandu-oktober',
            'judul' => 'Jadwal Posyandu Bulan Oktober 2026',
            'cover' => 'HERO-LAYANAN.png',
            'hari_lalu' => 2,
            'kedaluarsa_hari' => 30,
            'isi' => '<p>Diumumkan jadwal posyandu bulan Oktober 2026 di seluruh dusun Desa Kepandean. Setiap dusun memiliki jadwal berbeda, tercantum pada papan informasi balai desa.</p><p>Bawa buku KIA dan kartu berobat saat datang.</p>',
        ],
        [
            'slug' => 'pendaftaran-bantuan-sembako',
            'judul' => 'Pendataan Penerima Bantuan Sembako Tahap Berikutnya',
            'cover' => 'HERO-INFORMASI.png',
            'hari_lalu' => 4,
            'kedaluarsa_hari' => 21,
            'isi' => '<p>Pemerintah desa membuka pendataan calon penerima bantuan sembako tahap berikutnya. Warga mendaftar lewat ketua RT dengan membawa fotokopi KK dan KTP.</p><p>Pendaftaran ditutup sesuai jadwal yang ditempel di papan pengumuman dusun.</p>',
        ],
        [
            'slug' => 'pemadaman-listrik-bergilir',
            'judul' => 'Informasi Pemeliharaan Jaringan Listrik Dusun Krajan',
            'cover' => 'HERO-PEMERINTAHAN.png',
            'hari_lalu' => 7,
            'kedaluarsa_hari' => 14,
            'isi' => '<p>Petugas PLN melakukan pemeliharaan jaringan di Dusun Krajan sehingga listrik padam sementara sesuai jadwal yang diumumkan ketua RT.</p><p>Warga diimbau mencabut peralatan elektronik yang sensitif selama pemeliharaan.</p>',
        ],
        [
            'slug' => 'lowongan-kader-posyandu',
            'judul' => 'Rekrutmen Kader Posyandu Baru Tahun 2026',
            'cover' => 'HERO-POTENSI.png',
            'hari_lalu' => 10,
            'kedaluarsa_hari' => null,
            'isi' => '<p>Dibuka pendaftaran kader posyandu baru untuk memperkuat layanan kesehatan ibu dan anak. Syarat dan berkas pendaftaran dapat ditanyakan ke perangkat desa bagian pelayanan.</p><p>Pelamar yang lolos seleksi berkas mengikuti wawancara di balai desa.</p>',
        ],
    ];

    /** @var array<int, array{slug: string, judul: string, cover: string, hari_lalu: int, kedaluarsa_hari: int|null, isi: string}> */
    private const KEGIATANS = [
        [
            'slug' => 'senam-pagi-rutin-balai-desa',
            'judul' => 'Senam Pagi Rutin Setiap Minggu di Halaman Balai Desa',
            'cover' => 'slide-1.png',
            'hari_lalu' => 1,
            'kedaluarsa_hari' => null,
            'isi' => '<p>Senam pagi rutin digelar setiap Minggu pukul 06.30 WIB di halaman balai desa. Kegiatan terbuka untuk seluruh warga tanpa dipungut biaya.</p><p>Peserta diimbau membawa air minum dan memakai pakaian olahraga yang nyaman.</p>',
        ],
        [
            'slug' => 'pelatihan-umkm-kemasan-produk',
            'judul' => 'Pelatihan Pengemasan Produk untuk Pelaku UMKM',
            'cover' => 'slide-5.png',
            'hari_lalu' => 5,
            'kedaluarsa_hari' => 25,
            'isi' => '<p>Pemerintah desa mengadakan pelatihan pengemasan produk bagi pelaku UMKM agar produk lebih menarik dan tahan lama. Peserta praktik langsung mengemas produk bawaannya.</p><p>Pendaftaran lewat perangkat desa bagian ekonomi dan pembangunan.</p>',
        ],
        [
            'slug' => 'turnamen-volleyball-antar-rt',
            'judul' => 'Turnamen Bola Voli Antar RT Meriahkan HUT Desa',
            'cover' => 'slide-2.png',
            'hari_lalu' => 8,
            'kedaluarsa_hari' => 20,
            'isi' => '<p>Karang taruna menggelar turnamen bola voli antar RT dalam rangka memeriahkan HUT desa. Pertandingan digelar di lapangan desa setiap sore hari.</p><p>Dukungan suporter diharapkan tertib dan menjunjung sportivitas.</p>',
        ],
        [
            'slug' => 'penyuluhan-pertanian-padi',
            'judul' => 'Penyuluhan Budidaya Padi Bersama Petugas Lapangan',
            'cover' => 'HERO-POTENSI.png',
            'hari_lalu' => 11,
            'kedaluarsa_hari' => null,
            'isi' => '<p>Petugas penyuluh pertanian memberikan penyuluhan budidaya padi kepada kelompok tani, mulai dari pemilihan benih sampai pengendalian hama.</p><p>Pertemuan kelompok tani berikutnya membahas jadwal tanam serentak.</p>',
        ],
    ];

    public function run(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();

        if (! $desa instanceof Desa) {
            $this->command?->warn('KontenInformasiSeeder: desa kepandean tidak ada, dilewati.');

            return;
        }

        $kategoriIds = [];

        foreach (self::KATEGORIS as $kategori) {
            $model = Kategori::withoutGlobalScope('desa')->firstOrCreate(
                ['desa_id' => $desa->id, 'slug' => $kategori['slug']],
                ['nama' => $kategori['nama']]
            );
            $kategoriIds[$kategori['slug']] = $model->id;
        }

        foreach (self::BERITAS as $berita) {
            $path = $this->salin($berita['cover'], 'cover/' . $berita['slug'] . '.' . pathinfo($berita['cover'], PATHINFO_EXTENSION));

            if ($path === null) {
                continue;
            }

            Berita::withoutGlobalScope('desa')->updateOrCreate(
                ['desa_id' => $desa->id, 'slug' => $berita['slug']],
                [
                    'kategori_id' => $kategoriIds[$berita['kategori']] ?? null,
                    'judul' => $berita['judul'],
                    'isi' => $berita['isi'],
                    'cover_path' => $path,
                    'status' => Berita::STATUS_PUBLISHED,
                    'published_at' => now()->subDays($berita['hari_lalu']),
                ]
            );
        }

        // Artikel asli dari web lama (tanggal terbit dipertahankan).
        foreach (self::ARTIKEL_ASLI as $berita) {
            $path = $this->salin($berita['cover'], 'cover/' . $berita['slug'] . '.' . pathinfo($berita['cover'], PATHINFO_EXTENSION));

            if ($path === null) {
                continue;
            }

            Berita::withoutGlobalScope('desa')->updateOrCreate(
                ['desa_id' => $desa->id, 'slug' => $berita['slug']],
                [
                    'kategori_id' => $kategoriIds[$berita['kategori']] ?? null,
                    'judul' => $berita['judul'],
                    'isi' => $berita['isi'],
                    'cover_path' => $path,
                    'status' => Berita::STATUS_PUBLISHED,
                    'published_at' => $berita['tanggal'],
                ]
            );
        }

        foreach (self::PENGUMUMANS as $pengumuman) {
            $path = $this->salin($pengumuman['cover'], 'cover/' . $pengumuman['slug'] . '.' . pathinfo($pengumuman['cover'], PATHINFO_EXTENSION));

            if ($path === null) {
                continue;
            }

            Pengumuman::withoutGlobalScope('desa')->updateOrCreate(
                ['desa_id' => $desa->id, 'slug' => $pengumuman['slug']],
                [
                    'judul' => $pengumuman['judul'],
                    'isi' => $pengumuman['isi'],
                    'cover_path' => $path,
                    'status' => Pengumuman::STATUS_PUBLISHED,
                    'published_at' => now()->subDays($pengumuman['hari_lalu']),
                    'expired_at' => $pengumuman['kedaluarsa_hari'] === null
                        ? null
                        : now()->addDays($pengumuman['kedaluarsa_hari']),
                ]
            );
        }

        foreach (self::KEGIATANS as $kegiatan) {
            $path = $this->salin($kegiatan['cover'], 'cover/' . $kegiatan['slug'] . '.' . pathinfo($kegiatan['cover'], PATHINFO_EXTENSION));

            if ($path === null) {
                continue;
            }

            Kegiatan::withoutGlobalScope('desa')->updateOrCreate(
                ['desa_id' => $desa->id, 'slug' => $kegiatan['slug']],
                [
                    'judul' => $kegiatan['judul'],
                    'isi' => $kegiatan['isi'],
                    'cover_path' => $path,
                    'status' => Kegiatan::STATUS_PUBLISHED,
                    'published_at' => now()->subDays($kegiatan['hari_lalu']),
                    'expired_at' => $kegiatan['kedaluarsa_hari'] === null
                        ? null
                        : now()->addDays($kegiatan['kedaluarsa_hari']),
                ]
            );
        }
    }

    /**
     * Salin satu berkas sumber ke disk publik. Kembalikan path relatif
     * atau null bila sumber hilang (diperingatkan, tidak digagalkan).
     */
    private function salin(string $berkas, string $tujuan): ?string
    {
        $sumber = database_path('data/images/' . $berkas);

        if (! is_file($sumber)) {
            $this->command?->warn("KontenInformasiSeeder: sumber {$berkas} tidak ada, dilewati.");

            return null;
        }

        Storage::disk('public')->put($tujuan, (string) file_get_contents($sumber));

        return $tujuan;
    }
}
