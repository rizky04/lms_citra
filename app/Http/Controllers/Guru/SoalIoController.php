<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use App\Models\Mapel;
use App\Models\Soal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Import/export bank soal lewat CSV. Sengaja CSV, bukan .xlsx: fungsi CSV
 * ada di PHP inti (fgetcsv/fputcsv) dan Excel membukanya langsung.
 * ponytail: kalau guru butuh unggah .xlsx apa adanya, tambah maatwebsite/excel.
 */
class SoalIoController extends Controller
{
    private const HEADER = [
        'tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d',
        'jawaban_benar', 'bobot', 'tingkat', 'tag',
    ];

    public function form(): View
    {
        return view('guru.soal.io', [
            'jenjangList' => Jenjang::orderBy('id')->get(),
            'mapelList' => Mapel::orderBy('nama')->get(),
        ]);
    }

    // Unduh template kosong berisi contoh baris.
    public function template(): StreamedResponse
    {
        return $this->kirimCsv('template-bank-soal.csv', [
            ['pg', 'Perintah SQL untuk mengambil data adalah?', 'INSERT', 'SELECT', 'UPDATE', 'DELETE', 'B', 2, 'mudah', 'Basis Data'],
            ['esai', 'Jelaskan perbedaan array dan linked list.', '', '', '', '', '', 5, 'sedang', 'Struktur Data'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $baris = Soal::with(['mapel', 'jenjang'])
            ->when($request->tipe, fn ($q, $t) => $q->where('tipe', $t))
            ->latest()->get()
            ->map(fn (Soal $s) => [
                $s->tipe,
                $s->pertanyaan,
                $s->opsi_json['A'] ?? '',
                $s->opsi_json['B'] ?? '',
                $s->opsi_json['C'] ?? '',
                $s->opsi_json['D'] ?? '',
                $s->jawaban_benar ?? '',
                $s->bobot,
                $s->tingkat ?? '',
                $s->tag ?? '',
            ])->all();

        return $this->kirimCsv('bank-soal-'.now()->format('Ymd-Hi').'.csv', $baris);
    }

    public function import(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'berkas' => ['required', 'file', 'max:5120', 'mimes:csv,txt'],
            'jenjang_id' => ['required', 'exists:jenjangs,id'],
            'mapel_nama' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $mapel = Mapel::firstOrCreate([
            'jenjang_id' => $v['jenjang_id'],
            'nama' => trim($v['mapel_nama']),
        ]);

        $handle = fopen($request->file('berkas')->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['berkas' => 'Berkas tidak bisa dibaca.']);
        }

        $header = fgetcsv($handle);
        // Toleransi BOM dari Excel supaya kolom pertama tetap terbaca.
        if ($header) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header ?: []);

        if (array_diff(['tipe', 'pertanyaan'], $header)) {
            fclose($handle);

            return back()->withErrors([
                'berkas' => 'Header CSV tidak sesuai. Minimal harus ada kolom "tipe" dan "pertanyaan". Unduh template sebagai acuan.',
            ]);
        }

        $masuk = 0;
        $dilewati = [];
        $nomor = 1;

        DB::transaction(function () use ($handle, $header, $mapel, $v, $request, &$masuk, &$dilewati, &$nomor) {
            while (($data = fgetcsv($handle)) !== false) {
                $nomor++;

                if (count(array_filter($data, fn ($c) => trim((string) $c) !== '')) === 0) {
                    continue; // baris kosong
                }

                $row = array_combine(
                    array_slice($header, 0, count($data)),
                    array_slice($data, 0, count($header))
                ) ?: [];

                $tipe = strtolower(trim($row['tipe'] ?? ''));
                $pertanyaan = trim($row['pertanyaan'] ?? '');

                if (! in_array($tipe, ['pg', 'esai', 'praktik'], true) || $pertanyaan === '') {
                    $dilewati[] = "baris {$nomor}: tipe/pertanyaan tidak valid";
                    continue;
                }

                $opsi = null;
                $kunci = null;

                if ($tipe === 'pg') {
                    $opsi = array_filter([
                        'A' => trim($row['opsi_a'] ?? ''),
                        'B' => trim($row['opsi_b'] ?? ''),
                        'C' => trim($row['opsi_c'] ?? ''),
                        'D' => trim($row['opsi_d'] ?? ''),
                    ], fn ($t) => $t !== '');
                    $kunci = strtoupper(trim($row['jawaban_benar'] ?? ''));

                    if (count($opsi) < 2 || ! array_key_exists($kunci, $opsi)) {
                        $dilewati[] = "baris {$nomor}: opsi PG kurang atau kunci jawaban tidak cocok";
                        continue;
                    }
                }

                $tingkat = strtolower(trim($row['tingkat'] ?? ''));

                Soal::create([
                    'mapel_id' => $mapel->id,
                    'jenjang_id' => $v['jenjang_id'],
                    'guru_id' => $request->user()->id,
                    'tipe' => $tipe,
                    'pertanyaan' => $pertanyaan,
                    'opsi_json' => $opsi,
                    'jawaban_benar' => $kunci,
                    'bobot' => max(1, (int) ($row['bobot'] ?? 1)),
                    'tingkat' => in_array($tingkat, ['mudah', 'sedang', 'sulit'], true) ? $tingkat : null,
                    'tag' => trim($row['tag'] ?? '') ?: null,
                    'status' => $v['status'],
                    'sumber' => 'import',
                ]);
                $masuk++;
            }
        });

        fclose($handle);

        $pesan = "{$masuk} soal berhasil diimpor.";
        if ($dilewati) {
            $pesan .= ' '.count($dilewati).' baris dilewati: '.implode('; ', array_slice($dilewati, 0, 5));
            if (count($dilewati) > 5) {
                $pesan .= '; …';
            }
        }

        return redirect()->route('soal.index')->with('status', $pesan);
    }

    private function kirimCsv(string $namaFile, array $baris): StreamedResponse
    {
        return response()->streamDownload(function () use ($baris) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM: biar Excel baca UTF-8 dengan benar
            fputcsv($out, self::HEADER);
            foreach ($baris as $b) {
                fputcsv($out, $b);
            }
            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
