<?php

namespace App\Services;

use App\Exceptions\GeminiPermanentException;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Http;
use RuntimeException;

// Klien tipis Gemini REST API. Sengaja tanpa SDK — satu endpoint saja.
class Gemini
{
    public function __construct(private ?Sekolah $sekolah = null) {}

    /**
     * Kirim prompt, minta balasan JSON terstruktur, kembalikan array hasil decode.
     *
     * @throws RuntimeException bila key kosong, API gagal, atau balasan bukan JSON valid.
     */
    public function jsonPrompt(string $prompt, array $skema): array
    {
        $key = $this->sekolah?->geminiApiKey() ?: config('services.gemini.key');

        if (blank($key)) {
            throw new GeminiPermanentException('GEMINI_API_KEY belum diisi di .env.');
        }

        $model = config('services.gemini.model', 'gemini-2.0-flash');

        $response = Http::timeout(120)
            ->retry(2, 2000, throw: false) // jaringan goyang: coba lagi 2x
            // Key lewat header, bukan query string: tidak ikut terekam di log/URL.
            ->withHeaders(['x-goog-api-key' => $key])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    // Structured output: Gemini dipaksa balas JSON sesuai skema,
                    // jadi tidak perlu parsing teks bebas yang rapuh.
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $skema,
                ],
            ]);

        if ($response->failed()) {
            $pesan = $response->json('error.message') ?? 'HTTP '.$response->status();
            $status = $response->status();

            // 400/401/403 = salah key / API belum aktif / permintaan tak valid.
            // 429 dengan "limit: 0" juga permanen: project ini memang tak punya jatah,
            // beda dengan 429 biasa yang cuma kena batas per menit.
            $kuotaNol = $status === 429 && str_contains($pesan, 'limit: 0');

            if (in_array($status, [400, 401, 403], true) || $kuotaNol) {
                throw new GeminiPermanentException($this->pesanRamah($status, $pesan, $kuotaNol));
            }

            throw new RuntimeException("Gemini API gagal (HTTP {$status}): {$pesan}");
        }

        $teks = $response->json('candidates.0.content.parts.0.text');

        if (blank($teks)) {
            throw new RuntimeException('Gemini tidak mengembalikan konten. Coba ulangi.');
        }

        $data = json_decode($teks, true);

        if (! is_array($data)) {
            throw new RuntimeException('Balasan Gemini bukan JSON valid.');
        }

        return $data;
    }

    // Terjemahkan error Google jadi langkah yang bisa dikerjakan guru/admin.
    private function pesanRamah(int $status, string $asli, bool $kuotaNol): string
    {
        if ($kuotaNol) {
            return 'Project Google di balik API key ini tidak punya jatah pemakaian (limit: 0). '
                .'Buat key baru di https://aistudio.google.com/apikey — key AI Studio diawali "AIza". '
                .'Kalau key dibuat lewat Google Cloud Console, aktifkan dulu "Generative Language API" '
                .'pada project tersebut.';
        }

        return match ($status) {
            // 400 bisa karena key salah ATAU bentuk permintaan/skema tidak valid — jangan tebak.
            400 => 'Permintaan ditolak Gemini (400). Detail: '.$asli,
            401, 403 => 'API key ditolak (HTTP '.$status.'). Pastikan key benar dan '
                .'"Generative Language API" sudah aktif di project-nya. Detail: '.$asli,
            default => 'Gemini menolak permintaan (HTTP '.$status.'): '.$asli,
        };
    }
}
