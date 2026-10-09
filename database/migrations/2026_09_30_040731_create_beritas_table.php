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
     * artikel di repo/PRD); SESUDAH 0 baris (tidak ada seeder — contoh
     * artikel TIDAK dikarang; migrasi asli + konfirmasi perangkat desa
     * menunggu akses OpenSID, via halaman admin).
     * Unique [desa_id, slug]: slug sama beda desa boleh, satu desa ditolak.
     * Index [desa_id, status, published_at]: listing publik per-desa.
     *
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beritas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->string('judul');
            $table->string('slug');
            $table->text('isi');
            $table->string('cover_path')->nullable();
            $table->string('status')->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['desa_id', 'slug']);
            $table->index(['desa_id', 'status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beritas');
    }
};
