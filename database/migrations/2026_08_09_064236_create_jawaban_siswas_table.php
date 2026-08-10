<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // siswa
            $table->unsignedInteger('percobaan')->default(1);
            $table->text('jawaban')->nullable();
            $table->boolean('benar')->nullable(); // null = belum dinilai (esai/praktik)
            $table->decimal('nilai', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['kuis_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswas');
    }
};
