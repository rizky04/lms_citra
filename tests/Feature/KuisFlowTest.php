<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Sekolah;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KuisFlowTest extends TestCase
{
    use RefreshDatabase;

    private function setupSekolah(): array
    {
        $jenjang = Jenjang::create(['nama' => 'SMP']);
        $sekolah = Sekolah::create(['nama' => 'S']);
        $guru = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'G', 'email' => 'g@t.id',
            'password' => 'x', 'status' => 'active',
        ]);
        $guru->assignRole('guru');
        $siswa = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'S', 'email' => 's@t.id',
            'password' => 'x', 'status' => 'active',
        ]);
        $siswa->assignRole('siswa');

        return compact('jenjang', 'sekolah', 'guru', 'siswa');
    }

    public function test_guru_bikin_soal_kuis_siswa_kerjakan_auto_grade(): void
    {
        ['jenjang' => $jenjang, 'guru' => $guru, 'siswa' => $siswa] = $this->setupSekolah();

        // Guru bikin soal PG
        $this->actingAs($guru);
        $this->post('/soal', [
            'jenjang_id' => $jenjang->id, 'mapel_nama' => 'Informatika',
            'tipe' => 'pg', 'pertanyaan' => '1+1?', 'bobot' => 2, 'status' => 'published',
            'opsi' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'jawaban_benar' => 'B',
        ])->assertRedirect(route('soal.index'));

        $soal = Soal::first();
        $this->assertSame('B', $soal->jawaban_benar);

        // Guru bikin kelas + kuis
        $this->post('/kelas', ['nama' => '7A', 'jenjang_id' => $jenjang->id]);
        $kelas = Kelas::first();
        $kelas->siswa()->attach($siswa->id);

        $this->post('/kuis', ['judul' => 'UH1', 'kelas_id' => $kelas->id, 'max_percobaan' => 1]);
        $kuis = Kuis::first();

        $this->post("/kuis/{$kuis->id}/soal", ['mode' => 'manual', 'soal_ids' => [$soal->id]]);
        $this->post("/kuis/{$kuis->id}/publish")->assertRedirect();
        $this->assertSame('published', $kuis->fresh()->status);

        // Siswa kerjakan — jawab benar
        $this->actingAs($siswa);
        $this->get(route('kerjakan.show', $kuis))->assertOk();
        $this->post(route('kerjakan.submit', $kuis), [
            'jawaban' => [$soal->id => 'B'],
        ])->assertRedirect(route('kerjakan.hasil', $kuis));

        $this->assertDatabaseHas('jawaban_siswas', [
            'soal_id' => $soal->id, 'user_id' => $siswa->id, 'benar' => true, 'nilai' => 2,
        ]);
    }

    public function test_siswa_tak_bisa_kerjakan_kuis_kelas_yang_tak_diikuti(): void
    {
        ['jenjang' => $jenjang, 'guru' => $guru, 'siswa' => $siswa] = $this->setupSekolah();

        $this->actingAs($guru);
        $kelas = Kelas::create(['nama' => '8B', 'jenjang_id' => $jenjang->id, 'wali_guru_id' => $guru->id]);
        $kuis = Kuis::create([
            'kelas_id' => $kelas->id, 'guru_id' => $guru->id, 'judul' => 'X',
            'max_percobaan' => 1, 'status' => 'published',
        ]);

        // siswa TIDAK di-enroll ke kelas ini
        $this->actingAs($siswa);
        $this->get(route('kerjakan.show', $kuis))->assertForbidden();
    }
}
