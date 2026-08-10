<?php

namespace Tests\Feature;

use App\Models\Jenjang;
use App\Models\Sekolah;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SoalIoTest extends TestCase
{
    use RefreshDatabase;

    private function guru(): User
    {
        $sekolah = Sekolah::create(['nama' => 'SMK Uji']);
        $g = User::create([
            'sekolah_id' => $sekolah->id, 'name' => 'Guru', 'email' => 'g@uji.test',
            'password' => Hash::make('password123'), 'status' => 'active',
        ]);
        $g->assignRole('guru');

        return $g;
    }

    private function csv(string $isi): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'soal').'.csv';
        file_put_contents($path, $isi);

        return new UploadedFile($path, 'soal.csv', 'text/csv', null, true);
    }

    public function test_import_csv_valid_masuk_dan_baris_rusak_dilewati(): void
    {
        $guru = $this->guru();
        $this->actingAs($guru);

        $isi = <<<CSV
        tipe,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,jawaban_benar,bobot,tingkat,tag
        pg,Ibu kota Jawa Barat?,Bandung,Bogor,Bekasi,Depok,A,2,mudah,Geografi
        esai,Jelaskan algoritma sorting.,,,,,,5,sedang,Algoritma
        pg,PG tanpa kunci cocok,Satu,Dua,,,Z,1,,Bab1
        ,,,,,,,,,
        xxx,Tipe tidak dikenal,,,,,,1,,
        CSV;

        $res = $this->post(route('soal.io.import'), [
            'berkas' => $this->csv($isi),
            'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'mapel_nama' => 'Informatika',
            'status' => 'draft',
        ]);

        $res->assertRedirect(route('soal.index'));

        // 2 valid masuk; PG kunci salah + tipe tak dikenal dilewati; baris kosong diabaikan.
        $this->assertSame(2, Soal::count());

        $pg = Soal::where('tipe', 'pg')->first();
        $this->assertSame('Ibu kota Jawa Barat?', $pg->pertanyaan);
        $this->assertSame('A', $pg->jawaban_benar);
        $this->assertSame(['A' => 'Bandung', 'B' => 'Bogor', 'C' => 'Bekasi', 'D' => 'Depok'], $pg->opsi_json);
        $this->assertSame('draft', $pg->status);
        $this->assertSame('import', $pg->sumber);

        $this->assertStringContainsString('2 soal berhasil diimpor', session('status'));
        $this->assertStringContainsString('dilewati', session('status'));
    }

    public function test_import_menolak_header_yang_salah(): void
    {
        $this->actingAs($this->guru());

        $res = $this->from(route('soal.io'))->post(route('soal.io.import'), [
            'berkas' => $this->csv("kolom_ngaco,lainnya\nisi,isi"),
            'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'mapel_nama' => 'Informatika',
            'status' => 'draft',
        ]);

        $res->assertSessionHasErrors('berkas');
        $this->assertSame(0, Soal::count());
    }

    public function test_import_toleran_terhadap_bom_excel(): void
    {
        $this->actingAs($this->guru());

        $isi = "\xEF\xBB\xBF".<<<CSV
        tipe,pertanyaan,bobot
        esai,Soal dengan BOM di awal berkas.,3
        CSV;

        $this->post(route('soal.io.import'), [
            'berkas' => $this->csv($isi),
            'jenjang_id' => Jenjang::where('nama', 'SMP')->first()->id,
            'mapel_nama' => 'Informatika',
            'status' => 'published',
        ]);

        $this->assertSame(1, Soal::count());
        $this->assertSame('Soal dengan BOM di awal berkas.', Soal::first()->pertanyaan);
    }

    public function test_export_mengembalikan_csv_berisi_soal(): void
    {
        $guru = $this->guru();
        $this->actingAs($guru);

        Soal::create([
            'mapel_id' => \App\Models\Mapel::create([
                'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id, 'nama' => 'Informatika',
            ])->id,
            'jenjang_id' => Jenjang::where('nama', 'SMK')->first()->id,
            'guru_id' => $guru->id, 'tipe' => 'pg', 'pertanyaan' => 'Soal ekspor?',
            'opsi_json' => ['A' => 'x', 'B' => 'y'], 'jawaban_benar' => 'B',
            'bobot' => 1, 'status' => 'published',
        ]);

        $res = $this->get(route('soal.io.export'));
        $res->assertOk();

        $isi = $res->streamedContent();
        $this->assertStringContainsString('Soal ekspor?', $isi);
        $this->assertStringContainsString('tipe,pertanyaan', $isi);
    }

    public function test_template_bisa_diunduh(): void
    {
        $this->actingAs($this->guru());

        $res = $this->get(route('soal.io.template'));
        $res->assertOk();
        $this->assertStringContainsString('jawaban_benar', $res->streamedContent());
    }
}
