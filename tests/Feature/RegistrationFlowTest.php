<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_buat_sekolah_jadi_admin_dan_login(): void
    {
        $res = $this->post('/register', [
            'name' => 'Bu Guru', 'email' => 'guru@test.id',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'peran' => 'guru', 'mode_sekolah' => 'buat', 'nama_sekolah' => 'SMKN 1',
        ]);

        $res->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $u = User::where('email', 'guru@test.id')->first();
        $this->assertTrue($u->hasRole('admin_sekolah'));
        $this->assertSame('active', $u->status);
        $this->assertNotNull($u->sekolah_id);
    }

    public function test_guru_gabung_sekolah_ber_admin_jadi_pending(): void
    {
        $sekolah = Sekolah::create(['nama' => 'SMP 2']);
        User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Admin', 'email' => 'adm@test.id',
            'password' => 'x', 'status' => 'active',
        ])->assignRole('admin_sekolah');

        $res = $this->post('/register', [
            'name' => 'Guru Baru', 'email' => 'baru@test.id',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'peran' => 'guru', 'mode_sekolah' => 'gabung', 'sekolah_id' => $sekolah->id,
        ]);

        $res->assertRedirect(route('login'));
        $this->assertGuest(); // pending tidak auto-login
        $this->assertSame('pending', User::where('email', 'baru@test.id')->first()->status);
    }

    public function test_siswa_daftar_pakai_kode_kelas(): void
    {
        $jenjang = Jenjang::create(['nama' => 'SMP']);
        $sekolah = Sekolah::create(['nama' => 'SMP 3']);
        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id, 'jenjang_id' => $jenjang->id,
            'nama' => '7A', 'kode_undangan' => 'ABCD1234',
        ]);

        $res = $this->post('/register', [
            'name' => 'Budi', 'email' => 'budi@test.id',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'peran' => 'siswa', 'kode_kelas' => 'abcd1234', // case-insensitive
        ]);

        $res->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $siswa = User::where('email', 'budi@test.id')->first();
        $this->assertTrue($siswa->hasRole('siswa'));
        $this->assertSame($sekolah->id, $siswa->sekolah_id);
        $this->assertTrue($kelas->siswa()->where('users.id', $siswa->id)->exists());
    }

    public function test_kode_kelas_salah_ditolak(): void
    {
        $res = $this->from('/register')->post('/register', [
            'name' => 'X', 'email' => 'x@test.id',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'peran' => 'siswa', 'kode_kelas' => 'NGACO999',
        ]);

        $res->assertSessionHasErrors('kode_kelas');
        $this->assertGuest();
    }
}
