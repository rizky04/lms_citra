<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Sekolah;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Str::singular() bikin resource "kelas"/"kuis" jadi param {kela}/{kui}, dan model
// binding gagal DIAM-DIAM (model kosong disuntik, bukan 404). Test ini mengunci
// bahwa halaman detail benar-benar memuat model dari DB.
class RouteBindingTest extends TestCase
{
    use RefreshDatabase;

    private function guru(): User
    {
        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $guru = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $guru->assignRole('guru');

        return $guru;
    }

    public function test_halaman_detail_kelas_memuat_model(): void
    {
        $guru = $this->guru();
        $this->actingAs($guru);

        $kelas = Kelas::create([
            'nama' => 'XI RPL 1',
            'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'wali_guru_id' => $guru->id,
        ]);

        $this->get(route('kelas.show', $kelas))
            ->assertOk()
            ->assertSee('XI RPL 1')
            ->assertSee($kelas->kode_undangan);
    }

    public function test_halaman_kelola_kuis_memuat_model(): void
    {
        $guru = $this->guru();
        $this->actingAs($guru);

        $jenjang = Jenjang::where('nama', 'SMK')->first();
        $kelas = Kelas::create(['nama' => 'XII TKJ', 'jenjang_id' => $jenjang->id, 'wali_guru_id' => $guru->id]);
        $mapel = Mapel::create(['jenjang_id' => $jenjang->id, 'nama' => 'Informatika']);

        $kuis = Kuis::create([
            'kelas_id' => $kelas->id, 'guru_id' => $guru->id,
            'judul' => 'UH Jaringan', 'max_percobaan' => 1,
        ]);

        Soal::create([
            'mapel_id' => $mapel->id, 'jenjang_id' => $jenjang->id, 'guru_id' => $guru->id,
            'tipe' => 'pg', 'pertanyaan' => 'Apa itu TCP?', 'opsi_json' => ['A' => 'x', 'B' => 'y'],
            'jawaban_benar' => 'A', 'bobot' => 1, 'status' => 'published',
        ]);

        // Halaman ini yang dulu 500 karena $kuis->kelas null.
        $this->get(route('kuis.show', $kuis))
            ->assertOk()
            ->assertSee('UH Jaringan')
            ->assertSee('XII TKJ');

        $this->get(route('kuis.edit', $kuis))->assertOk()->assertSee('UH Jaringan');
    }

    public function test_halaman_edit_soal_memuat_model(): void
    {
        $guru = $this->guru();
        $this->actingAs($guru);

        $jenjang = Jenjang::where('nama', 'SMP')->first();
        $mapel = Mapel::create(['jenjang_id' => $jenjang->id, 'nama' => 'Informatika']);
        $soal = Soal::create([
            'mapel_id' => $mapel->id, 'jenjang_id' => $jenjang->id, 'guru_id' => $guru->id,
            'tipe' => 'esai', 'pertanyaan' => 'Jelaskan algoritma sorting.', 'bobot' => 5, 'status' => 'published',
        ]);

        $this->get(route('soal.edit', $soal))
            ->assertOk()
            ->assertSee('Jelaskan algoritma sorting.');
    }
}
