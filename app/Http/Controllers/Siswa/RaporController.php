<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\JawabanSiswa;
use App\Models\Kuis;
use App\Models\SubmisiTugas;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Rekap semua nilai milik siswa sendiri (kuis + tugas).
class RaporController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $kelasIds = $request->user()->kelasDiikuti()->pluck('kelas.id');

        // --- KUIS: nilai per percobaan terbaik? Ambil percobaan terakhir tiap kuis. ---
        $jawaban = JawabanSiswa::with('soal')
            ->where('user_id', $userId)
            ->whereHas('kuis', fn ($q) => $q->whereIn('kelas_id', $kelasIds))
            ->get()
            ->groupBy('kuis_id');

        $kuisList = Kuis::with('kelas')->whereIn('id', $jawaban->keys())->get()->keyBy('id');

        $nilaiKuis = $jawaban->map(function ($grup, $kuisId) use ($kuisList) {
            $percobaanTerakhir = $grup->max('percobaan');
            $set = $grup->where('percobaan', $percobaanTerakhir);

            $totalBobot = $set->sum(fn ($j) => $j->soal->bobot);
            $poin = $set->sum('nilai');                    // esai belum dinilai = null → 0
            $adaManual = $set->contains(fn ($j) => $j->benar === null && $j->nilai === null);

            return [
                'kuis' => $kuisList->get($kuisId),
                'poin' => $poin,
                'total' => $totalBobot,
                'persen' => $totalBobot > 0 ? round($poin / $totalBobot * 100) : null,
                'menunggu' => $adaManual,
            ];
        })->filter(fn ($r) => $r['kuis'])->sortByDesc(fn ($r) => optional($r['kuis'])->created_at)->values();

        // --- TUGAS ---
        $nilaiTugas = SubmisiTugas::with('tugas.kelas')
            ->where('user_id', $userId)
            ->whereHas('tugas', fn ($q) => $q->whereIn('kelas_id', $kelasIds))
            ->latest()->get();

        // --- Ringkasan ---
        $persenKuis = $nilaiKuis->whereNotNull('persen')->pluck('persen');
        $nilaiTugasDinilai = $nilaiTugas->whereNotNull('nilai')->pluck('nilai');

        $ringkas = [
            'rataKuis' => $persenKuis->count() ? round($persenKuis->avg()) : null,
            'rataTugas' => $nilaiTugasDinilai->count() ? round($nilaiTugasDinilai->avg()) : null,
            'jumlahKuis' => $nilaiKuis->count(),
            'jumlahTugas' => $nilaiTugas->count(),
        ];

        return view('siswa.rapor.index', compact('nilaiKuis', 'nilaiTugas', 'ringkas'));
    }
}
