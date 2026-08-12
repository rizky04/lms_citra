<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\AiController;
use App\Http\Controllers\Admin\SekolahController as AdminSekolahController;
use App\Http\Controllers\Guru\KelasController;
use App\Http\Controllers\Guru\KoreksiController;
use App\Http\Controllers\Guru\KuisController;
use App\Http\Controllers\Guru\LaporanController;
use App\Http\Controllers\Guru\MapelController;
use App\Http\Controllers\Guru\MateriController;
use App\Http\Controllers\Guru\PerangkatController;
use App\Http\Controllers\Guru\SoalController;
use App\Http\Controllers\Guru\SoalIoController;
use App\Http\Controllers\Guru\TugasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\BacaMateriController;
use App\Http\Controllers\Siswa\KerjakanKuisController;
use App\Http\Controllers\Siswa\RaporController;
use App\Http\Controllers\Siswa\TugasSiswaController;
use App\Http\Controllers\SuperAdmin\ImpersonationController;
use App\Http\Controllers\SuperAdmin\MasterController;
use App\Http\Controllers\SuperAdmin\SekolahController as SuperSekolahController;
use App\Http\Controllers\SuperAdmin\UserController as SuperUserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');

// --- Auth (tanpa Breeze) ---
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1');
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// --- Umum (semua role) ---
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('notifikasi/{id}', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Guru (& admin sekolah) ---
Route::middleware(['auth', 'role:guru|admin_sekolah'])->group(function () {
    // parameters() wajib: Str::singular() bikin "kelas"→{kela} dan "kuis"→{kui},
    // yang tidak cocok dengan $kelas/$kuis di controller sehingga model binding gagal diam-diam.
    Route::resource('kelas', KelasController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->parameters(['kelas' => 'kelas']);
    // IO soal didaftarkan SEBELUM resource, supaya "soal/io" tidak tertangkap "soal/{soal}".
    Route::get('soal/io', [SoalIoController::class, 'form'])->name('soal.io');
    Route::get('soal/io/template', [SoalIoController::class, 'template'])->name('soal.io.template');
    Route::get('soal/io/export', [SoalIoController::class, 'export'])->name('soal.io.export');
    Route::post('soal/io/import', [SoalIoController::class, 'import'])->name('soal.io.import');
    Route::resource('soal', SoalController::class)->except('show');

    Route::resource('materi', MateriController::class)->parameters(['materi' => 'materi']);
    Route::get('materi/{materi}/pdf', [MateriController::class, 'pdf'])->name('materi.pdf');

    Route::get('perangkat/{perangkat}/pdf', [PerangkatController::class, 'pdf'])->name('perangkat.pdf');
    Route::resource('perangkat', PerangkatController::class)->parameters(['perangkat' => 'perangkat']);

    Route::get('ai', [AiController::class, 'index'])->name('ai.index');
    Route::get('ai/status', [AiController::class, 'status'])->name('ai.status');
    Route::post('ai', [AiController::class, 'store'])->name('ai.store');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');

    Route::get('mapel', [MapelController::class, 'index'])->name('mapel.index');
    Route::post('mapel', [MapelController::class, 'store'])->name('mapel.store');
    Route::put('mapel/{mapel}', [MapelController::class, 'update'])->name('mapel.update');
    Route::delete('mapel/{mapel}', [MapelController::class, 'destroy'])->name('mapel.destroy');

    Route::resource('kuis', KuisController::class)->parameters(['kuis' => 'kuis']);
    Route::post('kuis/{kuis}/soal', [KuisController::class, 'tambahSoal'])->name('kuis.soal.tambah');
    Route::delete('kuis/{kuis}/soal/{soal}', [KuisController::class, 'hapusSoal'])->name('kuis.soal.hapus');
    Route::post('kuis/{kuis}/publish', [KuisController::class, 'publish'])->name('kuis.publish');

    Route::resource('tugas', TugasController::class)->parameters(['tugas' => 'tugas']);
    Route::post('tugas/{tugas}/nilai/{submisi}', [TugasController::class, 'nilai'])->name('tugas.nilai');

    Route::get('koreksi', [KoreksiController::class, 'index'])->name('koreksi.index');
    Route::get('koreksi/{kuis}', [KoreksiController::class, 'show'])->name('koreksi.show');
    Route::post('koreksi/{kuis}', [KoreksiController::class, 'nilai'])->name('koreksi.nilai');
});

// --- Admin sekolah ---
Route::middleware(['auth', 'role:admin_sekolah'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('pengguna', [UserController::class, 'index'])->name('user.index');
    Route::post('pengguna/{user}/approve', [UserController::class, 'approve'])->name('user.approve');
    Route::post('pengguna/{user}/tolak', [UserController::class, 'tolak'])->name('user.tolak');
    Route::post('pengguna/siswa', [UserController::class, 'storeSiswa'])->name('user.siswa.store');
    Route::delete('pengguna/{user}', [UserController::class, 'destroy'])->name('user.destroy');

    Route::get('sekolah', [AdminSekolahController::class, 'edit'])->name('sekolah.edit');
    Route::put('sekolah', [AdminSekolahController::class, 'update'])->name('sekolah.update');
    Route::put('sekolah/apikey', [AdminSekolahController::class, 'updateApiKey'])->name('sekolah.apikey');
});

// --- Super admin platform ---
Route::middleware(['auth', 'role:super_admin'])->prefix('super')->name('superadmin.')->group(function () {
    Route::get('sekolah', [SuperSekolahController::class, 'index'])->name('sekolah.index');
    Route::post('sekolah/{sekolah}/toggle', [SuperSekolahController::class, 'toggle'])->name('sekolah.toggle');

    // Manajemen peran & akses lintas sekolah
    Route::get('pengguna', [SuperUserController::class, 'index'])->name('pengguna.index');
    Route::put('pengguna/{user}/peran', [SuperUserController::class, 'ubahPeran'])->name('pengguna.peran');
    Route::post('pengguna/{user}/toggle', [SuperUserController::class, 'toggleStatus'])->name('pengguna.toggle');
    Route::delete('pengguna/{user}', [SuperUserController::class, 'destroy'])->name('pengguna.destroy');

    // Masuk sebagai user sekolah
    Route::post('masuk-sebagai/{user}', [ImpersonationController::class, 'masuk'])->name('masuk-sebagai');

    // Master data & setelan platform
    Route::get('master', [MasterController::class, 'index'])->name('master.index');
    Route::post('master/jenjang', [MasterController::class, 'storeJenjang'])->name('master.jenjang.store');
    Route::put('master/jenjang/{jenjang}', [MasterController::class, 'updateJenjang'])->name('master.jenjang.update');
    Route::delete('master/jenjang/{jenjang}', [MasterController::class, 'destroyJenjang'])->name('master.jenjang.destroy');
});

// Keluar dari mode "masuk sebagai" — di grup auth biasa karena saat impersonasi
// user aktif bukan super admin lagi.
Route::post('impersonasi/keluar', [ImpersonationController::class, 'keluar'])
    ->middleware('auth')->name('impersonasi.keluar');

// --- Siswa ---
Route::middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('kerjakan', [KerjakanKuisController::class, 'index'])->name('kerjakan.index');
    Route::get('kerjakan/{kuis}', [KerjakanKuisController::class, 'show'])->name('kerjakan.show');
    Route::post('kerjakan/{kuis}', [KerjakanKuisController::class, 'submit'])->name('kerjakan.submit');
    Route::get('kerjakan/{kuis}/hasil', [KerjakanKuisController::class, 'hasil'])->name('kerjakan.hasil');

    Route::get('belajar', [BacaMateriController::class, 'index'])->name('materi.baca.index');
    Route::get('belajar/{materi}', [BacaMateriController::class, 'show'])->name('materi.baca.show');

    Route::get('tugas-saya', [TugasSiswaController::class, 'index'])->name('tugas.saya.index');
    Route::get('tugas-saya/{tugas}', [TugasSiswaController::class, 'show'])->name('tugas.saya.show');
    Route::post('tugas-saya/{tugas}', [TugasSiswaController::class, 'submit'])->name('tugas.saya.submit');

    Route::get('rapor', [RaporController::class, 'index'])->name('rapor.index');
});
