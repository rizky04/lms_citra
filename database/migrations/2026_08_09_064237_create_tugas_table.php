<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->longText('instruksi')->nullable();
            $table->string('file_path')->nullable(); // lampiran instruksi
            $table->timestamp('deadline')->nullable();
            $table->timestamps();

            $table->index(['sekolah_id', 'kelas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
