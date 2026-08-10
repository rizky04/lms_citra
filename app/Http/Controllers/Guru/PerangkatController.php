<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\PerangkatPembelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PerangkatController extends Controller
{
    public function index(Request $request): View
    {
        $perangkat = PerangkatPembelajaran::with(['mapel', 'jenjang'])
            ->when($request->jenis, fn ($q, $j) => $q->where('jenis', $j))
            ->latest()->paginate(12)->withQueryString();

        return view('guru.perangkat.index', [
            'perangkat' => $perangkat,
            'jenisList' => PerangkatPembelajaran::JENIS,
        ]);
    }

    public function create(): View
    {
        return view('guru.perangkat.form', $this->formData(
            new PerangkatPembelajaran([
                'jenis' => 'modul_ajar',
                'status' => 'draft',
                'tahun_ajaran' => date('Y').'/'.(date('Y') + 1),
                'semester' => 'ganjil',
            ])
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $p = PerangkatPembelajaran::create($this->validated($request) + ['sumber' => 'manual']);

        return redirect()->route('perangkat.show', $p)->with('status', 'Dokumen dibuat.');
    }

    public function show(PerangkatPembelajaran $perangkat): View
    {
        $perangkat->load(['mapel', 'jenjang', 'guru']);

        return view('guru.perangkat.show', compact('perangkat'));
    }

    public function edit(PerangkatPembelajaran $perangkat): View
    {
        return view('guru.perangkat.form', $this->formData($perangkat));
    }

    public function update(Request $request, PerangkatPembelajaran $perangkat): RedirectResponse
    {
        $perangkat->update($this->validated($request));

        return redirect()->route('perangkat.show', $perangkat)->with('status', 'Dokumen diperbarui.');
    }

    public function destroy(PerangkatPembelajaran $perangkat): RedirectResponse
    {
        $perangkat->delete();

        return redirect()->route('perangkat.index')->with('status', 'Dokumen dihapus.');
    }

    // Cetak/unduh PDF berkop untuk dikumpulkan ke kepala sekolah.
    public function pdf(PerangkatPembelajaran $perangkat): Response
    {
        $perangkat->load(['mapel', 'jenjang', 'guru', 'sekolah']);

        $pdf = Pdf::loadView('pdf.perangkat', compact('perangkat'))->setPaper('a4');

        $nama = Str::slug($perangkat->jenis.'-'.$perangkat->judul).'.pdf';

        return $pdf->download($nama);
    }

    private function formData(PerangkatPembelajaran $perangkat): array
    {
        return [
            'perangkat' => $perangkat,
            'jenisList' => PerangkatPembelajaran::JENIS,
            'jenjangList' => Jenjang::orderBy('id')->get(),
            'mapelList' => Mapel::orderBy('nama')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'jenis' => ['required', 'in:'.implode(',', array_keys(PerangkatPembelajaran::JENIS))],
            'judul' => ['required', 'string', 'max:255'],
            'jenjang_id' => ['required', 'exists:jenjangs,id'],
            'mapel_nama' => ['required', 'string', 'max:255'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'in:ganjil,genap'],
            'konten' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $mapel = Mapel::firstOrCreate([
            'jenjang_id' => $v['jenjang_id'],
            'nama' => trim($v['mapel_nama']),
        ]);

        return [
            'guru_id' => $request->user()->id,
            'mapel_id' => $mapel->id,
            'jenjang_id' => $v['jenjang_id'],
            'jenis' => $v['jenis'],
            'judul' => $v['judul'],
            'tahun_ajaran' => $v['tahun_ajaran'] ?? null,
            'semester' => $v['semester'] ?? null,
            'konten' => $v['konten'] ?? null,
            'status' => $v['status'],
        ];
        // 'sumber' sengaja tidak diikutkan: diisi saat create, dan pada update
        // nilainya dipertahankan supaya jejak "ai_generated" tidak hilang saat guru merevisi.
    }
}
