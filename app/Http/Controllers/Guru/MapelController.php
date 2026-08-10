<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MapelController extends Controller
{
    public function index(): View
    {
        $mapel = Mapel::with('jenjang')
            ->withCount(['soal', 'materi', 'perangkat'])
            ->orderBy('jenjang_id')->orderBy('nama')
            ->get();

        return view('guru.mapel.index', [
            'mapel' => $mapel,
            'jenjangList' => Jenjang::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->aturan($request));

        Mapel::create($request->only('nama', 'jenjang_id'));

        return back()->with('status', 'Mata pelajaran ditambahkan.');
    }

    public function update(Request $request, Mapel $mapel): RedirectResponse
    {
        $request->validate($this->aturan($request, $mapel));

        $mapel->update($request->only('nama', 'jenjang_id'));

        return back()->with('status', 'Mata pelajaran diperbarui.');
    }

    public function destroy(Mapel $mapel): RedirectResponse
    {
        $mapel->loadCount(['soal', 'materi', 'perangkat']);
        $dipakai = $mapel->soal_count + $mapel->materi_count + $mapel->perangkat_count;

        // Jangan hapus mapel yang masih menaungi soal/materi/perangkat —
        // FK cascade akan menghapus data itu juga tanpa disadari guru.
        if ($dipakai > 0) {
            return back()->withErrors([
                'hapus' => "\"{$mapel->nama}\" masih dipakai {$dipakai} item (soal/materi/perangkat). "
                    .'Pindahkan dulu isinya atau ganti nama saja.',
            ]);
        }

        $mapel->delete();

        return back()->with('status', 'Mata pelajaran dihapus.');
    }

    // Nama mapel unik per (sekolah, jenjang) — cegah duplikat "Informatika" di jenjang sama.
    private function aturan(Request $request, ?Mapel $abaikan = null): array
    {
        return [
            'jenjang_id' => ['required', 'exists:jenjangs,id'],
            'nama' => [
                'required', 'string', 'max:255',
                Rule::unique('mapels', 'nama')
                    ->where('sekolah_id', $request->user()->sekolah_id)
                    ->where('jenjang_id', $request->jenjang_id)
                    ->ignore($abaikan?->id),
            ],
        ];
    }
}
