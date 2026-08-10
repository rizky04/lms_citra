<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisPercobaan;
use App\Models\Mapel;
use App\Models\Sekolah;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DurasiKuisTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;

    private Kuis $kuis;

    private Soal $soal;

    private function setupKuis(int $durasiMenit): void
    {
        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $jenjang = Jenjang::where('nama', 'SMK')->first();

        $guru = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('x'), 'status' => 'active',
        ]);
        $guru->assignRole('guru');

        $this->siswa = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Siswa', 'email' => 's@uji.test',
            'password' => Hash::make('x'), 'status' => 'active',
        ]);
        $this->siswa->assignRole('siswa');

        $kelas = Kelas::create(['sekolah_id' => $sekolah->id, 'jenjang_id' => $jenjang->id,
            'nama' => 'XI', 'wali_guru_id' => $guru->id]);
        $kelas->siswa()->attach($this->siswa->id);

        $mapel = Mapel::create(['sekolah_id' => $sekolah->id, 'jenjang_id' => $jenjang->id, 'nama' => 'Informatika']);

        $this->soal = Soal::create([
            'sekolah_id' => $sekolah->id, 'mapel_id' => $mapel->id, 'jenjang_id' => $jenjang->id,
            'guru_id' => $guru->id, 'tipe' => 'pg', 'pertanyaan' => '1+1?',
            'opsi_json' => ['A' => '1', 'B' => '2'], 'jawaban_benar' => 'B',
            'bobot' => 2, 'status' => 'published',
        ]);

        $this->kuis = Kuis::create([
            'sekolah_id' => $sekolah->id, 'kelas_id' => $kelas->id, 'guru_id' => $guru->id,
            'judul' => 'UH', 'durasi_menit' => $durasiMenit, 'max_percobaan' => 1, 'status' => 'published',
        ]);
        $this->kuis->soal()->attach($this->soal->id, ['urutan' => 0]);
    }

    public function test_membuka_kuis_mencatat_jam_mulai(): void
    {
        $this->setupKuis(60);

        $this->actingAs($this->siswa)->get(route('kerjakan.show', $this->kuis))->assertOk();

        $this->assertDatabaseHas('kuis_percobaans', [
            'kuis_id' => $this->kuis->id, 'user_id' => $this->siswa->id, 'percobaan' => 1,
        ]);
    }

    public function test_reload_tidak_menggeser_jam_mulai(): void
    {
        $this->setupKuis(60);

        $this->actingAs($this->siswa)->get(route('kerjakan.show', $this->kuis));
        $mulai1 = KuisPercobaan::first()->mulai_at;

        $this->travel(5)->minutes();
        $this->actingAs($this->siswa)->get(route('kerjakan.show', $this->kuis));

        $this->assertSame(1, KuisPercobaan::count());
        $this->assertEquals($mulai1, KuisPercobaan::first()->fresh()->mulai_at);
    }

    public function test_submit_sebelum_waktu_habis_diterima(): void
    {
        $this->setupKuis(60);

        $this->actingAs($this->siswa)->get(route('kerjakan.show', $this->kuis));
        $this->actingAs($this->siswa)->post(route('kerjakan.submit', $this->kuis), [
            'jawaban' => [$this->soal->id => 'B'],
        ])->assertRedirect(route('kerjakan.hasil', $this->kuis));

        $this->assertDatabaseHas('jawaban_siswas', [
            'soal_id' => $this->soal->id, 'benar' => true, 'nilai' => 2,
        ]);
    }

    public function test_submit_setelah_waktu_habis_ditolak_jawabannya(): void
    {
        $this->setupKuis(30);

        $this->actingAs($this->siswa)->get(route('kerjakan.show', $this->kuis));

        // Lewat 31 menit — melewati durasi + toleransi.
        $this->travel(31)->minutes();

        $this->actingAs($this->siswa)->post(route('kerjakan.submit', $this->kuis), [
            'jawaban' => [$this->soal->id => 'B'], // jawaban benar, tapi telat
        ])->assertRedirect(route('kerjakan.hasil', $this->kuis));

        // Jawaban tersimpan tapi KOSONG (tidak dihitung benar) karena kadaluarsa.
        $this->assertDatabaseHas('jawaban_siswas', [
            'soal_id' => $this->soal->id, 'user_id' => $this->siswa->id,
            'jawaban' => null, 'nilai' => 0,
        ]);
    }

    public function test_membuka_kuis_yang_waktunya_sudah_habis_auto_forfeit(): void
    {
        $this->setupKuis(30);

        // Percobaan dimulai 40 menit lalu tapi tak pernah submit.
        KuisPercobaan::create([
            'sekolah_id' => $this->kuis->sekolah_id, 'kuis_id' => $this->kuis->id,
            'user_id' => $this->siswa->id, 'percobaan' => 1, 'mulai_at' => now()->subMinutes(40),
        ]);

        $this->actingAs($this->siswa)->get(route('kerjakan.show', $this->kuis))
            ->assertRedirect(route('kerjakan.hasil', $this->kuis));

        // Nilai 0, jawaban kosong tersimpan otomatis.
        $this->assertDatabaseHas('jawaban_siswas', [
            'soal_id' => $this->soal->id, 'user_id' => $this->siswa->id, 'nilai' => 0,
        ]);
    }

    public function test_kuis_tanpa_durasi_tidak_kadaluarsa(): void
    {
        $this->setupKuis(0); // 0 = tanpa batas

        $this->actingAs($this->siswa)->get(route('kerjakan.show', $this->kuis));
        $this->travel(10)->hours();

        $this->actingAs($this->siswa)->post(route('kerjakan.submit', $this->kuis), [
            'jawaban' => [$this->soal->id => 'B'],
        ]);

        $this->assertDatabaseHas('jawaban_siswas', ['benar' => true, 'nilai' => 2]);
    }
}
