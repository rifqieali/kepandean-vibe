<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pindahkan data lama lebih dulu agar tak ada nilai yatim.
        DB::table('users')->where('role', 'dinas')->update(['role' => 'techade']);

        // MySQL menegakkan daftar ENUM; driver lain (sqlite/pgsql test) cukup data-nya.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('techade', 'admin_desa', 'editor') NOT NULL DEFAULT 'editor'");
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'techade')->update(['role' => 'dinas']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('dinas', 'admin_desa', 'editor') NOT NULL DEFAULT 'editor'");
        }
    }
};
