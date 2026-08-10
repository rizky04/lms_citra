<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $sekolahId = $request->user()->sekolah_id;

        $base = fn () => User::where('sekolah_id', $sekolahId)->with('roles');

        return view('admin.user.index', [
            'pending' => $base()->where('status', 'pending')->latest()->get(),
            'guru' => $base()->where('status', 'active')
                ->whereHas('roles', fn ($q) => $q->whereIn('name', [Role::GURU, Role::ADMIN_SEKOLAH]))
                ->orderBy('name')->get(),
            'siswa' => $base()->whereHas('roles', fn ($q) => $q->where('name', Role::SISWA))
                ->withCount('kelasDiikuti')->orderBy('name')->paginate(20),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->pastikanSekolahSama($request, $user);

        $user->update(['status' => 'active']);

        return back()->with('status', "Akun {$user->name} disetujui.");
    }

    public function tolak(Request $request, User $user): RedirectResponse
    {
        $this->pastikanSekolahSama($request, $user);
        abort_if($user->status !== 'pending', 403, 'Hanya akun pending yang bisa ditolak.');

        $nama = $user->name;
        $user->delete();

        return back()->with('status', "Pendaftaran {$nama} ditolak.");
    }

    // Buat akun siswa manual (untuk yang belum punya email sendiri, mis. jenjang SD).
    public function storeSiswa(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'daftar_nama' => ['required', 'string'], // satu nama per baris
        ]);

        $kelas = Kelas::findOrFail($v['kelas_id']);
        $namaList = collect(preg_split('/\r\n|\r|\n/', $v['daftar_nama']))
            ->map(fn ($n) => trim($n))->filter()->take(200);

        abort_if($namaList->isEmpty(), 422);

        $dibuat = DB::transaction(function () use ($namaList, $kelas, $request) {
            $hasil = [];
            foreach ($namaList as $nama) {
                $username = $this->usernameUnik($nama);
                $sandi = Str::upper(Str::random(6));

                $siswa = User::create([
                    'sekolah_id' => $request->user()->sekolah_id,
                    'name' => $nama,
                    'email' => $username,
                    'password' => Hash::make($sandi),
                    'status' => 'active',
                ]);
                $siswa->assignRole(Role::SISWA);
                $kelas->siswa()->attach($siswa->id);

                $hasil[] = ['nama' => $nama, 'email' => $username, 'sandi' => $sandi];
            }

            return $hasil;
        });

        // Sandi hanya bisa dilihat sekali di sini — tersimpan ter-hash.
        return back()->with('kartuLogin', $dibuat)
            ->with('status', count($dibuat).' akun siswa dibuat. Catat/cetak kartu login sekarang.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->pastikanSekolahSama($request, $user);
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa menghapus akun sendiri.');

        $nama = $user->name;
        $user->delete();

        return back()->with('status', "Akun {$nama} dihapus.");
    }

    // Admin hanya boleh menyentuh user di sekolahnya sendiri.
    private function pastikanSekolahSama(Request $request, User $user): void
    {
        abort_unless($user->sekolah_id === $request->user()->sekolah_id, 403);
    }

    // Email sintetis untuk siswa tanpa email asli: budi.santoso@<sekolah>.lokal
    private function usernameUnik(string $nama): string
    {
        $dasar = Str::slug($nama, '.') ?: 'siswa';
        $email = $dasar.'@siswa.lokal';
        $n = 1;

        while (User::where('email', $email)->exists()) {
            $email = $dasar.(++$n).'@siswa.lokal';
        }

        return $email;
    }
}
