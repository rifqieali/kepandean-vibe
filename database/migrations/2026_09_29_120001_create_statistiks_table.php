<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statistiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained()->cascadeOnDelete();
            $table->string('kunci');
            $table->string('nilai');
            $table->timestamps();

            $table->unique(['desa_id', 'kunci']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statistiks');
    }
};
