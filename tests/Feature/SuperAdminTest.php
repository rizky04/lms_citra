<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $u = User::create([
            'name' => 'Super', 'email' => 'super@lms.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $u->assignRole('super_admin');

        return $u;
    }

    private function guru(string $email = 'g@uji.test', string $role = 'admin_sekolah'): User
    {
        $sekolah = Sekolah::firstOrCreate(['nama' => 'SMK Uji']);
        $u = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Pak Guru', 'email' => $email,
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $u->assignRole($role);

        return $u;
    }

    // --- Akses ---

    public function test_hanya_super_admin_yang_bisa_buka_menu_platform(): void
    {
        $this->actingAs($this->guru())->get(route('superadmin.pengguna.index'))->assertForbidden();
        $this->actingAs($this->guru('g2@uji.test', 'guru'))->get(route('superadmin.master.index'))->assertForbidden();

        $super = $this->superAdmin();
        $this->actingAs($super)->get(route('superadmin.pengguna.index'))->assertOk();
        $this->actingAs($super)->get(route('superadmin.master.index'))->assertOk();
    }

    // --- Manajemen peran ---

    public function test_super_admin_mengubah_peran_user(): void
    {
        $super = $this->superAdmin();
        $guru = $this->guru('naik@uji.test', 'guru');

        $this->actingAs($super)->put(route('superadmin.pengguna.peran', $guru), ['role' => 'admin_sekolah'])
            ->assertRedirect();

        $this->assertTrue($guru->fresh()->hasRole('admin_sekolah'));
        $this->assertFalse($guru->fresh()->hasRole('guru'));
    }

    public function test_naik_ke_super_admin_melepas_sekolah(): void
    {
        $super = $this->superAdmin();
        $guru = $this->guru('promote@uji.test', 'guru');

        $this->actingAs($super)->put(route('superadmin.pengguna.peran', $guru), ['role' => 'super_admin']);

        $guru->refresh();
        $this->assertTrue($guru->hasRole('super_admin'));
        $this->assertNull($guru->sekolah_id);
    }

    public function test_tidak_bisa_ubah_akun_sendiri(): void
    {
        $super = $this->superAdmin();

        $this->actingAs($super)->put(route('superadmin.pengguna.peran', $super), ['role' => 'guru'])
            ->assertForbidden();
        $this->actingAs($super)->delete(route('superadmin.pengguna.destroy', $super))->assertForbidden();
    }

    public function test_suspend_dan_hapus_user(): void
    {
        $super = $this->superAdmin();
        $guru = $this->guru('sus@uji.test', 'guru');

        $this->actingAs($super)->post(route('superadmin.pengguna.toggle', $guru));
        $this->assertSame('suspended', $guru->fresh()->status);

        $this->actingAs($super)->delete(route('superadmin.pengguna.destroy', $guru));
        $this->assertNull(User::find($guru->id));
    }

    // --- Impersonation ---

    public function test_masuk_sebagai_dan_keluar(): void
    {
        $super = $this->superAdmin();
        $admin = $this->guru('adm@uji.test', 'admin_sekolah');

        // Masuk sebagai admin sekolah → jadi user itu, bisa buka menu sekolah
        $this->actingAs($super)->post(route('superadmin.masuk-sebagai', $admin))
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->assertSame($super->id, session('impersonator_id'));

        // Saat impersonasi bisa buka menu guru
        $this->get(route('kelas.index'))->assertOk();

        // Keluar → kembali jadi super admin
        $this->post(route('impersonasi.keluar'))->assertRedirect(route('superadmin.pengguna.index'));
        $this->assertAuthenticatedAs($super);
        $this->assertNull(session('impersonator_id'));
    }

    public function test_tidak_bisa_masuk_sebagai_super_admin_lain(): void
    {
        $super = $this->superAdmin();
        $super2 = User::create([
            'name' => 'Super2', 'email' => 'super2@lms.test',
            'password' => Hash::make('x'), 'status' => 'active',
        ]);
        $super2->assignRole('super_admin');

        $this->actingAs($super)->post(route('superadmin.masuk-sebagai', $super2))->assertForbidden();
    }

    public function test_guru_biasa_tak_bisa_impersonasi(): void
    {
        $guru = $this->guru('g@uji.test', 'guru');
        $target = $this->guru('t@uji.test', 'guru');

        $this->actingAs($guru)->post(route('superadmin.masuk-sebagai', $target))->assertForbidden();
    }

    // --- Master data ---

    public function test_tambah_dan_ubah_jenjang(): void
    {
        $super = $this->superAdmin();

        $this->actingAs($super)->post(route('superadmin.master.jenjang.store'), ['nama' => 'MA'])->assertRedirect();
        $this->assertDatabaseHas('jenjangs', ['nama' => 'MA']);

        $ma = Jenjang::where('nama', 'MA')->first();
        $this->actingAs($super)->put(route('superadmin.master.jenjang.update', $ma), ['nama' => 'Madrasah Aliyah']);
        $this->assertSame('Madrasah Aliyah', $ma->fresh()->nama);
    }

    public function test_jenjang_terpakai_tidak_bisa_dihapus(): void
    {
        $super = $this->superAdmin();
        $smp = Jenjang::where('nama', 'SMP')->first();
        $sekolah = Sekolah::create(['nama' => 'S']);
        Mapel::create(['sekolah_id' => $sekolah->id, 'jenjang_id' => $smp->id, 'nama' => 'Informatika']);

        $this->actingAs($super)->from(route('superadmin.master.index'))
            ->delete(route('superadmin.master.jenjang.destroy', $smp))
            ->assertSessionHasErrors('jenjang');

        $this->assertNotNull(Jenjang::find($smp->id));
    }
}
