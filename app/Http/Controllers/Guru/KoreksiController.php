<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JawabanSiswa;
use App\Models\Kuis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Koreksi manual jawaban esai/praktik pada kuis (PG sudah dinilai otomatis).
class KoreksiController extends Controller
{
    public function index(): View
    {
        // Kuis yang masih punya jawaban belum dinilai.
        // whereHas, bukan having() — HAVING tanpa agregat ditolak SQLite.
        $kuis = Kuis::with('kelas')
            ->withCount('jawabanBelumDinilai as perlu_koreksi_count')
            ->whereHas('jawabanBelumDinilai')
            ->latest()->get();

        return view('guru.koreksi.index', compact('kuis'));
    }

    public function show(Kuis $kuis): View
    {
        $jawaban = JawabanSiswa::with(['soal', 'siswa'])
            ->where('kuis_id', $kuis->id)
            ->whereNull('benar') // esai/praktik: PG selalu true/false
            ->orderBy('user_id')->get()
            ->groupBy('user_id');

        return view('guru.koreksi.show', compact('kuis', 'jawaban'));
    }

    public function nilai(Request $request, Kuis $kuis): RedirectResponse
    {
        $v = $request->validate([
            'nilai' => ['required', 'array'],
            'nilai.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tersimpan = 0;
        foreach ($v['nilai'] as $jawabanId => $nilai) {
            if ($nilai === null || $nilai === '') {
                continue;
            }

            $jawaban = JawabanSiswa::with('soal')->where('kuis_id', $kuis->id)->find($jawabanId);
            if (! $jawaban) {
                continue;
            }

            // Nilai tidak boleh melebihi bobot soal.
            $jawaban->update(['nilai' => min((float) $nilai, (float) $jawaban->soal->bobot)]);
            $tersimpan++;
        }

        return back()->with('status', "{$tersimpan} jawaban dinilai.");
    }
}
