<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Super admin platform: pantau & kelola status semua sekolah.
class SekolahController extends Controller
{
    public function index(): View
    {
        $sekolah = Sekolah::withCount([
            'users',
            'users as guru_count' => fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', [Role::GURU, Role::ADMIN_SEKOLAH])),
            'users as siswa_count' => fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', Role::SISWA)),
        ])->latest()->paginate(20);

        return view('superadmin.sekolah.index', compact('sekolah'));
    }

    public function toggle(Request $request, Sekolah $sekolah): RedirectResponse
    {
        $baru = $sekolah->status === 'active' ? 'suspended' : 'active';
        $sekolah->update(['status' => $baru]);

        if ($baru === 'suspended') {
            // Blokir login: hanya user aktif yang diubah, jejak 'pending' dipertahankan.
            User::where('sekolah_id', $sekolah->id)->where('status', 'active')
                ->update(['status' => 'suspended']);
        } else {
            // Aktifkan kembali hanya yang tadi disuspend — jangan sentuh guru 'pending'.
            User::where('sekolah_id', $sekolah->id)->where('status', 'suspended')
                ->update(['status' => 'active']);
        }

        $label = $baru === 'active' ? 'diaktifkan' : 'disuspend';

        return back()->with('status', "Sekolah \"{$sekolah->nama}\" {$label}.");
    }
}
