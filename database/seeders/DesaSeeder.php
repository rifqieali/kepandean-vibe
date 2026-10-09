<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Profil;
use App\Models\Statistik;
use Illuminate\Database\Seeder;

class DesaSeeder extends Seeder
{
    public function run(): void
    {
        $kepandean = Desa::firstOrCreate(
            ['slug' => 'kepandean'],
            [
                'name' => 'Desa Kepandean',
                'slug' => 'kepandean',
                'domain' => 'kepandean.test',
                'is_default' => true,
            ]
        );

        $desaB = Desa::firstOrCreate(
            ['slug' => 'desa-b'],
            [
                'name' => 'Desa B',
                'slug' => 'desa-b',
                'domain' => 'desa-b.test',
                'is_default' => false,
            ]
        );

        // Empty singleton rows: real Sejarah/Visi/Misi text still waits for
        // confirmation from perangkat desa, so nothing is fabricated here.
        Profil::forDesa($kepandean);
        Profil::forDesa($desaB);

        // Nilai awal spec-002; admin dapat mengubahnya tanpa developer.
        Statistik::seedDefaults($kepandean);
        Statistik::seedDefaults($desaB);
    }
}
