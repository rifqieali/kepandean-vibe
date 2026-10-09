<?php

use App\Models\Desa;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

// Dijalankan sekali tiap container start (lihat docker/entrypoint.sh).
// Idempoten: aman dijalankan ulang — migrate, desa default, admin, symlink.

$root = '/var/www/html';

require $root.'/vendor/autoload.php';

$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);

// 1. Migrasi (termasuk tabel sessions/cache/jobs bawaan Laravel).
// Catatan: call() pertama ini juga me-bootstrap app, jadi helper config()
// baru boleh dipakai SETELAH baris ini (sebelumnya binding 'config'
// belum ada -> "Class config does not exist").
$kernel->call('migrate', ['--force' => true]);
echo Artisan::output();

// Diagnostik (tanpa secret): koneksi DB efektif yang dipakai app.
$driver = config('database.default');
$host = config("database.connections.{$driver}.host");
$database = config("database.connections.{$driver}.database");
echo "DB efektif: {$driver} @ {$host}/{$database}\n";

// 2. Seeder otomatis jika database masih baru (mengisi desa, profil, slide hero, konten)
if (Profil::count() === 0) {
    echo "Database baru terdeteksi: menjalankan DatabaseSeeder...\n";
    $kernel->call('db:seed', ['--force' => true]);
    echo Artisan::output();
}

$desa = Desa::where('slug', 'kepandean')->first() ?? Desa::first();
if (! $desa) {
    $desa = Desa::create([
        'name' => 'Kepandean',
        'slug' => 'kepandean',
        'is_default' => true,
    ]);
    echo "Desa default dibuat.\n";
}

// 3. Akun admin dari env (diisi saat deploy Blueprint).
$email = (string) getenv('ADMIN_EMAIL');
$password = (string) getenv('ADMIN_PASSWORD');
if ($email !== '' && $password !== '') {
    $data = [
        'name' => 'Admin Desa',
        'password' => Hash::make($password),
        'desa_id' => $desa->id,
        'role' => 'admin_desa',
    ];
    $user = User::where('email', $email)->first();
    if ($user) {
        $user->update($data);
        echo "Admin diperbarui: {$email}\n";
    } else {
        User::create($data + ['email' => $email]);
        echo "Admin dibuat: {$email}\n";
    }
} else {
    echo "ADMIN_EMAIL/ADMIN_PASSWORD kosong — admin tidak dibuat.\n";
}

// 4. Symlink storage untuk upload (ephemeral di paket free).
$link = $root.'/public/storage';
if (! file_exists($link) && ! is_link($link)) {
    @symlink($root.'/storage/app/public', $link);
    echo "Storage link dibuat.\n";
}

echo "Bootstrap render selesai.\n";
