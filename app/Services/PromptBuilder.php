<?php

namespace App\Services;

// Menyusun prompt + skema JSON per jenis generate. Bahasa & kedalaman
// menyesuaikan jenjang — itu inti kenapa hasilnya layak dipakai di kelas.
class PromptBuilder
{
    private const GAYA_JENJANG = [
        'SD' => 'siswa Sekolah Dasar (usia 7-12). Gunakan kalimat pendek dan sederhana, '
            .'hindari istilah teknis asing, pakai analogi benda sehari-hari. '
            .'Fokus pengenalan: mengenal perangkat, berpikir runtut, keamanan berinternet dasar.',
        'SMP' => 'siswa SMP (usia 13-15). Bahasa lugas, boleh mulai memakai istilah teknis '
            .'asal langsung dijelaskan. Fokus: algoritma dasar, berpikir komputasional, '
            .'aplikasi perkantoran, etika digital.',
        'SMA' => 'siswa SMA (usia 16-18). Bahasa formal-akademis, boleh konsep abstrak. '
            .'Fokus: pemrograman, struktur data dasar, basis data, jaringan, analisis data.',
        'SMK' => 'siswa SMK jurusan komputer (RPL/TKJ/DKV). Bahasa teknis-praktis dan '
            .'berorientasi dunia kerja. Sertakan konteks kasus nyata/industri. '
            .'Fokus: pemrograman aplikatif, basis data, jaringan, framework, tools industri.',
    ];

    private function gaya(string $jenjang): string
    {
        return self::GAYA_JENJANG[$jenjang] ?? self::GAYA_JENJANG['SMP'];
    }

    // --- SOAL ---

    public function soal(array $p): array
    {
        $tipeInstruksi = match ($p['tipe']) {
            'pg' => 'Setiap soal pilihan ganda dengan TEPAT 4 opsi (A, B, C, D) dan tepat satu jawaban benar. '
                .'Opsi pengecoh harus masuk akal, jangan asal salah.',
            'esai' => 'Setiap soal berupa pertanyaan uraian yang menuntut penjelasan. '
                .'Kosongkan opsi dan jawaban_benar.',
            'praktik' => 'Setiap soal berupa tugas praktik/unjuk kerja yang bisa dikerjakan di komputer. '
                .'Kosongkan opsi dan jawaban_benar.',
        };

        $prompt = <<<TXT
        Kamu guru Informatika berpengalaman di Indonesia yang menyusun soal ujian.

        Buat {$p['jumlah']} soal mata pelajaran "{$p['mapel']}" dengan topik "{$p['topik']}".

        Sasaran: {$this->gaya($p['jenjang'])}
        Tingkat kesulitan: {$p['tingkat']}.
        {$tipeInstruksi}

        Aturan wajib:
        - Tulis dalam Bahasa Indonesia yang benar dan baku.
        - Soal harus sesuai Kurikulum Merdeka jenjang {$p['jenjang']}.
        - Jangan mengulang soal yang mirip satu sama lain.
        - Jangan memakai nomor urut di dalam teks pertanyaan.
        - Isi "pembahasan" dengan penjelasan singkat mengapa jawaban itu benar.
        TXT;

        // Skema disusun per tipe: field opsi & kunci hanya ada untuk PG.
        // (Gemini menolak enum yang memuat string kosong, jadi jangan "kosongkan" field —
        // hilangkan saja fieldnya.)
        $properti = ['pertanyaan' => ['type' => 'STRING']];
        $wajib = ['pertanyaan', 'pembahasan'];

        if ($p['tipe'] === 'pg') {
            $properti += [
                'opsi_a' => ['type' => 'STRING'],
                'opsi_b' => ['type' => 'STRING'],
                'opsi_c' => ['type' => 'STRING'],
                'opsi_d' => ['type' => 'STRING'],
                'jawaban_benar' => ['type' => 'STRING', 'enum' => ['A', 'B', 'C', 'D']],
            ];
            $wajib = ['pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'jawaban_benar', 'pembahasan'];
        }

        $properti['pembahasan'] = ['type' => 'STRING'];

        $skema = [
            'type' => 'OBJECT',
            'properties' => [
                'soal' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => $properti,
                        'required' => $wajib,
                    ],
                ],
            ],
            'required' => ['soal'],
        ];

        return [$prompt, $skema];
    }

    // --- MATERI ---

    public function materi(array $p): array
    {
        // Gemini tidak menghasilkan gambar di sini; yang diminta adalah PENANDA
        // tempat ilustrasi + deskripsinya, lalu guru mengunggah gambarnya.
        $instruksiGambar = ($p['sertakan_gambar'] ?? false)
            ? "\n        - Sisipkan 3-5 penanda ilustrasi pada bagian yang paling terbantu oleh gambar.\n"
                ."          Format penanda PERSIS seperti ini, berdiri sendiri di satu baris:\n"
                ."          [GAMBAR: deskripsi jelas gambar yang dibutuhkan]\n"
                ."          Contoh: [GAMBAR: Diagram bagian-bagian komputer dengan label monitor, keyboard, mouse, dan CPU]\n"
                ."          Jangan membuat gambar ASCII atau tautan gambar — cukup penanda itu saja.\n"
            : '';

        $prompt = <<<TXT
        Kamu guru Informatika berpengalaman di Indonesia yang menyusun bahan ajar.

        Tulis materi pembelajaran mata pelajaran "{$p['mapel']}" dengan topik "{$p['topik']}".

        Sasaran: {$this->gaya($p['jenjang'])}

        Struktur isi materi:
        1. Pengantar singkat yang mengaitkan topik dengan kehidupan sehari-hari siswa.
        2. Penjelasan konsep inti, dipecah dalam sub-bagian berjudul.
        3. Contoh konkret (untuk topik pemrograman, sertakan potongan kode sederhana).
        4. Rangkuman poin penting.
        5. Latihan mandiri 3 butir di akhir (tanpa kunci jawaban).

        Aturan wajib:
        - Bahasa Indonesia baku, sesuai Kurikulum Merdeka jenjang {$p['jenjang']}.
        - Tulis sebagai teks biasa, JANGAN pakai sintaks Markdown seperti ** atau ##.
        - Setiap judul bagian ditulis di barisnya sendiri, lalu SATU BARIS KOSONG sebelum isinya.
          Antar bagian juga dipisah satu baris kosong.
        - Panjang memadai untuk satu pertemuan (kira-kira 500-900 kata).{$instruksiGambar}
        TXT;

        $skema = [
            'type' => 'OBJECT',
            'properties' => [
                'judul' => ['type' => 'STRING'],
                'konten' => ['type' => 'STRING'],
            ],
            'required' => ['judul', 'konten'],
        ];

        return [$prompt, $skema];
    }

    // --- PERANGKAT PEMBELAJARAN ---

    public function perangkat(array $p): array
    {
        $jenisLabel = \App\Models\PerangkatPembelajaran::JENIS[$p['jenis']] ?? $p['jenis'];

        $strukturPerJenis = match ($p['jenis']) {
            'modul_ajar' => 'Informasi Umum (identitas, kompetensi awal, profil pelajar Pancasila, sarana prasarana, '
                .'target peserta didik, model pembelajaran); Komponen Inti (tujuan pembelajaran, pemahaman bermakna, '
                .'pertanyaan pemantik, kegiatan pembelajaran pendahuluan-inti-penutup lengkap dengan alokasi menit, '
                .'asesmen, pengayaan dan remedial); Lampiran (LKPD, bahan bacaan, glosarium, daftar pustaka).',
            'prota' => 'Tabel program tahunan: semester, alur tujuan pembelajaran/materi pokok, dan alokasi jam pelajaran '
                .'untuk satu tahun ajaran penuh. Total jam harus realistis.',
            'prosem' => 'Program semester: rincian materi per bulan dan per pekan efektif dalam satu semester, '
                .'termasuk pekan untuk penilaian tengah dan akhir semester.',
            'atp_silabus' => 'Alur Tujuan Pembelajaran: capaian pembelajaran per elemen, tujuan pembelajaran yang '
                .'diturunkan darinya, alur/urutan logis, perkiraan alokasi waktu, dan kata kunci tiap tujuan.',
            'kktp' => 'Kriteria Ketercapaian Tujuan Pembelajaran: tiap tujuan pembelajaran diberi kriteria dengan '
                .'empat tingkat capaian (Belum Berkembang, Mulai Berkembang, Berkembang Sesuai Harapan, Sangat Berkembang).',
            default => 'Susun sesuai format dokumen yang lazim dipakai guru di Indonesia.',
        };

        $prompt = <<<TXT
        Kamu guru Informatika di Indonesia yang menyusun perangkat pembelajaran administratif
        untuk dikumpulkan ke kepala sekolah dan pengawas.

        Susun dokumen "{$jenisLabel}" untuk mata pelajaran "{$p['mapel']}",
        jenjang {$p['jenjang']}, topik/lingkup "{$p['topik']}",
        tahun ajaran {$p['tahun_ajaran']}, semester {$p['semester']}.

        Sasaran peserta didik: {$this->gaya($p['jenjang'])}

        Struktur yang harus ada: {$strukturPerJenis}

        Aturan wajib:
        - Ikuti kaidah Kurikulum Merdeka.
        - Bahasa Indonesia baku dan formal.
        - Tulis sebagai teks biasa dengan judul bagian bernomor, JANGAN pakai sintaks Markdown seperti ** atau ##.
        - Setiap judul bagian ditulis di barisnya sendiri, lalu SATU BARIS KOSONG sebelum isinya.
          Antar bagian juga dipisah satu baris kosong.
        - Isi harus konkret dan siap pakai, bukan template kosong berisi placeholder.
        TXT;

        $skema = [
            'type' => 'OBJECT',
            'properties' => [
                'judul' => ['type' => 'STRING'],
                'konten' => ['type' => 'STRING'],
            ],
            'required' => ['judul', 'konten'],
        ];

        return [$prompt, $skema];
    }
}
