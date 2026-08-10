<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Soal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KuisController extends Controller
{
    public function index(): View
    {
        $kuis = Kuis::with('kelas')->withCount('soal')->latest()->paginate(15);

        return view('guru.kuis.index', compact('kuis'));
    }

    public function create(): View
    {
        return view('guru.kuis.form', [
            'kuis' => new Kuis(['max_percobaan' => 1]),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $kuis = Kuis::create($this->validated($request));

        return redirect()->route('kuis.show', $kuis)->with('status', 'Kuis dibuat. Tambahkan soal.');
    }

    // Halaman kelola: atur soal + publish.
    public function show(Kuis $kuis): View
    {
        $kuis->load(['kelas', 'soal']);
        $terpasang = $kuis->soal->pluck('id');

        // Bank soal yang belum dipasang (jenjang mengikuti kelas)
        $bank = Soal::where('jenjang_id', $kuis->kelas->jenjang_id)
            ->where('status', 'published')
            ->whereNotIn('id', $terpasang)
            ->latest()->limit(50)->get();

        return view('guru.kuis.show', compact('kuis', 'bank'));
    }

    public function edit(Kuis $kuis): View
    {
        return view('guru.kuis.form', [
            'kuis' => $kuis,
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Kuis $kuis): RedirectResponse
    {
        $kuis->update($this->validated($request));

        return redirect()->route('kuis.show', $kuis)->with('status', 'Kuis diperbarui.');
    }

    public function destroy(Kuis $kuis): RedirectResponse
    {
        $kuis->delete();

        return redirect()->route('kuis.index')->with('status', 'Kuis dihapus.');
    }

    // Tambah soal: manual (ids) atau acak N by tag.
    public function tambahSoal(Request $request, Kuis $kuis): RedirectResponse
    {
        $request->validate([
            'mode' => ['required', 'in:manual,acak'],
            'soal_ids' => ['required_if:mode,manual', 'array'],
            'soal_ids.*' => ['exists:soals,id'],
            'jumlah' => ['required_if:mode,acak', 'nullable', 'integer', 'min:1'],
            'tag' => ['nullable', 'string'],
        ]);

        if ($request->mode === 'manual') {
            $ids = $request->soal_ids;
        } else {
            $ids = Soal::where('jenjang_id', $kuis->kelas->jenjang_id)
                ->where('status', 'published')
                ->when($request->tag, fn ($q, $t) => $q->where('tag', 'like', "%{$t}%"))
                ->whereNotIn('id', $kuis->soal()->pluck('soals.id'))
                ->inRandomOrder()->limit((int) $request->jumlah)->pluck('id');
        }

        // syncWithoutDetaching supaya tak dobel; urutan lanjut dari yang ada.
        $mulai = $kuis->soal()->count();
        $payload = [];
        foreach ($ids as $i => $id) {
            $payload[$id] = ['urutan' => $mulai + $i];
        }
        $kuis->soal()->syncWithoutDetaching($payload);

        return back()->with('status', count($ids).' soal ditambahkan.');
    }

    public function hapusSoal(Kuis $kuis, Soal $soal): RedirectResponse
    {
        $kuis->soal()->detach($soal->id);

        return back()->with('status', 'Soal dilepas dari kuis.');
    }

    public function publish(Kuis $kuis): RedirectResponse
    {
        if ($kuis->soal()->count() === 0) {
            return back()->withErrors(['publish' => 'Tambahkan minimal 1 soal sebelum publish.']);
        }

        $kuis->update(['status' => 'published']);

        return back()->with('status', 'Kuis dipublish. Siswa di kelas ini bisa mengerjakan.');
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'judul' => ['required', 'string', 'max:255'],
            'durasi_menit' => ['nullable', 'integer', 'min:1'],
            'max_percobaan' => ['required', 'integer', 'min:1'],
            'acak_soal' => ['nullable', 'boolean'],
            'mulai_at' => ['nullable', 'date'],
            'selesai_at' => ['nullable', 'date', 'after_or_equal:mulai_at'],
        ]);

        $v['guru_id'] = $request->user()->id;
        $v['acak_soal'] = $request->boolean('acak_soal');

        return $v;
    }
}
