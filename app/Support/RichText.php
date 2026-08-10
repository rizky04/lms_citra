<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Mengubah teks polos (hasil ketik guru atau generate AI) menjadi blok
 * terstruktur: judul bagian, paragraf, daftar, dan kode — supaya bisa
 * dirender dengan tipografi yang enak dibaca, bukan satu blok datar.
 */
class RichText
{
    /** @return array<int, array{tipe:string, isi:mixed, id?:string, urut?:bool}> */
    public static function blok(?string $teks): array
    {
        $teks = trim((string) $teks);

        if ($teks === '') {
            return [];
        }

        $teks = str_replace(["\r\n", "\r"], "\n", $teks);
        $teks = self::perbaikiTeksMenempel($teks);

        $blok = [];

        foreach (preg_split('/\n\s*\n+/', $teks) as $potongan) {
            $potongan = trim($potongan, "\n");

            if (trim($potongan) === '') {
                continue;
            }

            foreach (self::pisahDaftar($potongan) as $bagian) {
                $blok[] = $bagian;
            }
        }

        return $blok;
    }

    /** Deskripsi tiap slot gambar, terurut sesuai kemunculan di teks. */
    public static function slotGambar(?string $teks): array
    {
        return array_values(array_map(
            fn ($b) => $b['isi'],
            array_filter(self::blok($teks), fn ($b) => $b['tipe'] === 'gambar')
        ));
    }

    /** Hanya judul bagian — untuk daftar isi. */
    public static function daftarIsi(?string $teks): array
    {
        return array_values(array_filter(
            self::blok($teks),
            fn ($b) => $b['tipe'] === 'judul'
        ));
    }

    // Perkiraan waktu baca (200 kata/menit, minimal 1 menit).
    public static function menitBaca(?string $teks): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $teks)) / 200));
    }

    /**
     * Sebagian materi lama tersimpan sebagai satu baris panjang tanpa newline
     * sehingga kalimat & penomoran saling menempel ("...favorit!Layar Komputer").
     * Perbaikan ini SENGAJA hanya jalan pada teks yang memang rusak seperti itu,
     * supaya teks yang sudah rapi tidak ikut diubah.
     */
    private static function perbaikiTeksMenempel(string $teks): string
    {
        $rusak = substr_count($teks, "\n") <= 2 && mb_strlen($teks) > 600;

        if (! $rusak) {
            return $teks;
        }

        // Pulihkan judul bagian yang tertelan, mis. "…favorit!Layar Komputer (Monitor)Layar komputer itu…"
        // atau "…dan aman.Latihan Mandiri1. Aku adalah…".
        // Cirinya: rangkaian Kata Berkapital yang langsung diapit akhir kalimat (atau awal teks)
        // dan huruf kapital/angka TANPA spasi — prosa yang benar selalu memberi spasi,
        // jadi batas itu pasti hilang. Dijalankan sebelum pemisahan penomoran.
        // Satu "kata judul" boleh bertanda hubung: Bagian-Bagian, Sehari-hari.
        $kata = '\p{Lu}\p{Ll}+(?:-\p{Lu}?\p{Ll}+)*';
        $pola = '/(?:(?<=[\.\!\?])|^)'
            ."({$kata}(?:[ ](?:\p{Lu}?\p{Ll}+|{$kata})){0,6}(?:[ ]?\([^)]{1,24}\))?[\?]?)"
            .'(?=\p{Lu}|\d+[\.\)]\s)/u';
        $teks = preg_replace($pola, "\n\n$1\n\n", $teks);

        // "kartun:1. Kalian" / "penting.1. Layar" → penomoran pindah ke baris sendiri
        $teks = preg_replace('/(?<=[\p{Ll}\)\!\?\.\:])(?=\d+[\.\)]\s+\p{Lu})/u', "\n", $teks);

        // "selesai.Kalimat" → beri spasi setelah akhir kalimat yang hilang spasinya
        $teks = preg_replace('/(?<=[\p{Ll}\)])([\.\!\?])(?=\p{Lu})/u', '$1 ', $teks);

        return $teks;
    }

    /** Pecah satu potongan menjadi runtun paragraf / daftar / kode / judul. */
    private static function pisahDaftar(string $potongan): array
    {
        $baris = explode("\n", $potongan);

        // Blok kode: seluruh baris menjorok, atau dipagari ```
        if (self::terlihatKode($baris)) {
            return [[
                'tipe' => 'kode',
                'isi' => self::bersihkanPagarKode($baris),
            ]];
        }

        // "1. Tujuan Pembelajaran" (judul bab) vs "1. Nyalakan perangkat." (item daftar):
        // item daftar hampir selalu punya saudara bernomor di blok yang sama.
        $jumlahBernomor = count(array_filter($baris, fn ($b) => preg_match('/^\s*\d+[\.\)]\s+/u', $b)));
        $bernomorTunggal = $jumlahBernomor === 1;

        $hasil = [];
        $bufferTeks = [];
        $bufferDaftar = [];
        $urut = false;

        $buangBuffer = function () use (&$hasil, &$bufferTeks, &$bufferDaftar, &$urut) {
            if ($bufferTeks) {
                $teks = trim(implode(' ', $bufferTeks));
                if ($teks !== '') {
                    $hasil[] = self::judulAtauParagraf($teks, count($bufferTeks));
                }
                $bufferTeks = [];
            }
            if ($bufferDaftar) {
                $hasil[] = ['tipe' => 'daftar', 'isi' => $bufferDaftar, 'urut' => $urut];
                $bufferDaftar = [];
            }
        };

        foreach ($baris as $b) {
            $b = rtrim($b);

            if (trim($b) === '') {
                continue;
            }

            // Penanda ilustrasi dari AI: [GAMBAR: deskripsi]
            if (preg_match('/^\s*\[GAMBAR:\s*(.+?)\s*\]\s*$/ui', $b, $m)) {
                $buangBuffer();
                $hasil[] = ['tipe' => 'gambar', 'isi' => trim($m[1])];

                continue;
            }

            if (preg_match('/^\s*(\d+)[\.\)]\s+(.+)$/u', $b, $m)) {
                // Bernomor tunggal dan berbentuk judul → perlakukan sebagai judul bab.
                if ($bernomorTunggal && self::terlihatJudul(trim($b))) {
                    $buangBuffer();
                    $hasil[] = self::judulAtauParagraf(trim($b), 1);

                    continue;
                }

                if ($bufferTeks) {
                    $buangBuffer();
                }
                $urut = true;
                $bufferDaftar[] = trim($m[2]);

                continue;
            }

            if (preg_match('/^\s*[-•*]\s+(.+)$/u', $b, $m)) {
                if ($bufferTeks) {
                    $buangBuffer();
                }
                $urut = false;
                $bufferDaftar[] = trim($m[1]);

                continue;
            }

            if ($bufferDaftar) {
                $buangBuffer();
            }

            $bufferTeks[] = trim($b);
        }

        $buangBuffer();

        return $hasil;
    }

    private static function judulAtauParagraf(string $teks, int $jumlahBaris): array
    {
        if ($jumlahBaris === 1 && self::terlihatJudul($teks)) {
            return [
                'tipe' => 'judul',
                'isi' => rtrim($teks, ':'),
                'id' => 'bab-'.Str::slug(Str::limit($teks, 50, '')),
            ];
        }

        return ['tipe' => 'paragraf', 'isi' => $teks];
    }

    /**
     * Judul bagian: baris pendek yang berdiri sendiri dan tidak diakhiri titik.
     * Tanda tanya diizinkan ("Bagaimana Komputer Bekerja?"), titik tidak.
     */
    private static function terlihatJudul(string $teks): bool
    {
        $panjang = mb_strlen($teks);

        if ($panjang > 90 || $panjang < 3) {
            return false;
        }

        // Penomoran bab: "A. INFORMASI UMUM", "1. Tujuan Pembelajaran", "B. Komponen Inti"
        if (preg_match('/^([A-Z]|\d+)[\.\)]\s+\p{Lu}[^.!]{2,70}$/u', $teks)) {
            return true;
        }

        if (preg_match('/[\.\!]$/u', $teks)) {
            return false;
        }

        // Diawali huruf kapital & bukan kalimat panjang
        return (bool) preg_match('/^\p{Lu}/u', $teks) && str_word_count($teks) <= 12;
    }

    private static function terlihatKode(array $baris): bool
    {
        $isi = array_values(array_filter($baris, fn ($b) => trim($b) !== ''));

        if (! $isi) {
            return false;
        }

        if (str_starts_with(trim($isi[0]), '```')) {
            return true;
        }

        $menjorok = array_filter($isi, fn ($b) => preg_match('/^(\t| {4})/', $b));

        return count($menjorok) === count($isi);
    }

    private static function bersihkanPagarKode(array $baris): string
    {
        $isi = array_filter($baris, fn ($b) => ! str_starts_with(trim($b), '```'));

        // Buang indentasi seragam supaya kode tidak menjorok berlebihan
        $isi = array_map(fn ($b) => preg_replace('/^(\t| {4})/', '', $b), $isi);

        return trim(implode("\n", $isi), "\n");
    }
}
