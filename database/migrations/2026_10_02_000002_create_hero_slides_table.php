<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DDL only — no data migration (issue #18 checklist 1b).
     *
     * Slide hero Beranda per-desa. Maksimal 5 aktif tampil di Beranda
     * (dibatasi saat query, bukan DB constraint, agar admin tetap bisa
     * menyiapkan draf nonaktif). Urutan tayang: `urutan` lalu `id`.
     * Kosong → section slider disembunyikan total.
     */
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained()->cascadeOnDelete();
            $table->string('gambar_path')->nullable();
            $table->string('judul');
            $table->string('subjudul')->nullable();
            $table->string('tautan_label')->nullable();
            $table->string('tautan_url')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['desa_id', 'aktif', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
