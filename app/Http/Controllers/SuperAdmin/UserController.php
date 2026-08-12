<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Manajemen peran & akses seluruh pengguna lintas sekolah.
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with(['roles', 'sekolah'])
            ->when($request->q, fn ($query, $q) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
            ))
            ->when($request->sekolah_id, fn ($query, $id) => $query->where('sekolah_id', $id))
            ->when($request->role, fn ($query, $r) => $query->whereHas('roles', fn ($rq) => $rq->where('name', $r)))
            ->when($request->status, fn ($query, $s) => $query->where('status', $s))
            // Pending di atas; CASE portabel (FIELD() cuma ada di MySQL).
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->paginate(25)->withQueryString();

        return view('superadmin.pengguna.index', [
            'users' => $users,
            'sekolahList' => Sekolah::orderBy('nama')->get(['id', 'nama']),
            'roleList' => Role::SEMUA,
            'matriks' => $this->matriksAkses(),
        ]);
    }

    public function ubahPeran(Request $request, User $user): RedirectResponse
    {
        $this->pastikanBukanDiriSendiri($request, $user);

        $v = $request->validate([
            'role' => ['required', 'in:'.implode(',', Role::SEMUA)],
        ]);

        // Naik/turun ke peran non-super wajib punya sekolah; super admin justru tak boleh punya.
        if ($v['role'] === Role::SUPER_ADMIN) {
            $user->update(['sekolah_id' => null]);
        } elseif (! $user->sekolah_id) {
            return back()->withErrors(['role' => 'User ini belum punya sekolah, tidak bisa diberi peran sekolah.']);
        }

        $user->syncRoles([$v['role']]);

        return back()->with('status', "Peran {$user->name} diubah menjadi ".str_replace('_', ' ', $v['role']).'.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->pastikanBukanDiriSendiri($request, $user);

        $baru = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $baru]);

        return back()->with('status', "Akun {$user->name} ".($baru === 'active' ? 'diaktifkan' : 'disuspend').'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->pastikanBukanDiriSendiri($request, $user);

        $nama = $user->name;
        $user->delete();

        return back()->with('status', "Akun {$nama} dihapus.");
    }

    private function pastikanBukanDiriSendiri(Request $request, User $user): void
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa mengubah akun sendiri.');
    }

    // Matriks hak akses tiap peran — referensi, mencerminkan pembatasan route.
    private function matriksAkses(): array
    {
        return [
            'Kelola sekolah (platform)' => [Role::SUPER_ADMIN],
            'Masuk sebagai user sekolah' => [Role::SUPER_ADMIN],
            'Kelola master data & peran' => [Role::SUPER_ADMIN],
            'Approve guru & kelola pengguna sekolah' => [Role::ADMIN_SEKOLAH],
            'Pengaturan sekolah (nama, API key)' => [Role::ADMIN_SEKOLAH],
            'Kelas, materi, bank soal, kuis' => [Role::ADMIN_SEKOLAH, Role::GURU],
            'Tugas, koreksi, perangkat ajar' => [Role::ADMIN_SEKOLAH, Role::GURU],
            'Laporan & asisten AI' => [Role::ADMIN_SEKOLAH, Role::GURU],
            'Kerjakan kuis & tugas' => [Role::SISWA],
            'Baca materi & lihat rapor' => [Role::SISWA],
        ];
    }
}
