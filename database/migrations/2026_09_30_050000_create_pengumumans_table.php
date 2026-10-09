<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DDL only — no data migration (pola issue #16 checklist 4).
     *
     * Meniru tabel beritas versi ringan: tanpa kategori_id, tanpa cover,
     * tambah expired_at opsional (pola pikir kadaluarsa issue #17).
     * Unique [desa_id, slug]: slug sama beda desa boleh, satu desa ditolak.
     * Index [desa_id, status, published_at]: listing publik per-desa.
     *
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->string('slug');
            $table->text('isi');
            $table->string('status')->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expired_at')->nullable();
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
        Schema::dropIfExists('pengumumans');
    }
};
