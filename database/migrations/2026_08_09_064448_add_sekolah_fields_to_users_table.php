<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // nullable: super admin platform tidak terikat satu sekolah
            $table->foreignId('sekolah_id')->nullable()->after('id')->constrained('sekolahs')->nullOnDelete();
            // Role dikelola spatie/laravel-permission (tabel roles + model_has_roles).
            $table->string('status')->default('active')->after('email'); // pending/active (approval guru)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropColumn(['sekolah_id', 'status']);
        });
    }
};
