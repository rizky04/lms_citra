<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Master data platform: jenjang + info setelan (Gemini) yang dibaca dari .env.
class MasterController extends Controller
{
    public function index(): View
    {
        return view('superadmin.master.index', [
            'jenjangList' => Jenjang::withCount(['mapel', 'kelas', 'soal'])->orderBy('id')->get(),
            'setelan' => [
                'model' => config('services.gemini.model'),
                'limit_harian' => config('services.gemini.daily_limit'),
                'api_terisi' => filled(config('services.gemini.key')),
            ],
        ]);
    }

    public function storeJenjang(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:jenjangs,nama'],
        ]);

        Jenjang::create($v);

        return back()->with('status', "Jenjang \"{$v['nama']}\" ditambahkan.");
    }

    public function updateJenjang(Request $request, Jenjang $jenjang): RedirectResponse
    {
        $v = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:jenjangs,nama,'.$jenjang->id],
        ]);

        $jenjang->update($v);

        return back()->with('status', 'Nama jenjang diperbarui.');
    }

    public function destroyJenjang(Jenjang $jenjang): RedirectResponse
    {
        if ($jenjang->sedangDipakai()) {
            return back()->withErrors([
                'jenjang' => "Jenjang \"{$jenjang->nama}\" masih dipakai mapel/kelas/soal, tidak bisa dihapus.",
            ]);
        }

        $nama = $jenjang->nama;
        $jenjang->delete();

        return back()->with('status', "Jenjang \"{$nama}\" dihapus.");
    }
}
