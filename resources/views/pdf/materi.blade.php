<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $materi->judul }}</title>
    <style>
        @page { margin: 2.5cm 2cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.6; color: #1e293b; }

        .kop { border-bottom: 2px solid #4f46e5; padding-bottom: 8px; margin-bottom: 18px; }
        .kop .sekolah { font-size: 9pt; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        h1 { font-size: 16pt; margin: 6px 0 4px; }
        .meta { font-size: 9pt; color: #64748b; }

        /* Rata kiri, BUKAN justify: dompdf merenggangkan spasi berlebihan
           pada baris pendek sehingga teks terlihat berantakan. */
        p { text-align: left; margin: 0 0 10px; }
        h2.bab { font-size: 12pt; margin: 18px 0 8px; padding-left: 8px;
                 border-left: 3px solid #4f46e5; page-break-after: avoid; }
        ul.daftar, ol.daftar { margin: 0 0 10px; padding-left: 20px; }
        ul.daftar li, ol.daftar li { margin-bottom: 5px; }
        pre.kode { background: #f1f5f9; border-left: 3px solid #94a3b8; padding: 8px 10px;
                   font-size: 9.5pt; white-space: pre-wrap; margin: 0 0 10px; }
        .kosong { color: #94a3b8; font-style: italic; }
        .gambar { margin: 0 0 12px; text-align: center; page-break-inside: avoid; }
        .gambar img { max-width: 100%; max-height: 9cm; }
        .gambar .keterangan { font-size: 9pt; color: #64748b; margin-top: 4px; }

        .kaki { position: fixed; bottom: -1.5cm; left: 0; right: 0;
                text-align: center; font-size: 8pt; color: #94a3b8; }
    </style>
</head>
<body>

<div class="kop">
    <div class="sekolah">{{ $materi->sekolah->nama }}</div>
    <h1>{{ $materi->judul }}</h1>
    <div class="meta">
        {{ $materi->mapel->nama }} · {{ $materi->mapel->jenjang->nama }}
        @if ($materi->kelas) · Kelas {{ $materi->kelas->nama }} @endif
        · Disusun oleh {{ $materi->guru->name }}
    </div>
</div>

@include('pdf.partials.isi', ['teks' => $materi->konten, 'gambar' => $materi->gambar ?? []])

<div class="kaki">Dicetak dari LMS Citra · {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
