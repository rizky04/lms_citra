<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $perangkat->judul }}</title>
    <style>
        @page { margin: 2.5cm 2cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.6; color: #1e293b; }

        .kop { border-bottom: 3px double #334155; padding-bottom: 10px; margin-bottom: 18px; text-align: center; }
        .kop h1 { margin: 0; font-size: 15pt; text-transform: uppercase; letter-spacing: .5px; }
        .kop p { margin: 3px 0 0; font-size: 9pt; color: #64748b; }

        h2.judul-dok { font-size: 13pt; text-align: center; margin: 0 0 4px; text-transform: uppercase; }

        table.identitas { width: 100%; margin: 14px 0 18px; font-size: 10pt; border-collapse: collapse; }
        table.identitas td { padding: 3px 0; vertical-align: top; }
        table.identitas td.k { width: 130px; color: #64748b; }
        table.identitas td.t { width: 10px; }

        /* Rata kiri, BUKAN justify: dompdf merenggangkan spasi berlebihan
           pada baris pendek sehingga teks terlihat berantakan. */
        p { text-align: left; margin: 0 0 10px; }
        h2.bab { font-size: 12pt; margin: 16px 0 8px; padding-left: 8px;
                 border-left: 3px solid #334155; page-break-after: avoid; }
        ul.daftar, ol.daftar { margin: 0 0 10px; padding-left: 20px; }
        ul.daftar li, ol.daftar li { margin-bottom: 5px; }
        pre.kode { background: #f1f5f9; border-left: 3px solid #94a3b8; padding: 8px 10px;
                   font-size: 9.5pt; white-space: pre-wrap; margin: 0 0 10px; }
        .kosong { color: #94a3b8; font-style: italic; }
        .gambar { margin: 0 0 12px; text-align: center; page-break-inside: avoid; }
        .gambar img { max-width: 100%; max-height: 9cm; }
        .gambar .keterangan { font-size: 9pt; color: #64748b; margin-top: 4px; }

        .ttd { margin-top: 36px; width: 100%; page-break-inside: avoid; }
        .ttd td { width: 50%; font-size: 10pt; vertical-align: top; }
        .ttd .nama { margin-top: 60px; font-weight: bold; text-decoration: underline; }

        .kaki { position: fixed; bottom: -1.5cm; left: 0; right: 0;
                text-align: center; font-size: 8pt; color: #94a3b8; }
    </style>
</head>
<body>

<div class="kop">
    <h1>{{ $perangkat->sekolah->nama }}</h1>
    <p>Perangkat Pembelajaran — Tahun Ajaran {{ $perangkat->tahun_ajaran ?: '-' }}</p>
</div>

<h2 class="judul-dok">{{ \App\Models\PerangkatPembelajaran::JENIS[$perangkat->jenis] ?? $perangkat->jenis }}</h2>

<table class="identitas">
    <tr><td class="k">Judul</td><td class="t">:</td><td><strong>{{ $perangkat->judul }}</strong></td></tr>
    <tr><td class="k">Mata Pelajaran</td><td class="t">:</td><td>{{ $perangkat->mapel?->nama ?? '-' }}</td></tr>
    <tr><td class="k">Jenjang</td><td class="t">:</td><td>{{ $perangkat->jenjang?->nama ?? '-' }}</td></tr>
    <tr><td class="k">Semester</td><td class="t">:</td><td>{{ ucfirst($perangkat->semester ?: '-') }}</td></tr>
    <tr><td class="k">Penyusun</td><td class="t">:</td><td>{{ $perangkat->guru->name }}</td></tr>
</table>

@include('pdf.partials.isi', ['teks' => $perangkat->konten])

<table class="ttd">
    <tr>
        <td>Mengetahui,<br>Kepala Sekolah<div class="nama">…………………………</div></td>
        <td>{{ now()->translatedFormat('d F Y') }}<br>Guru Mata Pelajaran<div class="nama">{{ $perangkat->guru->name }}</div></td>
    </tr>
</table>

<div class="kaki">Dicetak dari LMS Citra · {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
