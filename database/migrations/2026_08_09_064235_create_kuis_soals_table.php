<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Pivot kuis <-> soal. Ikut kuis (tenant-scoped lewat kuis), tak perlu sekolah_id sendiri.
    public function up(): void
    {
        Schema::create('kuis_soals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soals')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(0);

            $table->unique(['kuis_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuis_soals');
    }
};
