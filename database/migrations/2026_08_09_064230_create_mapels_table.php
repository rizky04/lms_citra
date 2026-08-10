<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('jenjang_id')->constrained('jenjangs')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();

            $table->index(['sekolah_id', 'jenjang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapels');
    }
};
