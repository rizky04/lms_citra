<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Pengaturan sekolah milik admin sekolah sendiri.
class SekolahController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.sekolah.edit', [
            'sekolah' => $request->user()->sekolah,
            'keyPlatformAda' => filled(config('services.gemini.key')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $sekolah = $request->user()->sekolah;
        abort_unless($sekolah, 404);

        $v = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $sekolah->update(['nama' => $v['nama']]);

        return back()->with('status', 'Pengaturan sekolah disimpan.');
    }

    // API key dipisah: field password, dan ada opsi hapus agar kembali ke key platform.
    public function updateApiKey(Request $request): RedirectResponse
    {
        $sekolah = $request->user()->sekolah;
        abort_unless($sekolah, 404);

        if ($request->boolean('hapus_key')) {
            $sekolah->update(['gemini_api_key' => null]);

            return back()->with('status', 'API key sekolah dihapus. Kembali memakai key platform.');
        }

        $v = $request->validate([
            'gemini_api_key' => ['required', 'string', 'max:255'],
        ]);

        $sekolah->update(['gemini_api_key' => trim($v['gemini_api_key'])]);

        return back()->with('status', 'API key sekolah disimpan. Generate AI kini memakai kuota sekolah ini.');
    }
}
