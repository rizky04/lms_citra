@php
    $blok = \App\Support\RichText::blok($teks ?? null);
    $petaGambar = $gambar ?? [];
    $nomorGambar = 0;
@endphp

{{-- Isi dokumen untuk PDF. Sengaja dirender sebagai HTML terstruktur, BUKAN
     teks polos: kombinasi white-space:pre-line + text-align:justify membuat
     dompdf merenggangkan spasi tiap baris sebelum pemutus baris. --}}
@forelse ($blok as $b)
    @switch($b['tipe'])
        @case('judul')
            <h2 class="bab">{{ $b['isi'] }}</h2>
            @break

        @case('daftar')
            <{{ $b['urut'] ? 'ol' : 'ul' }} class="daftar">
                @foreach ($b['isi'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </{{ $b['urut'] ? 'ol' : 'ul' }}>
            @break

        @case('kode')
            <pre class="kode">{{ $b['isi'] }}</pre>
            @break

        @case('gambar')
            @php
                $idx = $nomorGambar++;
                // dompdf butuh path berkas nyata, bukan URL.
                $berkas = isset($petaGambar[$idx])
                    ? storage_path('app/public/'.$petaGambar[$idx])
                    : null;
            @endphp
            <div class="gambar">
                @if ($berkas && is_file($berkas))
                    <img src="{{ $berkas }}" alt="">
                @endif
                <div class="keterangan">Gambar {{ $idx + 1 }}: {{ $b['isi'] }}</div>
            </div>
            @break

        @default
            <p>{{ $b['isi'] }}</p>
    @endswitch
@empty
    <p class="kosong">— dokumen belum ada isinya —</p>
@endforelse
