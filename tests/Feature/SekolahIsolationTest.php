<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Jaminan inti multi-tenant: user sekolah A tidak boleh melihat data sekolah B.
class SekolahIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_hanya_melihat_data_sekolahnya(): void
    {
        $jenjang = Jenjang::create(['nama' => 'SMP']);

        $sekolahA = Sekolah::create(['nama' => 'Sekolah A']);
        $sekolahB = Sekolah::create(['nama' => 'Sekolah B']);

        $guruA = User::create([
            'sekolah_id' => $sekolahA->id, 'name' => 'Guru A',
            'email' => 'a@test.id', 'password' => 'x',
        ]);
        $guruA->assignRole('guru');
        $guruB = User::create([
            'sekolah_id' => $sekolahB->id, 'name' => 'Guru B',
            'email' => 'b@test.id', 'password' => 'x',
        ]);
        $guruB->assignRole('guru');

        // Guru A bikin mapel (sekolah_id keisi otomatis dari user login)
        $this->actingAs($guruA);
        Mapel::create(['jenjang_id' => $jenjang->id, 'nama' => 'Informatika A']);

        $this->actingAs($guruB);
        Mapel::create(['jenjang_id' => $jenjang->id, 'nama' => 'Informatika B']);

        // Guru B hanya lihat mapel sekolahnya
        $this->actingAs($guruB);
        $this->assertSame(['Informatika B'], Mapel::pluck('nama')->all());

        // Guru A hanya lihat mapel sekolahnya
        $this->actingAs($guruA);
        $this->assertSame(['Informatika A'], Mapel::pluck('nama')->all());

        // Guru A tak bisa akses mapel sekolah B lewat tebak ID
        $mapelB = Mapel::withoutGlobalScopes()->where('nama', 'Informatika B')->first();
        $this->assertNull(Mapel::find($mapelB->id));
    }

    public function test_super_admin_melihat_semua_sekolah(): void
    {
        $jenjang = Jenjang::create(['nama' => 'SMA']);
        $sekolahA = Sekolah::create(['nama' => 'A']);
        $sekolahB = Sekolah::create(['nama' => 'B']);

        // Tanpa auth: create tidak di-scope, sekolah_id diisi eksplisit.
        Mapel::create(['sekolah_id' => $sekolahA->id, 'jenjang_id' => $jenjang->id, 'nama' => 'X']);
        Mapel::create(['sekolah_id' => $sekolahB->id, 'jenjang_id' => $jenjang->id, 'nama' => 'Y']);

        $super = User::create([
            'name' => 'Super', 'email' => 's@test.id', 'password' => 'x',
            'status' => 'active',
        ]);
        $super->assignRole('super_admin');

        $this->actingAs($super);
        $this->assertCount(2, Mapel::all());
    }
}
