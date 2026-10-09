<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pindahkan data lama lebih dulu agar tak ada nilai yatim.
        DB::table('users')->where('role', 'techade')->update(['role' => 'developer']);
        DB::table('users')->where('email', 'admin@techade.dev')->update(['email' => 'admin@developer.dev']);

        // Samakan password akun sistem bawaan menjadi 'password'.
        foreach (['admin@developer.dev', 'admin@kepandean.id', 'editor@kepandean.id'] as $email) {
            DB::table('users')->where('email', $email)->update(['password' => Hash::make('password')]);
        }

        $this->gantiDaftarRole(['developer', 'admin_desa', 'editor']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'developer')->update(['role' => 'techade']);
        DB::table('users')->where('email', 'admin@developer.dev')->update(['email' => 'admin@techade.dev']);

        $this->gantiDaftarRole(['techade', 'admin_desa', 'editor']);
    }

    /**
     * MySQL menegakkan daftar ENUM lewat ALTER; sqlite/pgsql menempelkannya
     * sebagai CHECK saat kolom dibuat, jadi kolom dibangun ulang lewat
     * kolom sementara (data disalin, tak ada baris hilang).
     */
    private function gantiDaftarRole(array $daftar): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $enum = "'".implode("', '", $daftar)."'";
            DB::statement("ALTER TABLE users MODIFY role ENUM({$enum}) NOT NULL DEFAULT 'editor'");

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role_baru')->nullable();
        });
        DB::table('users')->update(['role_baru' => DB::raw('role')]);
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::table('users', function (Blueprint $table) use ($daftar) {
            $table->enum('role', $daftar)->default('editor');
        });
        DB::table('users')->update(['role' => DB::raw('role_baru')]);
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_baru');
        });
    }
};
