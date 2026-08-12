<?php

namespace Tests\Feature;

use App\Models\AiGenerationJob;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AiStatusTest extends TestCase
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

    public function test_endpoint_status_menampilkan_riwayat_terbaru(): void
    {
        $guru = $this->guru();

        AiGenerationJob::create([
            'sekolah_id' => $guru->sekolah_id, 'guru_id' => $guru->id, 'jenis' => 'materi',
            'status' => 'processing', 'request_json' => ['topik' => 'Algoritma', 'jenjang' => 'SMK'],
        ]);

        $res = $this->actingAs($guru)->get(route('ai.status'));

        $res->assertOk()
            ->assertSee('Algoritma')
            ->assertSee('processing');
    }

    public function test_status_ganti_ke_done_terlihat_di_endpoint(): void
    {
        $guru = $this->guru();

        $job = AiGenerationJob::create([
            'sekolah_id' => $guru->sekolah_id, 'guru_id' => $guru->id, 'jenis' => 'materi',
            'status' => 'queued', 'request_json' => ['topik' => 'Jaringan', 'jenjang' => 'SMK'],
        ]);

        // Simulasi worker selesai
        $job->update(['status' => 'done', 'hasil_json' => ['jenis' => 'materi', 'dibuat' => 1]]);

        $this->actingAs($guru)->get(route('ai.status'))
            ->assertOk()
            ->assertSee('done')
            ->assertSee('Review materi');
    }

    public function test_siswa_tak_bisa_akses_status(): void
    {
        $sekolah = Sekolah::create(['nama' => 'S']);
        $siswa = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Siswa', 'email' => 's@uji.test',
            'password' => Hash::make('x'), 'status' => 'active',
        ]);
        $siswa->assignRole('siswa');

        $this->actingAs($siswa)->get(route('ai.status'))->assertForbidden();
    }
}
