<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateKontenAi;
use App\Models\AiGenerationJob;
use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PerangkatPembelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function index(Request $request): View
    {
        return view('guru.ai.index', [
            'riwayat' => AiGenerationJob::with('guru')->latest()->limit(20)->get(),
            'jenjangList' => Jenjang::orderBy('id')->get(),
            'mapelList' => Mapel::orderBy('nama')->get(),
            'kelasList' => Kelas::orderBy('nama')->get(),
            'jenisPerangkat' => PerangkatPembelajaran::JENIS,
            'sisaKuota' => $this->sisaKuota($request),
            'apiSiap' => filled(config('services.gemini.key')),
            // Job mengendap >2 menit = pertanda `queue:work` tidak jalan.
            'workerMacet' => AiGenerationJob::where('status', 'queued')
                ->where('created_at', '<', now()->subMinutes(2))->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'jenis' => ['required', 'in:soal,materi,perangkat'],
            'jenjang_id' => ['required', 'exists:jenjangs,id'],
            'mapel_nama' => ['required', 'string', 'max:255'],
            'topik' => ['required', 'string', 'max:255'],
            // khusus soal
            'jumlah' => ['required_if:jenis,soal', 'nullable', 'integer', 'min:1', 'max:30'],
            'tipe' => ['required_if:jenis,soal', 'nullable', 'in:pg,esai,praktik'],
            'tingkat' => ['nullable', 'in:mudah,sedang,sulit'],
            'bobot' => ['nullable', 'integer', 'min:1'],
            // khusus materi
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'sertakan_gambar' => ['nullable', 'boolean'],
            // khusus perangkat
            'jenis_perangkat' => ['required_if:jenis,perangkat', 'nullable', 'in:'.implode(',', array_keys(PerangkatPembelajaran::JENIS))],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'in:ganjil,genap'],
        ]);

        if (blank(config('services.gemini.key'))) {
            return back()->withErrors(['jenis' => 'GEMINI_API_KEY belum diisi di file .env. Isi dulu lalu coba lagi.']);
        }

        if ($this->sisaKuota($request) <= 0) {
            return back()->withErrors(['jenis' => 'Kuota generate AI sekolah hari ini sudah habis. Coba lagi besok.']);
        }

        $jenjang = Jenjang::findOrFail($v['jenjang_id']);
        $mapel = Mapel::firstOrCreate([
            'jenjang_id' => $jenjang->id,
            'nama' => trim($v['mapel_nama']),
        ]);

        $job = AiGenerationJob::create([
            'guru_id' => $request->user()->id,
            'jenis' => $v['jenis'],
            'status' => 'queued',
            'request_json' => [
                'jenjang' => $jenjang->nama,
                'jenjang_id' => $jenjang->id,
                'mapel' => $mapel->nama,
                'mapel_id' => $mapel->id,
                'kelas_id' => $v['kelas_id'] ?? null,
                'sertakan_gambar' => $request->boolean('sertakan_gambar'),
                'topik' => $v['topik'],
                'jumlah' => $v['jumlah'] ?? null,
                'tipe' => $v['tipe'] ?? null,
                'tingkat' => $v['tingkat'] ?? 'sedang',
                'bobot' => $v['bobot'] ?? 1,
                'jenis' => $v['jenis_perangkat'] ?? null,
                'tahun_ajaran' => $v['tahun_ajaran'] ?? null,
                'semester' => $v['semester'] ?? null,
            ],
        ]);

        GenerateKontenAi::dispatch($job->id);

        return redirect()->route('ai.index')
            ->with('status', 'Permintaan dikirim. Hasil muncul di sini setelah selesai diproses.');
    }

    // Sisa kuota generate hari ini untuk sekolah ini.
    private function sisaKuota(Request $request): int
    {
        $batas = (int) config('services.gemini.daily_limit', 50);

        $terpakai = AiGenerationJob::whereDate('created_at', today())
            ->whereIn('status', ['queued', 'processing', 'done'])
            ->count();

        return max(0, $batas - $terpakai);
    }
}
