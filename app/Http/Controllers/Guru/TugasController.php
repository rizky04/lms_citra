<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\SubmisiTugas;
use App\Models\Tugas;
use App\Notifications\TugasBaru;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TugasController extends Controller
{
    public function index(): View
    {
        $tugas = Tugas::with('kelas')->withCount([
            'submisi',
            'submisi as belum_dinilai_count' => fn ($q) => $q->whereNull('nilai'),
        ])->latest()->paginate(12);

        return view('guru.tugas.index', compact('tugas'));
    }

    public function create(): View
    {
        return view('guru.tugas.form', [
            'tugas' => new Tugas,
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tugas = Tugas::create($this->validated($request));

        // Beri tahu siswa di kelas tersebut.
        Notification::send($tugas->kelas->siswa, new TugasBaru($tugas));

        return redirect()->route('tugas.show', $tugas)->with('status', 'Tugas dibuat & siswa diberi notifikasi.');
    }

    // Halaman kelola: daftar submisi siswa + status penilaian.
    public function show(Tugas $tugas): View
    {
        $tugas->load(['kelas.siswa']);

        $submisi = SubmisiTugas::with('siswa')->where('tugas_id', $tugas->id)->get()->keyBy('user_id');

        return view('guru.tugas.show', compact('tugas', 'submisi'));
    }

    public function edit(Tugas $tugas): View
    {
        return view('guru.tugas.form', [
            'tugas' => $tugas,
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        $data = $this->validated($request);

        if (isset($data['file_path']) && $tugas->file_path) {
            Storage::disk('public')->delete($tugas->file_path);
        }

        $tugas->update($data);

        return redirect()->route('tugas.show', $tugas)->with('status', 'Tugas diperbarui.');
    }

    public function destroy(Tugas $tugas): RedirectResponse
    {
        if ($tugas->file_path) {
            Storage::disk('public')->delete($tugas->file_path);
        }
        $tugas->delete();

        return redirect()->route('tugas.index')->with('status', 'Tugas dihapus.');
    }

    // Beri nilai + feedback pada satu submisi.
    public function nilai(Request $request, Tugas $tugas, SubmisiTugas $submisi): RedirectResponse
    {
        abort_unless($submisi->tugas_id === $tugas->id, 404);

        $v = $request->validate([
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submisi->update($v);

        return back()->with('status', "Nilai {$submisi->siswa->name} tersimpan.");
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'judul' => ['required', 'string', 'max:255'],
            'instruksi' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'lampiran' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip'],
        ]);

        $data = [
            'guru_id' => $request->user()->id,
            'kelas_id' => $v['kelas_id'],
            'judul' => $v['judul'],
            'instruksi' => $v['instruksi'] ?? null,
            'deadline' => $v['deadline'] ?? null,
        ];

        if ($request->hasFile('lampiran')) {
            $data['file_path'] = $request->file('lampiran')->store('tugas', 'public');
        }

        return $data;
    }
}
