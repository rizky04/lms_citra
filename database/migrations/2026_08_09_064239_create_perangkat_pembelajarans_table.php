<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perangkat_pembelajarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained('mapels')->nullOnDelete();
            $table->foreignId('jenjang_id')->nullable()->constrained('jenjangs')->nullOnDelete();
            $table->string('jenis'); // modul_ajar, prota, prosem, atp_silabus, kktp
            $table->string('judul');
            $table->string('tahun_ajaran')->nullable(); // "2025/2026"
            $table->string('semester')->nullable(); // ganjil/genap
            $table->longText('konten')->nullable();
            $table->string('file_path')->nullable(); // hasil import
            $table->string('status')->default('draft');
            $table->string('sumber')->default('manual'); // manual/ai_generated/import
            $table->timestamps();

            $table->index(['sekolah_id', 'guru_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat_pembelajarans');
    }
};
