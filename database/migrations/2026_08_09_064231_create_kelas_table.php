<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('jenjang_id')->constrained('jenjangs')->cascadeOnDelete();
            $table->foreignId('wali_guru_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama'); // "7A", "XI RPL 2"
            $table->string('kode_undangan')->unique(); // dibagikan ke siswa untuk join
            $table->timestamps();

            $table->index('sekolah_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
