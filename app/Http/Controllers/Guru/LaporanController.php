<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JawabanSiswa;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\SubmisiTugas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $kelasList = Kelas::orderBy('nama')->get();
        $kelas = $request->kelas_id
            ? $kelasList->firstWhere('id', (int) $request->kelas_id)
            : $kelasList->first();

        if (! $kelas) {
            return view('guru.laporan.index', [
                'kelasList' => $kelasList, 'kelas' => null,
                'rekap' => collect(), 'kuisList' => collect(), 'soalSulit' => collect(),
            ]);
        }

        $kelas->load('siswa');
        $kuisList = Kuis::where('kelas_id', $kelas->id)->where('status', 'published')->orderBy('id')->get();

        // Nilai kuis per siswa: total poin / total bobot soal di kuis itu.
        $nilaiKuis = JawabanSiswa::whereIn('kuis_id', $kuisList->pluck('id'))
            ->selectRaw('user_id, kuis_id, SUM(COALESCE(nilai,0)) as poin')
            ->groupBy('user_id', 'kuis_id')->get()
            ->groupBy('user_id');

        $bobotKuis = $kuisList->mapWithKeys(fn (Kuis $k) => [$k->id => (int) $k->soal()->sum('bobot')]);

        // Rata-rata nilai tugas per siswa.
        $nilaiTugas = SubmisiTugas::whereHas('tugas', fn ($q) => $q->where('kelas_id', $kelas->id))
            ->whereNotNull('nilai')
            ->selectRaw('user_id, AVG(nilai) as rata')
            ->groupBy('user_id')->pluck('rata', 'user_id');

        $rekap = $kelas->siswa->map(function ($siswa) use ($nilaiKuis, $bobotKuis, $nilaiTugas, $kuisList) {
            $perKuis = [];
            $totalPersen = [];

            foreach ($kuisList as $k) {
                $poin = optional($nilaiKuis->get($siswa->id))->firstWhere('kuis_id', $k->id)?->poin;
                $bobot = $bobotKuis[$k->id] ?? 0;

                if ($poin === null || $bobot === 0) {
                    $perKuis[$k->id] = null;

                    continue;
                }

                $persen = round($poin / $bobot * 100);
                $perKuis[$k->id] = $persen;
                $totalPersen[] = $persen;
            }

            return [
                'siswa' => $siswa,
                'perKuis' => $perKuis,
                'rataKuis' => $totalPersen ? round(array_sum($totalPersen) / count($totalPersen)) : null,
                'rataTugas' => isset($nilaiTugas[$siswa->id]) ? round($nilaiTugas[$siswa->id]) : null,
            ];
        })->sortByDesc(fn ($r) => $r['rataKuis'] ?? -1)->values();

        // Soal yang paling sering dijawab salah di kelas ini.
        $soalSulit = JawabanSiswa::with('soal')
            ->whereIn('kuis_id', $kuisList->pluck('id'))
            ->where('benar', false)
            ->selectRaw('soal_id, COUNT(*) as salah')
            ->groupBy('soal_id')->orderByDesc('salah')->limit(8)->get()
            ->filter(fn ($j) => $j->soal !== null);

        return view('guru.laporan.index', compact('kelasList', 'kelas', 'rekap', 'kuisList', 'soalSulit'));
    }
}
