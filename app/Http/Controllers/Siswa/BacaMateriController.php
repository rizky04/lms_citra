<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BacaMateriController extends Controller
{
    public function index(Request $request): View
    {
        $kelasIds = $request->user()->kelasDiikuti()->pluck('kelas.id');

        // Materi untuk kelasnya, atau materi umum (tanpa kelas) di sekolah yang sama.
        $materi = Materi::with(['mapel.jenjang', 'kelas'])
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereIn('kelas_id', $kelasIds)->orWhereNull('kelas_id'))
            ->orderBy('urutan')->latest()
            ->paginate(12);

        return view('siswa.materi.index', compact('materi'));
    }

    public function show(Request $request, Materi $materi): View
    {
        $kelasIds = $request->user()->kelasDiikuti()->pluck('kelas.id');

        abort_unless(
            $materi->status === 'published'
                && ($materi->kelas_id === null || $kelasIds->contains($materi->kelas_id)),
            403
        );

        $materi->load(['mapel.jenjang', 'kelas', 'guru']);

        return view('siswa.materi.show', compact('materi'));
    }
}
