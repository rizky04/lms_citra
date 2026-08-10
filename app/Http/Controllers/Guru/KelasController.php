<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        return view('guru.kelas.index', [
            'kelas' => Kelas::with('jenjang')->withCount('siswa')->latest()->get(),
            'jenjangList' => Jenjang::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenjang_id' => ['required', 'exists:jenjangs,id'],
        ]);

        Kelas::create([
            'nama' => $v['nama'],
            'jenjang_id' => $v['jenjang_id'],
            'wali_guru_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Kelas dibuat. Bagikan kode ke siswa untuk gabung.');
    }

    public function show(Kelas $kelas): View
    {
        $kelas->load(['jenjang', 'siswa']);

        return view('guru.kelas.show', compact('kelas'));
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $kelas->delete();

        return redirect()->route('kelas.index')->with('status', 'Kelas dihapus.');
    }
}
