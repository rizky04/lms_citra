<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function guru(): User
    {
        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $u = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $u->assignRole('guru');

        return $u;
    }

    // Sisipkan notifikasi database mentah dengan url apa pun (meniru data lama).
    private function suntikNotif(User $user, ?string $url): string
    {
        $id = (string) Str::uuid();
        $user->notifications()->create([
            'id' => $id,
            'type' => 'App\\Notifications\\HasilAiSiap',
            'data' => ['tipe' => 'ai', 'judul' => 'Materi hasil AI', 'pesan' => 'x', 'url' => $url, 'sukses' => true],
            'read_at' => null,
        ]);

        return $id;
    }

    public function test_klik_notifikasi_url_lokalhost_lama_diarahkan_ke_path_benar(): void
    {
        $guru = $this->guru();
        // Notifikasi lama dari queue saat APP_URL=localhost
        $id = $this->suntikNotif($guru, 'http://localhost/ai');

        $res = $this->actingAs($guru)->get(route('notifikasi.baca', $id));

        // Harus redirect ke /ai (path saja), BUKAN ke localhost
        $res->assertRedirect('/ai');
    }

    public function test_klik_notifikasi_url_relatif_baru(): void
    {
        $guru = $this->guru();
        $id = $this->suntikNotif($guru, '/ai');

        $this->actingAs($guru)->get(route('notifikasi.baca', $id))->assertRedirect('/ai');
    }

    public function test_notifikasi_tanpa_url_ke_daftar(): void
    {
        $guru = $this->guru();
        $id = $this->suntikNotif($guru, null);

        $this->actingAs($guru)->get(route('notifikasi.baca', $id))
            ->assertRedirect(route('notifikasi.index'));
    }

    public function test_klik_menandai_terbaca(): void
    {
        $guru = $this->guru();
        $id = $this->suntikNotif($guru, '/ai');

        $this->actingAs($guru)->get(route('notifikasi.baca', $id));

        $this->assertNotNull($guru->notifications()->find($id)->read_at);
    }

    public function test_tidak_bisa_baca_notifikasi_orang_lain(): void
    {
        $a = $this->guru();
        $b = User::create([
            'sekolah_id' => $a->sekolah_id, 'name' => 'B', 'email' => 'b@uji.test',
            'password' => Hash::make('x'), 'status' => 'active',
        ]);
        $b->assignRole('guru');

        $id = $this->suntikNotif($a, '/ai');

        $this->actingAs($b)->get(route('notifikasi.baca', $id))->assertNotFound();
    }
}
