<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Pejabat;
use Illuminate\Database\Seeder;

class PejabatSeeder extends Seeder
{
    /**
     * OpenSID parity (issue #15 checklist 3 — honest note, no fabricated data).
     *
     * - Sumber: akses OpenSID tidak tersedia di lingkungan ini; repo dan
     *   PRD tidak memuat dump tabel aparatur, jadi tidak ada baris asli
     *   yang bisa dipindahkan.
     * - Row SEBELUM: 0 (tabel `pejabats` belum ada; tidak ada sumber).
     * - Row SESUDAH: 5 baris contoh untuk Kepandean (1 pimpinan,
     *   2 perangkat, 2 wilayah: RW 05 + RW 06), 0 baris untuk Desa B.
     * - Nama contoh meniru visual design/Struktur.png (Wastedjo, Heri A.
     *   Tiar, dsb.) HANYA untuk tata letak/UAT — kebenaran = tabel
     *   aparatur OpenSID + konfirmasi perangkat desa. JANGAN anggap ini
     *   data migrasi asli; ganti via halaman admin setelah konfirmasi.
     * - Roster rumpang (RT, RW selain 05/06) ditandai kosong untuk
     *   konfirmasi perangkat desa.
     * - Idempotent: firstOrCreate pada (desa_id, nama), aman dijalankan
     *   ulang; terkunci oleh
     *   PejabatPublicTest::test_pejabat_seeder_parity_five_rows_two_wilayah_idempotent.
     */
    public function run(): void
    {
        $desa = Desa::where('slug', 'kepandean')->first();

        if (! $desa instanceof Desa) {
            return;
        }

        $rows = [
            ['nama' => 'Wastedjo', 'jabatan' => 'Kepala Desa', 'kelompok' => Pejabat::KELOMPOK_PIMPINAN, 'urutan' => 1],
            ['nama' => 'Heri A. Tiar', 'jabatan' => 'Sekretaris Desa', 'kelompok' => Pejabat::KELOMPOK_PERANGKAT, 'urutan' => 2],
            ['nama' => 'Nurcholis', 'jabatan' => 'Bendahara Desa', 'kelompok' => Pejabat::KELOMPOK_PERANGKAT, 'urutan' => 3],
            ['nama' => 'Daryono', 'jabatan' => 'Ketua RW', 'kelompok' => Pejabat::KELOMPOK_WILAYAH, 'wilayah_label' => 'RW 05', 'urutan' => 4],
            ['nama' => 'Ratmo', 'jabatan' => 'Ketua RW', 'kelompok' => Pejabat::KELOMPOK_WILAYAH, 'wilayah_label' => 'RW 06', 'urutan' => 5],
        ];

        foreach ($rows as $row) {
            Pejabat::withoutGlobalScope('desa')->firstOrCreate(
                ['desa_id' => $desa->id, 'nama' => $row['nama']],
                $row + ['desa_id' => $desa->id]
            );
        }
    }
}
