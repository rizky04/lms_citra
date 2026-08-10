<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Semua halaman utama harus render (bukan 500) untuk tiap role, termasuk saat kosong.
class HalamanSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function buat(string $role): User
    {
        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $u = User::create([
            'sekolah_id' => $sekolah->id, 'name' => ucfirst($role),
            'email' => $role.'@uji.test', 'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $u->assignRole($role);

        return $u;
    }

    public function test_halaman_guru_render(): void
    {
        $guru = $this->buat('guru');
        $this->actingAs($guru);

        foreach ([
            'dashboard', 'kelas.index', 'materi.index', 'materi.create',
            'soal.index', 'soal.create', 'soal.io', 'kuis.index', 'kuis.create',
            'tugas.index', 'tugas.create', 'koreksi.index',
            'perangkat.index', 'perangkat.create', 'ai.index', 'laporan.index',
            'mapel.index', 'notifikasi.index', 'profile.edit',
        ] as $rute) {
            $this->get(route($rute))->assertOk();
        }
    }

    public function test_halaman_siswa_render(): void
    {
        $siswa = $this->buat('siswa');
        $this->actingAs($siswa);

        foreach ([
            'dashboard', 'materi.baca.index', 'kerjakan.index',
            'tugas.saya.index', 'rapor.index', 'notifikasi.index', 'profile.edit',
        ] as $rute) {
            $this->get(route($rute))->assertOk();
        }
    }

    public function test_halaman_admin_sekolah_render(): void
    {
        $admin = $this->buat('admin_sekolah');
        $this->actingAs($admin);

        $this->get(route('admin.user.index'))->assertOk();
        $this->get(route('admin.sekolah.edit'))->assertOk();
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_super_admin_render(): void
    {
        $super = User::create([
            'name' => 'Super', 'email' => 'sa@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $super->assignRole('super_admin');

        $this->actingAs($super)->get(route('dashboard'))->assertOk();
        $this->actingAs($super)->get(route('superadmin.sekolah.index'))->assertOk();
    }

    public function test_guru_tidak_bisa_akses_super_admin(): void
    {
        $this->actingAs($this->buat('guru'))
            ->get(route('superadmin.sekolah.index'))->assertForbidden();
    }

    public function test_guru_biasa_tidak_bisa_kelola_pengguna(): void
    {
        $this->actingAs($this->buat('guru'))
            ->get(route('admin.user.index'))->assertForbidden();
    }

    public function test_siswa_tidak_bisa_buka_halaman_guru(): void
    {
        $siswa = $this->buat('siswa');

        foreach (['materi.index', 'tugas.index', 'koreksi.index'] as $rute) {
            $this->actingAs($siswa)->get(route($rute))->assertForbidden();
        }
    }

    public function test_kelas_kosong_tetap_render(): void
    {
        $guru = $this->buat('guru');
        $this->actingAs($guru);

        $kelas = Kelas::create([
            'nama' => 'X TKJ', 'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'wali_guru_id' => $guru->id,
        ]);

        $this->get(route('kelas.show', $kelas))->assertOk()->assertSee('X TKJ');
    }
}
