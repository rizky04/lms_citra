<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MateriController extends Controller
{
    public function index(Request $request): View
    {
        $materi = Materi::with(['mapel.jenjang', 'kelas'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $t) => $q->where('judul', 'like', "%{$t}%"))
            ->orderBy('urutan')->latest()
            ->paginate(12)->withQueryString();

        return view('guru.materi.index', compact('materi'));
    }

    public function create(): View
    {
        return view('guru.materi.form', $this->formData(new Materi(['status' => 'draft', 'urutan' => 0])));
    }

    public function store(Request $request): RedirectResponse
    {
        $materi = Materi::create($this->validated($request));
        $this->simpanGambar($request, $materi);

        return redirect()->route('materi.index')->with('status', 'Materi ditambahkan.');
    }

    public function show(Materi $materi): View
    {
        $materi->load(['mapel.jenjang', 'kelas', 'guru']);

        return view('guru.materi.show', compact('materi'));
    }

    public function edit(Materi $materi): View
    {
        return view('guru.materi.form', $this->formData($materi));
    }

    public function update(Request $request, Materi $materi): RedirectResponse
    {
        $data = $this->validated($request);

        // Lampiran lama dihapus hanya kalau memang diganti.
        if (isset($data['file_path']) && $materi->file_path) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->update($data);
        $this->simpanGambar($request, $materi);

        return redirect()->route('materi.index')->with('status', 'Materi diperbarui.');
    }

    public function destroy(Materi $materi): RedirectResponse
    {
        if ($materi->file_path) {
            Storage::disk('public')->delete($materi->file_path);
        }
        $materi->delete();

        return redirect()->route('materi.index')->with('status', 'Materi dihapus.');
    }

    // Cetak materi jadi PDF (untuk dibagikan luring / diarsipkan).
    public function pdf(Materi $materi): Response
    {
        $materi->load(['mapel.jenjang', 'guru', 'sekolah']);

        return Pdf::loadView('pdf.materi', compact('materi'))
            ->setPaper('a4')
            ->download(Str::slug($materi->judul).'.pdf');
    }

    private function formData(Materi $materi): array
    {
        return [
            'materi' => $materi,
            'jenjangList' => Jenjang::orderBy('id')->get(),
            'mapelList' => Mapel::orderBy('nama')->get(),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'jenjang_id' => ['required', 'exists:jenjangs,id'],
            'mapel_nama' => ['required', 'string', 'max:255'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'judul' => ['required', 'string', 'max:255'],
            'konten' => ['nullable', 'string'],
            'urutan' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            // Batas & tipe dijaga: file diunggah guru lalu dibuka siswa.
            'lampiran' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip'],
            // Gambar ilustrasi per slot [GAMBAR: ...]
            'gambar' => ['nullable', 'array'],
            'gambar.*' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif'],
        ]);

        $mapel = Mapel::firstOrCreate([
            'jenjang_id' => $v['jenjang_id'],
            'nama' => trim($v['mapel_nama']),
        ]);

        $data = [
            'guru_id' => $request->user()->id,
            'mapel_id' => $mapel->id,
            'kelas_id' => $v['kelas_id'] ?? null,
            'judul' => $v['judul'],
            'konten' => $v['konten'] ?? null,
            'urutan' => $v['urutan'],
            'status' => $v['status'],
            'sumber' => 'manual',
        ];

        if ($request->hasFile('lampiran')) {
            $data['file_path'] = $request->file('lampiran')->store('materi', 'public');
            $data['sumber'] = 'import';
        }

        return $data;
    }

    /**
     * Simpan gambar ilustrasi per slot. Slot yang tidak diunggahi tetap memakai
     * gambar lama, jadi guru bisa mencicil unggahnya.
     */
    private function simpanGambar(Request $request, Materi $materi): void
    {
        if (! $request->hasFile('gambar')) {
            return;
        }

        $peta = $materi->gambar ?? [];

        foreach ($request->file('gambar') as $slot => $berkas) {
            if (! $berkas) {
                continue;
            }

            if (isset($peta[$slot])) {
                Storage::disk('public')->delete($peta[$slot]);
            }

            $peta[$slot] = $berkas->store('materi/gambar', 'public');
        }

        ksort($peta);
        $materi->update(['gambar' => $peta]);
    }
}
