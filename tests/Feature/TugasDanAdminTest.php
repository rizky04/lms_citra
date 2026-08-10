<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\PerangkatPembelajaran;
use App\Models\Sekolah;
use App\Models\SubmisiTugas;
use App\Models\Tugas;
use App\Models\User;
use App\Notifications\TugasBaru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TugasDanAdminTest extends TestCase
{
    use RefreshDatabase;

    private Sekolah $sekolah;

    private User $guru;

    private User $siswa;

    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sekolah = Sekolah::create(['nama' => 'SMK Uji']);

        $this->guru = User::create([
            'sekolah_id' => $this->sekolah->id, 'name' => 'Pak Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $this->guru->assignRole('admin_sekolah');

        $this->siswa = User::create([
            'sekolah_id' => $this->sekolah->id, 'name' => 'Siswa Satu', 'email' => 's@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $this->siswa->assignRole('siswa');

        $this->kelas = Kelas::create([
            'sekolah_id' => $this->sekolah->id,
            'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'nama' => 'XI RPL', 'wali_guru_id' => $this->guru->id,
        ]);
        $this->kelas->siswa()->attach($this->siswa->id);
    }

    public function test_alur_tugas_lengkap_dari_buat_sampai_dinilai(): void
    {
        Notification::fake();
        Storage::fake('public');

        // Guru buat tugas → siswa dinotifikasi
        $this->actingAs($this->guru)->post(route('tugas.store'), [
            'kelas_id' => $this->kelas->id,
            'judul' => 'Praktikum Flowchart',
            'instruksi' => 'Buat flowchart menghitung luas segitiga.',
        ])->assertRedirect();

        $tugas = Tugas::first();
        Notification::assertSentTo($this->siswa, TugasBaru::class);

        // Siswa kumpulkan
        $this->actingAs($this->siswa)->post(route('tugas.saya.submit', $tugas), [
            'isi' => 'Ini jawaban saya.',
            'berkas' => UploadedFile::fake()->create('flowchart.pdf', 200, 'application/pdf'),
        ])->assertRedirect(route('tugas.saya.show', $tugas));

        $submisi = SubmisiTugas::first();
        $this->assertSame('Ini jawaban saya.', $submisi->isi);
        $this->assertNotNull($submisi->submitted_at);
        $this->assertSame($this->sekolah->id, $submisi->sekolah_id);
        Storage::disk('public')->assertExists($submisi->file_path);

        // Guru menilai
        $this->actingAs($this->guru)->post(route('tugas.nilai', [$tugas, $submisi]), [
            'nilai' => 88, 'feedback' => 'Rapi, tapi beri keterangan simbol.',
        ])->assertRedirect();

        $this->assertEquals(88, $submisi->fresh()->nilai);

        // Sudah dinilai → siswa tidak bisa mengubah lagi
        $this->actingAs($this->siswa)->post(route('tugas.saya.submit', $tugas), [
            'isi' => 'Coba ubah setelah dinilai',
        ])->assertForbidden();
    }

    public function test_submit_kosong_ditolak(): void
    {
        $tugas = Tugas::create([
            'sekolah_id' => $this->sekolah->id, 'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id, 'judul' => 'Tugas kosong',
        ]);

        $this->actingAs($this->siswa)
            ->from(route('tugas.saya.show', $tugas))
            ->post(route('tugas.saya.submit', $tugas), ['isi' => ''])
            ->assertSessionHasErrors('isi');

        $this->assertSame(0, SubmisiTugas::count());
    }

    public function test_siswa_kelas_lain_tidak_bisa_lihat_tugas(): void
    {
        $lain = User::create([
            'sekolah_id' => $this->sekolah->id, 'name' => 'Siswa Lain', 'email' => 'l@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $lain->assignRole('siswa');

        $tugas = Tugas::create([
            'sekolah_id' => $this->sekolah->id, 'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id, 'judul' => 'Rahasia',
        ]);

        $this->actingAs($lain)->get(route('tugas.saya.show', $tugas))->assertForbidden();
    }

    public function test_admin_menyetujui_guru_pending(): void
    {
        $pending = User::create([
            'sekolah_id' => $this->sekolah->id, 'name' => 'Guru Baru', 'email' => 'baru@uji.test',
            'password' => Hash::make('password123'), 'status' => 'pending',
        ]);
        $pending->assignRole('guru');

        // Belum disetujui → tidak bisa login
        $this->post('/login', ['email' => 'baru@uji.test', 'password' => 'password123'])
            ->assertSessionHasErrors('email');

        $this->actingAs($this->guru)->post(route('admin.user.approve', $pending))->assertRedirect();
        $this->assertSame('active', $pending->fresh()->status);

        // Sekarang bisa login
        $this->post('/login', ['email' => 'baru@uji.test', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_tidak_bisa_sentuh_user_sekolah_lain(): void
    {
        $sekolahLain = Sekolah::create(['nama' => 'SMA Lain']);
        $userLain = User::create([
            'sekolah_id' => $sekolahLain->id, 'name' => 'Orang Lain', 'email' => 'x@lain.test',
            'password' => Hash::make('password123'), 'status' => 'pending',
        ]);
        $userLain->assignRole('guru');

        $this->actingAs($this->guru)->post(route('admin.user.approve', $userLain))->assertForbidden();
        $this->assertSame('pending', $userLain->fresh()->status);
    }

    public function test_buat_akun_siswa_massal_menghasilkan_kartu_login(): void
    {
        $this->actingAs($this->guru)->post(route('admin.user.siswa.store'), [
            'kelas_id' => $this->kelas->id,
            'daftar_nama' => "Budi Santoso\nSiti Aminah\n\nAndi Wijaya",
        ])->assertRedirect();

        $kartu = session('kartuLogin');
        $this->assertCount(3, $kartu); // baris kosong diabaikan
        $this->assertSame('Budi Santoso', $kartu[0]['nama']);
        $this->assertStringContainsString('@siswa.lokal', $kartu[0]['email']);

        // Semua masuk ke kelas dan berperan siswa
        $this->assertSame(4, $this->kelas->siswa()->count()); // 1 lama + 3 baru
        $this->assertTrue(User::where('email', $kartu[0]['email'])->first()->hasRole('siswa'));
    }

    public function test_perangkat_pembelajaran_bisa_diunduh_pdf(): void
    {
        $p = PerangkatPembelajaran::create([
            'sekolah_id' => $this->sekolah->id, 'guru_id' => $this->guru->id,
            'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'jenis' => 'modul_ajar', 'judul' => 'Modul Ajar Informatika',
            'tahun_ajaran' => '2025/2026', 'semester' => 'ganjil',
            'konten' => 'Isi modul ajar untuk diuji.', 'status' => 'published',
        ]);

        $res = $this->actingAs($this->guru)->get(route('perangkat.pdf', $p));

        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }
}
