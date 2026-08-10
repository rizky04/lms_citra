<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\Sekolah;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MenuTambahanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(Sekolah $sekolah): User
    {
        $u = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Admin', 'email' => 'a'.uniqid().'@uji.test',
            'password' => Hash::make('x'), 'status' => 'active',
        ]);
        $u->assignRole('admin_sekolah');

        return $u;
    }

    // --- MAPEL ---

    public function test_mapel_duplikat_ditolak(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $admin = $this->admin($sekolah);
        $jenjang = Jenjang::where('nama', 'SMK')->first();

        Mapel::create(['sekolah_id' => $sekolah->id, 'jenjang_id' => $jenjang->id, 'nama' => 'Informatika']);

        $this->actingAs($admin)->from(route('mapel.index'))->post(route('mapel.store'), [
            'nama' => 'Informatika', 'jenjang_id' => $jenjang->id,
        ])->assertSessionHasErrors('nama');

        $this->assertSame(1, Mapel::count());
    }

    public function test_mapel_sama_beda_jenjang_boleh(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $admin = $this->admin($sekolah);

        Mapel::create([
            'sekolah_id' => $sekolah->id,
            'jenjang_id' => Jenjang::where('nama', 'SMP')->first()->id, 'nama' => 'Informatika',
        ]);

        $this->actingAs($admin)->post(route('mapel.store'), [
            'nama' => 'Informatika', 'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
        ])->assertRedirect();

        $this->assertSame(2, Mapel::count());
    }

    public function test_mapel_yang_dipakai_tidak_bisa_dihapus(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $admin = $this->admin($sekolah);
        $jenjang = Jenjang::where('nama', 'SMK')->first();
        $mapel = Mapel::create(['sekolah_id' => $sekolah->id, 'jenjang_id' => $jenjang->id, 'nama' => 'Informatika']);

        Soal::create([
            'sekolah_id' => $sekolah->id, 'mapel_id' => $mapel->id, 'jenjang_id' => $jenjang->id,
            'guru_id' => $admin->id, 'tipe' => 'esai', 'pertanyaan' => 'x', 'bobot' => 1, 'status' => 'draft',
        ]);

        $this->actingAs($admin)->from(route('mapel.index'))
            ->delete(route('mapel.destroy', $mapel))->assertSessionHasErrors('hapus');

        $this->assertSame(1, Mapel::count());
    }

    public function test_mapel_kosong_bisa_dihapus_dan_rename(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $admin = $this->admin($sekolah);
        $jenjang = Jenjang::where('nama', 'SMK')->first();
        $mapel = Mapel::create(['sekolah_id' => $sekolah->id, 'jenjang_id' => $jenjang->id, 'nama' => 'Informatka']);

        // rename typo
        $this->actingAs($admin)->put(route('mapel.update', $mapel), [
            'nama' => 'Informatika', 'jenjang_id' => $jenjang->id,
        ])->assertRedirect();
        $this->assertSame('Informatika', $mapel->fresh()->nama);

        // hapus
        $this->actingAs($admin)->delete(route('mapel.destroy', $mapel))->assertRedirect();
        $this->assertSame(0, Mapel::count());
    }

    // --- PENGATURAN SEKOLAH ---

    public function test_admin_ubah_nama_dan_apikey_sekolah(): void
    {
        $sekolah = Sekolah::create(['nama' => 'Nama Lama']);
        $admin = $this->admin($sekolah);

        $this->actingAs($admin)->put(route('admin.sekolah.update'), ['nama' => 'Nama Baru'])->assertRedirect();
        $this->assertSame('Nama Baru', $sekolah->fresh()->nama);

        $this->actingAs($admin)->put(route('admin.sekolah.apikey'), ['gemini_api_key' => 'AIzaKEYSEKOLAH'])->assertRedirect();
        $this->assertSame('AIzaKEYSEKOLAH', $sekolah->fresh()->gemini_api_key);

        // hapus key → kembali null
        $this->actingAs($admin)->put(route('admin.sekolah.apikey'), ['hapus_key' => '1'])->assertRedirect();
        $this->assertNull($sekolah->fresh()->gemini_api_key);
    }

    // --- SUPER ADMIN ---

    public function test_super_admin_suspend_memblokir_login_user(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $guru = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $guru->assignRole('guru');

        $super = User::create(['name' => 'Super', 'email' => 'sa@uji.test',
            'password' => Hash::make('x'), 'status' => 'active']);
        $super->assignRole('super_admin');

        $this->actingAs($super)->post(route('superadmin.sekolah.toggle', $sekolah))->assertRedirect();

        $this->assertSame('suspended', $sekolah->fresh()->status);
        $this->assertSame('suspended', $guru->fresh()->status);

        // Guru tak bisa login saat sekolah suspended (logout dulu: /login pakai middleware guest)
        auth()->logout();
        $this->post('/login', ['email' => 'g@uji.test', 'password' => 'password123'])
            ->assertSessionHasErrors('email');

        // Aktifkan lagi
        $this->actingAs($super)->post(route('superadmin.sekolah.toggle', $sekolah->fresh()));
        $this->assertSame('active', $guru->fresh()->status);
    }

    public function test_suspend_tidak_mengaktifkan_guru_pending(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $pending = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Pending', 'email' => 'p@uji.test',
            'password' => Hash::make('x'), 'status' => 'pending',
        ]);
        $pending->assignRole('guru');

        $super = User::create(['name' => 'Super', 'email' => 'sa@uji.test',
            'password' => Hash::make('x'), 'status' => 'active']);
        $super->assignRole('super_admin');

        // suspend lalu aktifkan
        $this->actingAs($super)->post(route('superadmin.sekolah.toggle', $sekolah));
        $this->actingAs($super)->post(route('superadmin.sekolah.toggle', $sekolah->fresh()));

        // Guru pending harus TETAP pending, tidak ikut ter-aktifkan
        $this->assertSame('pending', $pending->fresh()->status);
    }

    // --- RAPOR SISWA ---

    public function test_rapor_siswa_bisa_dibuka(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $siswa = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Siswa', 'email' => 's@uji.test',
            'password' => Hash::make('x'), 'status' => 'active',
        ]);
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)->get(route('rapor.index'))->assertOk()->assertSee('Rapor Saya');
    }
}
