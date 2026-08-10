<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_teks_kosong_menghasilkan_nol_blok(): void
    {
        $this->assertSame([], RichText::blok(null));
        $this->assertSame([], RichText::blok('   '));
    }

    public function test_judul_paragraf_dan_daftar_dikenali(): void
    {
        $teks = <<<TXT
        Apa itu Internet?

        Internet adalah jaringan yang menghubungkan komputer di seluruh dunia.

        Langkah Menggunakan Internet

        1. Nyalakan perangkat.
        2. Buka aplikasi peramban.
        3. Ketik alamat situs.
        TXT;

        $blok = RichText::blok($teks);
        $tipe = array_column($blok, 'tipe');

        $this->assertSame(['judul', 'paragraf', 'judul', 'daftar'], $tipe);
        $this->assertSame('Apa itu Internet?', $blok[0]['isi']);
        $this->assertTrue($blok[3]['urut']);
        $this->assertCount(3, $blok[3]['isi']);
        $this->assertSame('Nyalakan perangkat.', $blok[3]['isi'][0]);
    }

    public function test_kalimat_panjang_bukan_judul(): void
    {
        $teks = 'Komputer adalah alat elektronik yang dipakai untuk mengolah data menjadi informasi berguna.';

        $blok = RichText::blok($teks);

        $this->assertSame('paragraf', $blok[0]['tipe']);
    }

    public function test_daftar_bertanda_minus_dan_bullet(): void
    {
        $blok = RichText::blok("- Monitor\n- Keyboard\n• Mouse");

        $this->assertSame('daftar', $blok[0]['tipe']);
        $this->assertFalse($blok[0]['urut']);
        $this->assertSame(['Monitor', 'Keyboard', 'Mouse'], $blok[0]['isi']);
    }

    public function test_blok_kode_menjorok_dikenali(): void
    {
        $teks = "Contoh kode Python\n\n    for i in range(5):\n        print(i)";

        $blok = RichText::blok($teks);

        $this->assertSame('judul', $blok[0]['tipe']);
        $this->assertSame('kode', $blok[1]['tipe']);
        $this->assertStringContainsString('for i in range(5):', $blok[1]['isi']);
        // indentasi seragam dibuang, indentasi dalam tetap
        $this->assertStringStartsWith('for i', $blok[1]['isi']);
    }

    public function test_judul_bernomor_bab_dikenali(): void
    {
        foreach (['A. INFORMASI UMUM', '1. Tujuan Pembelajaran', 'B. Komponen Inti'] as $judul) {
            $blok = RichText::blok($judul."\n\nIsi bagian ini panjang sekali dan berupa kalimat biasa.");
            $this->assertSame('judul', $blok[0]['tipe'], "gagal untuk: {$judul}");
        }
    }

    // Materi lama tersimpan satu baris tanpa newline; penomoran & kalimat menempel.
    public function test_teks_rusak_satu_baris_diperbaiki(): void
    {
        $panjang = str_repeat('Ini kalimat isi materi yang cukup panjang untuk mengisi ruang. ', 12);
        $teks = $panjang.'menonton video:1. Klik tetikus.2. Perintah diproses.3. Video tampil.';

        $blok = RichText::blok($teks);
        $tipe = array_column($blok, 'tipe');

        $this->assertContains('daftar', $tipe, 'penomoran menempel seharusnya jadi daftar');

        $daftar = collect($blok)->firstWhere('tipe', 'daftar');
        $this->assertCount(3, $daftar['isi']);
        $this->assertSame('Klik tetikus.', $daftar['isi'][0]);
    }

    public function test_judul_yang_tertelan_dipulihkan(): void
    {
        $isi = str_repeat('Kalimat penjelasan yang cukup panjang supaya blob dianggap rusak. ', 10);
        $teks = 'Pengantar Singkat'.$isi.'komputer!Layar Komputer (Monitor)Layar komputer itu seperti televisi.'
            .'Bagaimana Bagian-Bagian Komputer Bekerja Bersama?Semua bagian saling membantu.'
            .'Latihan Mandiri1. Sebutkan dua bagian komputer.';

        $judul = array_column(RichText::daftarIsi($teks), 'isi');

        $this->assertContains('Pengantar Singkat', $judul, 'judul di awal teks');
        $this->assertContains('Layar Komputer (Monitor)', $judul, 'judul dengan tanda kurung');
        $this->assertContains('Bagaimana Bagian-Bagian Komputer Bekerja Bersama?', $judul, 'judul bertanda hubung');
        $this->assertContains('Latihan Mandiri', $judul, 'judul sebelum penomoran');
    }

    public function test_teks_rapi_tidak_ikut_diubah(): void
    {
        // Teks pendek & rapi TIDAK boleh kena perbaikan agresif.
        $teks = "Judul Bagian\n\nKalimat pertama.Kalimat kedua tanpa spasi.";

        $blok = RichText::blok($teks);

        $this->assertSame('Kalimat pertama.Kalimat kedua tanpa spasi.', $blok[1]['isi']);
    }

    public function test_daftar_isi_hanya_mengambil_judul(): void
    {
        $teks = "Bagian Satu\n\nIsi paragraf pertama di sini.\n\nBagian Dua\n\nIsi paragraf kedua di sini.";

        $isi = RichText::daftarIsi($teks);

        $this->assertCount(2, $isi);
        $this->assertSame('Bagian Satu', $isi[0]['isi']);
        $this->assertNotEmpty($isi[0]['id']);
    }

    public function test_menit_baca_minimal_satu(): void
    {
        $this->assertSame(1, RichText::menitBaca('pendek saja'));
        $this->assertSame(3, RichText::menitBaca(str_repeat('kata ', 500)));
    }
}
