<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->longText('konten')->nullable();
            $table->string('file_path')->nullable(); // lampiran
            $table->unsignedInteger('urutan')->default(0);
            $table->string('status')->default('draft'); // draft/published
            $table->string('sumber')->default('manual'); // manual/ai_generated/import
            $table->timestamps();

            $table->index(['sekolah_id', 'mapel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materis');
    }
};
