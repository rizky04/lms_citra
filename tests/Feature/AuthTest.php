<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function buatGuru(string $status = 'active'): User
    {
        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $user = User::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Uji',
            'email' => 'guru@uji.test',
            'password' => Hash::make('password123'),
            'status' => $status,
        ]);
        $user->assignRole('guru');

        return $user;
    }

    public function test_halaman_login_dan_register_bisa_dibuka(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
        $this->get('/register')->assertOk()->assertSee('Buat akun');
    }

    public function test_user_aktif_bisa_login(): void
    {
        $user = $this->buatGuru();

        $this->post('/login', ['email' => 'guru@uji.test', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_salah_ditolak(): void
    {
        $this->buatGuru();

        $this->post('/login', ['email' => 'guru@uji.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_pending_tidak_bisa_login(): void
    {
        $this->buatGuru('pending');

        $this->post('/login', ['email' => 'guru@uji.test', 'password' => 'password123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $user = $this->buatGuru();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_siswa_tidak_bisa_akses_halaman_guru(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $siswa = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Siswa', 'email' => 's@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)->get('/soal')->assertForbidden();
        $this->actingAs($siswa)->get('/kuis')->assertForbidden();
    }
}
