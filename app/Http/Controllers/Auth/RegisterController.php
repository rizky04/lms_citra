<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        // Daftar sekolah untuk dropdown "gabung sekolah" (guest: query tak di-scope)
        $sekolahList = Sekolah::where('status', 'active')->orderBy('nama')->get(['id', 'nama']);

        return view('auth.register', compact('sekolahList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'peran' => ['required', 'in:guru,siswa'],
        ]);

        return $request->peran === 'guru'
            ? $this->daftarGuru($request)
            : $this->daftarSiswa($request);
    }

    // Guru: buat sekolah baru (jadi admin) ATAU gabung sekolah existing (pending approval).
    private function daftarGuru(Request $request): RedirectResponse
    {
        $request->validate([
            'mode_sekolah' => ['required', 'in:buat,gabung'],
            'nama_sekolah' => ['required_if:mode_sekolah,buat', 'nullable', 'string', 'max:255'],
            'sekolah_id' => ['required_if:mode_sekolah,gabung', 'nullable', 'exists:sekolahs,id'],
        ]);

        $user = DB::transaction(function () use ($request) {
            if ($request->mode_sekolah === 'buat') {
                $sekolah = Sekolah::create(['nama' => $request->nama_sekolah]);
                $role = Role::ADMIN_SEKOLAH; // pembuat sekolah = admin
                $status = 'active';
            } else {
                $sekolah = Sekolah::findOrFail($request->sekolah_id);
                // Kalau sekolah belum punya admin aktif, guru ini jadi admin; kalau sudah, pending.
                $adaAdmin = User::where('sekolah_id', $sekolah->id)
                    ->where('status', 'active')
                    ->whereHas('roles', fn ($q) => $q->where('name', Role::ADMIN_SEKOLAH))
                    ->exists();
                $role = $adaAdmin ? Role::GURU : Role::ADMIN_SEKOLAH;
                $status = $adaAdmin ? 'pending' : 'active';
            }

            $user = User::create([
                'sekolah_id' => $sekolah->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $status,
            ]);
            $user->assignRole($role);

            return $user;
        });

        if (! $user->isActive()) {
            return redirect()->route('login')->with('status',
                'Pendaftaran diterima. Akun guru menunggu persetujuan admin sekolah.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    // Siswa: gabung pakai kode kelas dari guru.
    private function daftarSiswa(Request $request): RedirectResponse
    {
        $request->validate(['kode_kelas' => ['required', 'string']]);

        $kelas = Kelas::withoutGlobalScopes()
            ->where('kode_undangan', strtoupper(trim($request->kode_kelas)))->first();

        if (! $kelas) {
            throw ValidationException::withMessages([
                'kode_kelas' => 'Kode kelas tidak ditemukan.',
            ]);
        }

        $user = DB::transaction(function () use ($request, $kelas) {
            $user = User::create([
                'sekolah_id' => $kelas->sekolah_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);
            $user->assignRole(Role::SISWA);
            $kelas->siswa()->attach($user->id);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
