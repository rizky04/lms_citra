<?php

use App\Models\Jenjang;
use App\Support\Role as R;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

// Role dan jenjang adalah data baku (tidak dikelola user), jadi dibuat lewat
// migration supaya selalu ada di dev/test/prod tanpa perlu menjalankan seeder.
return new class extends Migration
{
    public function up(): void
    {
        foreach (R::SEMUA as $nama) {
            Role::findOrCreate($nama, 'web');
        }

        foreach (['SD', 'SMP', 'SMA', 'SMK'] as $nama) {
            Jenjang::firstOrCreate(['nama' => $nama]);
        }
    }

    public function down(): void
    {
        Role::whereIn('name', R::SEMUA)->delete();
        Jenjang::whereIn('nama', ['SD', 'SMP', 'SMA', 'SMK'])->delete();
    }
};
