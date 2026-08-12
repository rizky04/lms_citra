<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * "Masuk sebagai" — super admin login sementara sebagai user sekolah tertentu
 * supaya bisa membuka semua menu sekolah itu (kelas, soal, kuis, dst) tanpa
 * membongkar global scope. Id super admin asli disimpan di session dan
 * dikembalikan lewat "keluar".
 */
class ImpersonationController extends Controller
{
    // Hanya super admin yang boleh memulai (route sudah di-guard role:super_admin).
    public function masuk(User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 403, 'Tidak bisa masuk sebagai super admin lain.');
        abort_unless($user->sekolah_id, 422, 'User ini tidak terikat sekolah.');
        abort_unless($user->isActive(), 422, 'Akun ini tidak aktif.');

        // Simpan id asli sekali saja — hindari impersonasi bertingkat.
        session(['impersonator_id' => Auth::id()]);
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('status', "Kamu sekarang masuk sebagai {$user->name} ({$user->sekolah->nama}).");
    }

    public function keluar(): RedirectResponse
    {
        $asliId = session('impersonator_id');

        if (! $asliId) {
            return redirect()->route('dashboard');
        }

        $asli = User::find($asliId);
        session()->forget('impersonator_id');

        if (! $asli) {
            Auth::logout();

            return redirect()->route('login');
        }

        Auth::login($asli);

        return redirect()->route('superadmin.pengguna.index')
            ->with('status', 'Kembali ke akun super admin.');
    }
}
