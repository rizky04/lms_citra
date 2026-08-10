<?php

namespace App\Jobs;

use App\Exceptions\GeminiPermanentException;
use App\Models\AiGenerationJob;
use App\Models\Materi;
use App\Models\PerangkatPembelajaran;
use App\Models\Soal;
use App\Notifications\HasilAiSiap;
use App\Services\Gemini;
use App\Services\PromptBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Panggilan Gemini dijalankan di queue supaya request guru tidak menunggu
 * (generate 20 soal bisa puluhan detik). Hasil selalu masuk sebagai DRAFT —
 * guru wajib review sebelum siswa melihatnya.
 */
class GenerateKontenAi implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30];

    public int $timeout = 180;

    public function __construct(public int $jobId) {}

    public function handle(Gemini $gemini, PromptBuilder $builder): void
    {
        // Job berjalan tanpa user login → global scope tidak aktif, jadi
        // sekolah_id di bawah HARUS diisi eksplisit dari record job.
        $job = AiGenerationJob::withoutGlobalScopes()->findOrFail($this->jobId);

        $job->update(['status' => 'processing']);

        $p = $job->request_json;
        $gemini = new Gemini($job->sekolah->gemini_api_key ? $job->sekolah : null);

        [$prompt, $skema] = match ($job->jenis) {
            'soal' => $builder->soal($p),
            'materi' => $builder->materi($p),
            'perangkat' => $builder->perangkat($p),
        };

        try {
            $hasil = $gemini->jsonPrompt($prompt, $skema);
        } catch (GeminiPermanentException $e) {
            // Key salah / kuota nol: mengulang tidak akan menolong.
            // Tandai gagal sekarang juga supaya guru langsung lihat pesannya.
            $this->fail($e);

            return;
        }

        $ringkasan = DB::transaction(fn () => match ($job->jenis) {
            'soal' => $this->simpanSoal($job, $p, $hasil),
            'materi' => $this->simpanMateri($job, $p, $hasil),
            'perangkat' => $this->simpanPerangkat($job, $p, $hasil),
        });

        $job->update(['status' => 'done', 'hasil_json' => $ringkasan]);
        $job->guru->notify(new HasilAiSiap($job));
    }

    public function failed(Throwable $e): void
    {
        $job = AiGenerationJob::withoutGlobalScopes()->find($this->jobId);

        if ($job) {
            $job->update(['status' => 'failed', 'error' => $e->getMessage()]);
            $job->guru?->notify(new HasilAiSiap($job));
        }
    }

    private function simpanSoal(AiGenerationJob $job, array $p, array $hasil): array
    {
        $dibuat = 0;

        foreach ($hasil['soal'] ?? [] as $item) {
            if (blank($item['pertanyaan'] ?? null)) {
                continue;
            }

            $opsi = null;
            $kunci = null;

            if ($p['tipe'] === 'pg') {
                $opsi = array_filter([
                    'A' => $item['opsi_a'] ?? null,
                    'B' => $item['opsi_b'] ?? null,
                    'C' => $item['opsi_c'] ?? null,
                    'D' => $item['opsi_d'] ?? null,
                ]);
                $kunci = $item['jawaban_benar'] ?? null;

                // PG tanpa opsi lengkap / tanpa kunci valid tidak berguna — lewati.
                if (count($opsi) < 2 || ! in_array($kunci, array_keys($opsi), true)) {
                    continue;
                }
            }

            Soal::create([
                'sekolah_id' => $job->sekolah_id,
                'mapel_id' => $p['mapel_id'],
                'jenjang_id' => $p['jenjang_id'],
                'guru_id' => $job->guru_id,
                'tipe' => $p['tipe'],
                'pertanyaan' => trim($item['pertanyaan']),
                'opsi_json' => $opsi,
                'jawaban_benar' => $kunci,
                'bobot' => $p['bobot'] ?? 1,
                'tingkat' => $p['tingkat'] ?? null,
                'tag' => $p['topik'] ?? null,
                'status' => 'draft', // wajib direview guru
                'sumber' => 'ai_generated',
            ]);
            $dibuat++;
        }

        return ['jenis' => 'soal', 'dibuat' => $dibuat, 'diminta' => $p['jumlah']];
    }

    private function simpanMateri(AiGenerationJob $job, array $p, array $hasil): array
    {
        $materi = Materi::create([
            'sekolah_id' => $job->sekolah_id,
            'mapel_id' => $p['mapel_id'],
            'kelas_id' => $p['kelas_id'] ?? null,
            'guru_id' => $job->guru_id,
            'judul' => $hasil['judul'] ?? $p['topik'],
            'konten' => $hasil['konten'] ?? '',
            'status' => 'draft',
            'sumber' => 'ai_generated',
        ]);

        return ['jenis' => 'materi', 'dibuat' => 1, 'materi_id' => $materi->id];
    }

    private function simpanPerangkat(AiGenerationJob $job, array $p, array $hasil): array
    {
        $perangkat = PerangkatPembelajaran::create([
            'sekolah_id' => $job->sekolah_id,
            'guru_id' => $job->guru_id,
            'mapel_id' => $p['mapel_id'],
            'jenjang_id' => $p['jenjang_id'],
            'jenis' => $p['jenis'],
            'judul' => $hasil['judul'] ?? $p['topik'],
            'tahun_ajaran' => $p['tahun_ajaran'] ?? null,
            'semester' => $p['semester'] ?? null,
            'konten' => $hasil['konten'] ?? '',
            'status' => 'draft',
            'sumber' => 'ai_generated',
        ]);

        return ['jenis' => 'perangkat', 'dibuat' => 1, 'perangkat_id' => $perangkat->id];
    }
}
