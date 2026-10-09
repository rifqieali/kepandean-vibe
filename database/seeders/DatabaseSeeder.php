<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DesaSeeder::class);

        $kepandean = Desa::where('slug', 'kepandean')->first();

        User::firstOrCreate(
            ['email' => 'admin@kepandean.id'],
            [
                'name' => 'Admin Kepandean',
                'password' => 'password',
                'desa_id' => $kepandean?->id,
                'role' => 'admin_desa',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@techade.dev'],
            [
                'name' => 'Techade',
                'password' => 'Sukses2026!',
                'desa_id' => null,
                'role' => 'techade',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'editor@kepandean.id'],
            [
                'name' => 'Editor Kepandean',
                'password' => 'password',
                'desa_id' => $kepandean?->id,
                'role' => 'editor',
                'email_verified_at' => now(),
            ]
        );

        $this->call(ProfilDesaJsonSeeder::class);
        $this->call(KontenInformasiSeeder::class);
        $this->call(HeroGambarSeeder::class);
    }
}
