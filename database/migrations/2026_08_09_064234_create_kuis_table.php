<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->unsignedInteger('durasi_menit')->nullable();
            $table->boolean('acak_soal')->default(false);
            $table->unsignedInteger('max_percobaan')->default(1);
            $table->timestamp('mulai_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->string('status')->default('draft'); // draft/published
            $table->timestamps();

            $table->index(['sekolah_id', 'kelas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuis');
    }
};
