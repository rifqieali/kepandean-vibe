<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DDL only — no data migration (issue #16 checklist 4).
     *
     * OpenSID parity: SEBELUM 0 baris (tabel belum ada, tidak ada dump
     * kategori di repo/PRD); SESUDAH 0 baris (tidak ada seeder — contoh
     * artikel/kategori TIDAK dikarang; migrasi asli + konfirmasi perangkat
     * desa menunggu akses OpenSID, via halaman admin).
     *
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['desa_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};
