<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->foreignId('jenjang_id')->constrained('jenjangs')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipe'); // pg/esai/praktik
            $table->text('pertanyaan');
            $table->json('opsi_json')->nullable(); // untuk PG: {"A":"..","B":".."}
            $table->string('jawaban_benar')->nullable(); // untuk PG: "A"
            $table->unsignedInteger('bobot')->default(1);
            $table->string('tingkat')->nullable(); // mudah/sedang/sulit
            $table->string('tag')->nullable(); // bab/topik
            $table->string('status')->default('draft');
            $table->string('sumber')->default('manual');
            $table->timestamps();

            $table->index(['sekolah_id', 'mapel_id', 'jenjang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soals');
    }
};
