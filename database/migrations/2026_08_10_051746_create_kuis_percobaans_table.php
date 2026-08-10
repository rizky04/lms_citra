<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sumber kebenaran "kapan siswa mulai mengerjakan" — dibuat saat siswa PERTAMA
 * kali membuka halaman kerjakan, dipakai untuk menegakkan durasi_menit di server.
 * Tanpa ini, batas waktu kuis hanya kosmetik (siswa bisa biarkan tab terbuka
 * berjam-jam lalu submit, tidak ada yang mencegah).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis_percobaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('percobaan');
            $table->timestamp('mulai_at');
            $table->timestamps();

            $table->unique(['kuis_id', 'user_id', 'percobaan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuis_percobaans');
    }
};
