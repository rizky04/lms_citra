<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\Soal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoalController extends Controller
{
    public function index(Request $request): View
    {
        $soal = Soal::with(['mapel', 'jenjang'])
            ->when($request->tipe, fn ($q, $t) => $q->where('tipe', $t))
            ->when($request->tag, fn ($q, $t) => $q->where('tag', 'like', "%{$t}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(15)->withQueryString();

        // Ringkasan untuk hero — dihitung dari seluruh bank, bukan halaman ini saja.
        $ringkas = [
            'total' => Soal::count(),
            'pg' => Soal::where('tipe', 'pg')->count(),
            'draft' => Soal::where('status', 'draft')->count(),
        ];

        return view('guru.soal.index', compact('soal', 'ringkas'));
    }

    public function create(): View
    {
        return view('guru.soal.form', [
            'soal' => new Soal(['tipe' => 'pg', 'bobot' => 1, 'status' => 'published']),
            'jenjangList' => Jenjang::orderBy('id')->get(),
            'mapelList' => Mapel::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Soal::create($data);

        return redirect()->route('soal.index')->with('status', 'Soal ditambahkan.');
    }

    public function edit(Soal $soal): View
    {
        return view('guru.soal.form', [
            'soal' => $soal,
            'jenjangList' => Jenjang::orderBy('id')->get(),
            'mapelList' => Mapel::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Soal $soal): RedirectResponse
    {
        $soal->update($this->validated($request));

        return redirect()->route('soal.index')->with('status', 'Soal diperbarui.');
    }

    public function destroy(Soal $soal): RedirectResponse
    {
        $soal->delete();

        return redirect()->route('soal.index')->with('status', 'Soal dihapus.');
    }

    // Validasi + normalisasi (mapel inline, opsi PG jadi json).
    private function validated(Request $request): array
    {
        $v = $request->validate([
            'jenjang_id' => ['required', 'exists:jenjangs,id'],
            'mapel_nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'in:pg,esai,praktik'],
            'pertanyaan' => ['required', 'string'],
            'bobot' => ['required', 'integer', 'min:1'],
            'tingkat' => ['nullable', 'in:mudah,sedang,sulit'],
            'tag' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            // khusus PG
            'opsi' => ['required_if:tipe,pg', 'array'],
            'opsi.*' => ['nullable', 'string'],
            'jawaban_benar' => ['required_if:tipe,pg', 'nullable', 'string'],
        ]);

        $mapel = Mapel::firstOrCreate([
            'jenjang_id' => $v['jenjang_id'],
            'nama' => trim($v['mapel_nama']),
        ]);

        return [
            'guru_id' => $request->user()->id,
            'mapel_id' => $mapel->id,
            'jenjang_id' => $v['jenjang_id'],
            'tipe' => $v['tipe'],
            'pertanyaan' => $v['pertanyaan'],
            'opsi_json' => $v['tipe'] === 'pg' ? array_filter($v['opsi'] ?? []) : null,
            'jawaban_benar' => $v['tipe'] === 'pg' ? ($v['jawaban_benar'] ?? null) : null,
            'bobot' => $v['bobot'],
            'tingkat' => $v['tingkat'] ?? null,
            'tag' => $v['tag'] ?? null,
            'status' => $v['status'],
            'sumber' => 'manual',
        ];
    }
}
