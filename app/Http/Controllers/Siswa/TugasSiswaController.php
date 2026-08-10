<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SubmisiTugas;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TugasSiswaController extends Controller
{
    public function index(Request $request): View
    {
        $kelasIds = $request->user()->kelasDiikuti()->pluck('kelas.id');

        $tugas = Tugas::with('kelas')->whereIn('kelas_id', $kelasIds)
            ->orderByRaw('deadline is null, deadline asc')
            ->paginate(12);

        $submisiSaya = SubmisiTugas::where('user_id', $request->user()->id)
            ->whereIn('tugas_id', $tugas->pluck('id'))->get()->keyBy('tugas_id');

        return view('siswa.tugas.index', compact('tugas', 'submisiSaya'));
    }

    public function show(Request $request, Tugas $tugas): View
    {
        $this->pastikanBoleh($request, $tugas);

        $submisi = SubmisiTugas::where('tugas_id', $tugas->id)
            ->where('user_id', $request->user()->id)->first();

        return view('siswa.tugas.show', compact('tugas', 'submisi'));
    }

    public function submit(Request $request, Tugas $tugas): RedirectResponse
    {
        $this->pastikanBoleh($request, $tugas);

        $v = $request->validate([
            'isi' => ['nullable', 'string', 'max:20000'],
            'berkas' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,txt'],
        ]);

        if (blank($v['isi'] ?? null) && ! $request->hasFile('berkas')) {
            return back()->withErrors(['isi' => 'Isi jawaban atau unggah berkas minimal salah satu.']);
        }

        $submisi = SubmisiTugas::firstOrNew([
            'tugas_id' => $tugas->id,
            'user_id' => $request->user()->id,
        ]);

        // Sudah dinilai guru → tidak boleh diubah lagi.
        abort_if($submisi->exists && $submisi->nilai !== null, 403, 'Tugas sudah dinilai, tidak bisa diubah.');

        if ($request->hasFile('berkas')) {
            if ($submisi->file_path) {
                Storage::disk('public')->delete($submisi->file_path);
            }
            $submisi->file_path = $request->file('berkas')->store('submisi', 'public');
        }

        $submisi->sekolah_id = $tugas->sekolah_id;
        $submisi->isi = $v['isi'] ?? null;
        $submisi->submitted_at = now();
        $submisi->save();

        return redirect()->route('tugas.saya.show', $tugas)->with('status', 'Tugas dikumpulkan.');
    }

    private function pastikanBoleh(Request $request, Tugas $tugas): void
    {
        $terdaftar = $request->user()->kelasDiikuti()->where('kelas.id', $tugas->kelas_id)->exists();
        abort_unless($terdaftar, 403);
    }
}
