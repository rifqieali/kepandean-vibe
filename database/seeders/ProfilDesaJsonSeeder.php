<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Pejabat;
use App\Models\Profil;
use App\Models\Statistik;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProfilDesaJsonSeeder extends Seeder
{
    /**
     * Seed profil, statistik, dan roster pejabat Kepandean dari
     * database/data/profil-desa-kepandean.json (disalin verbatim dari
     * database/Profil_Desa_Kepandean.md, tanpa fabrikasi).
     *
     * Dijalankan SETELAH DesaSeeder sehingga data terkonfirmasi
     * dari dokumen mengisi profil, statistik, dan roster pejabat desa
     * (misi 3 poin, statistik 6997 jiwa, roster 31 pejabat).
     *
     * Idempotent: Profil/Statistik via updateOrCreate, roster pejabat
     * dihapus lalu dimasukkan ulang sesuai JSON.
     */
    public function run(): void
    {
        $path = database_path('data/profil-desa-kepandean.json');

        if (! is_file($path)) {
            throw new RuntimeException("Data profil desa tidak ditemukan: {$path}");
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            throw new RuntimeException("Data profil desa bukan JSON valid: {$path}");
        }

        $desa = Desa::where('slug', $data['desa_slug'] ?? 'kepandean')->first();

        if (! $desa instanceof Desa) {
            return;
        }

        Profil::withoutGlobalScope('desa')->updateOrCreate(
            ['desa_id' => $desa->id],
            [
                'sejarah' => $data['sejarah'],
                'visi' => $data['visi'],
                'misi' => collect($data['misi'])
                    ->map(fn (string $item, int $i): string => ($i + 1).'. '.$item)
                    ->implode("\n"),
            ]
        );

        foreach ([
            'total_jiwa' => $data['geografis']['total_jiwa'],
            'laki_laki' => $data['geografis']['laki_laki'],
            'perempuan' => $data['geografis']['perempuan'],
            'kepala_keluarga' => $data['geografis']['kepala_keluarga'],
        ] as $kunci => $nilai) {
            Statistik::withoutGlobalScope('desa')->updateOrCreate(
                ['desa_id' => $desa->id, 'kunci' => $kunci],
                ['nilai' => (string) $nilai]
            );
        }

        Pejabat::withoutGlobalScope('desa')->where('desa_id', $desa->id)->delete();

        foreach ($data['pejabat'] as $row) {
            Pejabat::create($row + ['desa_id' => $desa->id]);
        }
    }
}
