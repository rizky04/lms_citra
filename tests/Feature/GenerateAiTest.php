<?php

namespace Tests\Feature;

use App\Jobs\GenerateKontenAi;
use App\Models\AiGenerationJob;
use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Sekolah;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

// Gemini di-fake — test tidak pernah memanggil API sungguhan.
class GenerateAiTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private Mapel $mapel;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'kunci-palsu-untuk-test']);

        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $this->guru = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $this->guru->assignRole('guru');

        $this->mapel = Mapel::create([
            'sekolah_id' => $sekolah->id,
            'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'nama' => 'Informatika',
        ]);
    }

    private function balasGemini(array $payload): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => json_encode($payload)]]]],
                ],
            ]),
        ]);
    }

    private function buatJob(string $jenis, array $extra = []): AiGenerationJob
    {
        return AiGenerationJob::create([
            'sekolah_id' => $this->guru->sekolah_id,
            'guru_id' => $this->guru->id,
            'jenis' => $jenis,
            'status' => 'queued',
            'request_json' => array_merge([
                'jenjang' => 'SMK', 'jenjang_id' => $this->mapel->jenjang_id,
                'mapel' => 'Informatika', 'mapel_id' => $this->mapel->id,
                'topik' => 'Perulangan', 'tingkat' => 'sedang', 'bobot' => 2,
            ], $extra),
        ]);
    }

    public function test_form_generate_mengantre_job(): void
    {
        Queue::fake();
        $this->actingAs($this->guru);

        $this->post(route('ai.store'), [
            'jenis' => 'soal', 'jenjang_id' => $this->mapel->jenjang_id,
            'mapel_nama' => 'Informatika', 'topik' => 'Perulangan',
            'jumlah' => 5, 'tipe' => 'pg', 'tingkat' => 'sedang', 'bobot' => 1,
        ])->assertRedirect(route('ai.index'));

        Queue::assertPushed(GenerateKontenAi::class);
        $this->assertSame('queued', AiGenerationJob::first()->status);
    }

    public function test_job_soal_menyimpan_hasil_sebagai_draft(): void
    {
        Notification::fake();

        $this->balasGemini(['soal' => [
            [
                'pertanyaan' => 'Perulangan yang dijamin jalan minimal sekali?',
                'opsi_a' => 'for', 'opsi_b' => 'while', 'opsi_c' => 'do-while', 'opsi_d' => 'foreach',
                'jawaban_benar' => 'C', 'pembahasan' => 'do-while mengecek di akhir.',
            ],
            // Kunci jawaban tidak ada di opsi → harus dilewati, bukan bikin soal rusak.
            [
                'pertanyaan' => 'Soal rusak', 'opsi_a' => 'x', 'opsi_b' => 'y',
                'jawaban_benar' => 'D', 'pembahasan' => '-',
            ],
        ]]);

        $job = $this->buatJob('soal', ['jumlah' => 2, 'tipe' => 'pg']);
        (new GenerateKontenAi($job->id))->handle(app(\App\Services\Gemini::class), app(\App\Services\PromptBuilder::class));

        $this->assertSame(1, Soal::withoutGlobalScopes()->count());

        $soal = Soal::withoutGlobalScopes()->first();
        $this->assertSame('draft', $soal->status);          // wajib direview guru
        $this->assertSame('ai_generated', $soal->sumber);
        $this->assertSame('C', $soal->jawaban_benar);
        $this->assertSame($this->guru->sekolah_id, $soal->sekolah_id);

        $job->refresh();
        $this->assertSame('done', $job->status);
        $this->assertSame(1, $job->hasil_json['dibuat']);

        Notification::assertSentTo($this->guru, \App\Notifications\HasilAiSiap::class);
    }

    public function test_job_materi_menyimpan_draft(): void
    {
        Notification::fake();
        $this->balasGemini(['judul' => 'Perulangan di Python', 'konten' => 'Isi materi panjang…']);

        $job = $this->buatJob('materi');
        (new GenerateKontenAi($job->id))->handle(app(\App\Services\Gemini::class), app(\App\Services\PromptBuilder::class));

        $materi = Materi::withoutGlobalScopes()->first();
        $this->assertSame('Perulangan di Python', $materi->judul);
        $this->assertSame('draft', $materi->status);
        $this->assertSame('ai_generated', $materi->sumber);
    }

    // Dulu key dikirim sebagai argumen ke-3 Http::post() yang DIABAIKAN diam-diam,
    // jadi Google membalas 403 "unregistered callers". Test ini mengunci key benar terkirim.
    public function test_api_key_ikut_terkirim_ke_gemini(): void
    {
        Notification::fake();
        $this->balasGemini(['judul' => 'Judul', 'konten' => 'Isi']);

        $job = $this->buatJob('materi');
        (new GenerateKontenAi($job->id))->handle(app(\App\Services\Gemini::class), app(\App\Services\PromptBuilder::class));

        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'kunci-palsu-untuk-test'));
    }

    public function test_error_permanen_tidak_diulang_dan_pesannya_jelas(): void
    {
        Notification::fake();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(
            ['error' => ['message' => 'Method doesn\'t allow unregistered callers']], 403
        )]);

        $job = $this->buatJob('materi');

        $this->expectException(\App\Exceptions\GeminiPermanentException::class);

        try {
            app(\App\Services\Gemini::class)->jsonPrompt('halo', ['type' => 'OBJECT']);
        } catch (\App\Exceptions\GeminiPermanentException $e) {
            $this->assertStringContainsString('API key ditolak', $e->getMessage());
            $this->assertStringContainsString('Generative Language API', $e->getMessage());
            throw $e;
        }
    }

    public function test_kuota_nol_dianggap_permanen(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(
            ['error' => ['message' => 'Quota exceeded ... limit: 0, model: gemini-2.0-flash']], 429
        )]);

        try {
            app(\App\Services\Gemini::class)->jsonPrompt('halo', ['type' => 'OBJECT']);
            $this->fail('Seharusnya melempar GeminiPermanentException.');
        } catch (\App\Exceptions\GeminiPermanentException $e) {
            $this->assertStringContainsString('tidak punya jatah pemakaian', $e->getMessage());
            $this->assertStringContainsString('aistudio.google.com/apikey', $e->getMessage());
        }
    }

    public function test_kegagalan_api_ditandai_failed(): void
    {
        Notification::fake();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Kuota habis']], 429)]);

        $job = $this->buatJob('materi');

        try {
            (new GenerateKontenAi($job->id))->handle(app(\App\Services\Gemini::class), app(\App\Services\PromptBuilder::class));
            $this->fail('Seharusnya melempar exception.');
        } catch (\RuntimeException $e) {
            (new GenerateKontenAi($job->id))->failed($e);
        }

        $job->refresh();
        $this->assertSame('failed', $job->status);
        $this->assertStringContainsString('Kuota habis', $job->error);
        $this->assertSame(0, Materi::withoutGlobalScopes()->count());
    }

    public function test_ditolak_bila_api_key_kosong(): void
    {
        config(['services.gemini.key' => '']);
        $this->actingAs($this->guru);

        $this->from(route('ai.index'))->post(route('ai.store'), [
            'jenis' => 'materi', 'jenjang_id' => $this->mapel->jenjang_id,
            'mapel_nama' => 'Informatika', 'topik' => 'Perulangan',
        ])->assertSessionHasErrors('jenis');

        $this->assertSame(0, AiGenerationJob::count());
    }
}
