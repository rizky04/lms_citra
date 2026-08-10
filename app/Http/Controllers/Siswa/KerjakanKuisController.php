<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\JawabanSiswa;
use App\Models\Kuis;
use App\Models\KuisPercobaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KerjakanKuisController extends Controller
{
    public function index(Request $request): View
    {
        $kelasIds = $request->user()->kelasDiikuti()->pluck('kelas.id');

        $kuis = Kuis::with('kelas')->withCount('soal')
            ->whereIn('kelas_id', $kelasIds)
            ->where('status', 'published')
            ->latest()->get();

        return view('siswa.kuis.index', compact('kuis'));
    }

    public function show(Request $request, Kuis $kuis): View|RedirectResponse
    {
        $this->pastikanBoleh($request, $kuis);

        $percobaanTerpakai = $this->percobaanTerpakai($request, $kuis);
        if ($percobaanTerpakai >= $kuis->max_percobaan) {
            return redirect()->route('kerjakan.hasil', $kuis);
        }

        $percobaanKe = $percobaanTerpakai + 1;
        // Jam mulai dicatat sekali di sini dan tidak berubah lagi — inilah yang
        // menegakkan durasi_menit; tanpanya siswa bisa biarkan tab terbuka tanpa batas.
        $attempt = $this->ambilAtauBuatPercobaan($request, $kuis, $percobaanKe);

        if ($attempt->sudahKadaluarsa()) {
            if (! $this->sudahMenjawab($kuis, $request->user()->id, $percobaanKe)) {
                $this->simpanJawaban($kuis, $request->user()->id, $percobaanKe, []);
            }

            return redirect()->route('kerjakan.hasil', $kuis)
                ->with('status', 'Waktu pengerjaan sudah habis. Jawaban dikirim otomatis apa adanya.');
        }

        $soal = $kuis->soal;
        if ($kuis->acak_soal) {
            $soal = $soal->shuffle();
        }

        return view('siswa.kuis.kerjakan', [
            'kuis' => $kuis,
            'soal' => $soal,
            'sisaDetik' => $attempt->sisaDetik(), // null = tanpa batas waktu
        ]);
    }

    public function submit(Request $request, Kuis $kuis): RedirectResponse
    {
        $this->pastikanBoleh($request, $kuis);

        $percobaanTerpakai = $this->percobaanTerpakai($request, $kuis);
        $percobaanKe = $percobaanTerpakai + 1;

        if ($percobaanKe > $kuis->max_percobaan) {
            return redirect()->route('kerjakan.hasil', $kuis)
                ->withErrors(['kuis' => 'Kesempatan mengerjakan sudah habis.']);
        }

        // Bisa saja sudah tersimpan lewat auto-forfeit (GET setelah waktu habis)
        // sebelum request submit ini sempat tiba — jangan simpan dobel.
        if ($this->sudahMenjawab($kuis, $request->user()->id, $percobaanKe)) {
            return redirect()->route('kerjakan.hasil', $kuis)
                ->with('status', 'Jawaban untuk percobaan ini sudah tersimpan sebelumnya.');
        }

        $attempt = $this->ambilAtauBuatPercobaan($request, $kuis, $percobaanKe);

        $jawabanInput = $request->input('jawaban', []); // [soal_id => jawaban]
        $pesan = 'Jawaban terkirim.';

        if ($attempt->sudahKadaluarsa()) {
            // Waktu sudah lewat toleransi saat jawaban tiba di server — bukan cuma
            // telat kirim karena jaringan. Jawaban yang dikirim tidak dipakai.
            $jawabanInput = [];
            $pesan = 'Waktu pengerjaan sudah habis saat jawaban dikirim, sehingga tidak tersimpan.';
        }

        $this->simpanJawaban($kuis, $request->user()->id, $percobaanKe, $jawabanInput);

        return redirect()->route('kerjakan.hasil', $kuis)->with('status', $pesan);
    }

    public function hasil(Request $request, Kuis $kuis): View
    {
        $this->pastikanBoleh($request, $kuis);

        $jawaban = JawabanSiswa::with('soal')
            ->where('kuis_id', $kuis->id)
            ->where('user_id', $request->user()->id)
            ->orderByDesc('percobaan')->get();

        $percobaanTerakhir = $jawaban->max('percobaan') ?? 0;
        $set = $jawaban->where('percobaan', $percobaanTerakhir);

        $totalBobot = $set->sum(fn ($j) => $j->soal->bobot);
        $nilaiPg = $set->sum('nilai');
        $adaManual = $set->contains(fn ($j) => $j->benar === null);

        return view('siswa.kuis.hasil', compact('kuis', 'set', 'totalBobot', 'nilaiPg', 'adaManual', 'percobaanTerakhir'));
    }

    // --- helpers ---

    // Siswa hanya boleh akses kuis published di kelas yang dia ikuti (+ sekolah via global scope).
    private function pastikanBoleh(Request $request, Kuis $kuis): void
    {
        $terdaftar = $request->user()->kelasDiikuti()->where('kelas.id', $kuis->kelas_id)->exists();
        abort_unless($terdaftar && $kuis->status === 'published', 403);
    }

    private function percobaanTerpakai(Request $request, Kuis $kuis): int
    {
        return (int) JawabanSiswa::where('kuis_id', $kuis->id)
            ->where('user_id', $request->user()->id)
            ->max('percobaan');
    }

    // Idempotent: dipanggil berkali-kali (tiap reload halaman) mengembalikan
    // baris yang sama, jam mulai tidak pernah bergeser.
    private function ambilAtauBuatPercobaan(Request $request, Kuis $kuis, int $percobaanKe): KuisPercobaan
    {
        return KuisPercobaan::firstOrCreate(
            ['kuis_id' => $kuis->id, 'user_id' => $request->user()->id, 'percobaan' => $percobaanKe],
            ['mulai_at' => now()]
        );
    }

    private function sudahMenjawab(Kuis $kuis, int $userId, int $percobaanKe): bool
    {
        return JawabanSiswa::where('kuis_id', $kuis->id)
            ->where('user_id', $userId)
            ->where('percobaan', $percobaanKe)
            ->exists();
    }

    private function simpanJawaban(Kuis $kuis, int $userId, int $percobaanKe, array $jawabanInput): void
    {
        DB::transaction(function () use ($kuis, $userId, $percobaanKe, $jawabanInput) {
            foreach ($kuis->soal as $soal) {
                $jwb = $jawabanInput[$soal->id] ?? null;

                // Auto-grade hanya PG; esai/praktik menunggu koreksi guru.
                $benar = null;
                $nilai = null;
                if ($soal->tipe === 'pg') {
                    $benar = $jwb !== null && $jwb === $soal->jawaban_benar;
                    $nilai = $benar ? $soal->bobot : 0;
                }

                JawabanSiswa::create([
                    'kuis_id' => $kuis->id,
                    'soal_id' => $soal->id,
                    'user_id' => $userId,
                    'percobaan' => $percobaanKe,
                    'jawaban' => is_array($jwb) ? json_encode($jwb) : $jwb,
                    'benar' => $benar,
                    'nilai' => $nilai,
                ]);
            }
        });
    }
}
