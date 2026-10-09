<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DDL only — no data migration (issue #15 checklist 3).
     *
     * OpenSID parity: SEBELUM 0 baris (tabel belum ada, tidak ada dump
     * aparatur di repo/PRD); SESUDAH 5 baris contoh via PejabatSeeder
     * untuk Kepandean. Contoh BUKAN data asli — lihat catatan parity di
     * PejabatSeeder; penggantian dengan data asli menunggu konfirmasi
     * perangkat desa via halaman admin. Index (desa_id, kelompok, urutan)
     * mendukung scoping per-desa + default sort halaman publik.
     *
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pejabats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('kelompok'); // pimpinan | perangkat | wilayah
            $table->string('wilayah_label')->nullable();
            $table->string('foto_path')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['desa_id', 'kelompok', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pejabats');
    }
};
