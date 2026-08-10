<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Master data global (SD/SMP/SMA/SMK) — sama untuk semua sekolah, tanpa sekolah_id.
    public function up(): void
    {
        Schema::create('jenjangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // SD, SMP, SMA, SMK
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenjangs');
    }
};
