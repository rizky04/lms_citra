<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis'); // soal/materi/perangkat
            $table->json('request_json'); // parameter form generate
            $table->string('status')->default('queued'); // queued/processing/done/failed
            $table->json('hasil_json')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['sekolah_id', 'guru_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_jobs');
    }
};
