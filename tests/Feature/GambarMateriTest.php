<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GambarMateriTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private Mapel $mapel;

    protected function setUp(): void
    {
        parent::setUp();

        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $this->guru = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $this->guru->assignRole('guru');

        $this->mapel = Mapel::create([
            'sekolah_id' => $sekolah->id,
            'jenjang_id' => Jenjang::where('nama', 'SMP')->first()->id,
            'nama' => 'Informatika',
        ]);
    }

    private function buatMateri(string $konten): Materi
    {
        return Materi::create([
            'sekolah_id' => $this->guru->sekolah_id,
            'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guru->id,
            'judul' => 'Pengenalan Komputer',
            'konten' => $konten,
            'status' => 'published',
        ]);
    }

    public function test_penanda_gambar_dikenali_parser(): void
    {
        $teks = "Bagian Komputer\n\nKomputer punya banyak bagian.\n\n"
            ."[GAMBAR: Diagram bagian komputer dengan label monitor dan keyboard]\n\n"
            ."Setiap bagian punya fungsinya.";

        $blok = RichText::blok($teks);
        $tipe = array_column($blok, 'tipe');

        $this->assertSame(['judul', 'paragraf', 'gambar', 'paragraf'], $tipe);
        $this->assertSame('Diagram bagian komputer dengan label monitor dan keyboard', $blok[2]['isi']);

        $slot = RichText::slotGambar($teks);
        $this->assertCount(1, $slot);
    }

    public function test_slot_gambar_tampil_di_halaman_baca(): void
    {
        $materi = $this->buatMateri("Isi materi ini cukup panjang untuk diuji.\n\n[GAMBAR: Ilustrasi CPU]");

        $this->actingAs($this->guru)->get(route('materi.show', $materi))
            ->assertOk()
            ->assertSee('Ilustrasi CPU')
            ->assertSee('Slot ilustrasi 1');
    }

    public function test_guru_bisa_unggah_gambar_ke_slot(): void
    {
        Storage::fake('public');

        $materi = $this->buatMateri("Penjelasan awal materi.\n\n[GAMBAR: Ilustrasi CPU]\n\nPenjelasan lanjutan.");

        $this->actingAs($this->guru)->put(route('materi.update', $materi), [
            'jenjang_id' => $this->mapel->jenjang_id,
            'mapel_nama' => 'Informatika',
            'judul' => $materi->judul,
            'konten' => $materi->konten,
            'urutan' => 0,
            'status' => 'published',
            'gambar' => [0 => UploadedFile::fake()->image('cpu.png', 640, 480)],
        ])->assertRedirect(route('materi.index'));

        $materi->refresh();
        $this->assertArrayHasKey(0, $materi->gambar);
        Storage::disk('public')->assertExists($materi->gambar[0]);

        // Setelah diunggah, halaman baca menampilkan gambar, bukan placeholder.
        $this->actingAs($this->guru)->get(route('materi.show', $materi))
            ->assertOk()
            ->assertSee($materi->gambar[0])
            ->assertDontSee('Slot ilustrasi 1');
    }

    public function test_pdf_materi_tidak_memakai_justify(): void
    {
        $materi = $this->buatMateri("Judul Bagian\n\nIsi paragraf.\n\n* Poin satu\n* Poin dua");
        $materi->load(['mapel.jenjang', 'guru', 'sekolah']);

        $html = view('pdf.materi', compact('materi'))->render();

        // Justify + pre-line dulu bikin spasi merenggang sampai selebar halaman.
        $this->assertStringNotContainsString('text-align: justify', $html);
        $this->assertStringNotContainsString('white-space: pre-line', $html);

        // Bullet "*" harus jadi <li>, bukan teks mentah.
        $this->assertStringContainsString('<li>Poin satu</li>', $html);
        $this->assertStringContainsString('class="bab"', $html);
    }

    public function test_pdf_bisa_diunduh_dengan_slot_gambar(): void
    {
        $materi = $this->buatMateri("Isi materi.\n\n[GAMBAR: Ilustrasi CPU]");

        $res = $this->actingAs($this->guru)->get(route('materi.pdf', $materi));

        $res->assertOk();
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }
}
