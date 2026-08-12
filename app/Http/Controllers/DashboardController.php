<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Soal;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isPengajar()) {
            return view('dashboard.guru', [
                'user' => $user,
                'stats' => [
                    'soal' => Soal::count(),
                    'kuis' => Kuis::count(),
                    'kelas' => Kelas::count(),
                    'siswa' => User::where('sekolah_id', $user->sekolah_id)
                        ->whereHas('roles', fn ($q) => $q->where('name', Role::SISWA))->count(),
                ],
                'kuisTerbaru' => Kuis::with('kelas')->withCount('soal')->latest()->limit(5)->get(),
                'kelasList' => Kelas::withCount('siswa')->latest()->limit(4)->get(),
            ]);
        }

        if ($user->isSiswa()) {
            $kelasIds = $user->kelasDiikuti()->pluck('kelas.id');

            return view('dashboard.siswa', [
                'user' => $user,
                'kuisTersedia' => Kuis::with('kelas')->withCount('soal')
                    ->whereIn('kelas_id', $kelasIds)->where('status', 'published')
                    ->latest()->limit(5)->get(),
                'kelasSaya' => $user->kelasDiikuti()->with('jenjang')->get(),
            ]);
        }

        // Super admin platform — agregat lintas SEMUA sekolah.
        // Model tenant-scoped tidak punya auth sekolah di sini (super admin
        // sekolah_id null), jadi global scope tidak memfilter — count = seluruh platform.
        return view('dashboard.super', [
            'user' => $user,
            'stats' => [
                'sekolah' => \App\Models\Sekolah::count(),
                'sekolah_aktif' => \App\Models\Sekolah::where('status', 'active')->count(),
                'user' => User::count(),
                'guru' => User::whereHas('roles', fn ($q) => $q->whereIn('name', [Role::GURU, Role::ADMIN_SEKOLAH]))->count(),
                'siswa' => User::whereHas('roles', fn ($q) => $q->where('name', Role::SISWA))->count(),
                'pending' => User::where('status', 'pending')->count(),
                'soal' => Soal::count(),
                'kuis' => Kuis::count(),
                'materi' => \App\Models\Materi::count(),
                'tugas' => \App\Models\Tugas::count(),
                'ai_hari_ini' => \App\Models\AiGenerationJob::whereDate('created_at', today())->count(),
            ],
            'sekolahAktif' => \App\Models\Sekolah::withCount([
                'users as siswa_count' => fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', Role::SISWA)),
            ])->orderByDesc('siswa_count')->limit(6)->get(),
        ]);
    }
}
