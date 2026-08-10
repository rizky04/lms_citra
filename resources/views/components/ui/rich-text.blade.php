@props(['text', 'gambar' => [], 'bolehUnggah' => false, 'materi' => null])

@php
    $blok = \App\Support\RichText::blok($text);
    $nomorGambar = 0;
@endphp

<div {{ $attributes->merge(['class' => 'space-y-5']) }}>
    @foreach ($blok as $b)
        @switch($b['tipe'])

            @case('gambar')
                @php
                    $idx = $nomorGambar++;
                    $path = $gambar[$idx] ?? null;
                @endphp

                <figure class="overflow-hidden rounded-2xl border border-ink-200 bg-ink-50">
                    @if ($path)
                        <img src="{{ Storage::url($path) }}" alt="{{ $b['isi'] }}"
                             class="max-h-[26rem] w-full bg-white object-contain">
                    @else
                        <div class="flex flex-col items-center justify-center gap-2 px-6 py-10 text-center">
                            <svg class="h-8 w-8 text-ink-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 9h.008v.008H18V9Zm2.25 9V6a2.25 2.25 0 0 0-2.25-2.25H4.5A2.25 2.25 0 0 0 2.25 6v12A2.25 2.25 0 0 0 4.5 20.25h13.5A2.25 2.25 0 0 0 20.25 18Z" />
                            </svg>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Slot ilustrasi {{ $idx + 1 }}</p>
                            @if ($bolehUnggah && $materi)
                                <a href="{{ route('materi.edit', $materi) }}#gambar"
                                   class="text-xs font-semibold text-brand-600 hover:underline">Unggah gambar untuk slot ini →</a>
                            @endif
                        </div>
                    @endif

                    <figcaption class="border-t border-ink-200 bg-white px-4 py-2.5 text-xs leading-relaxed text-ink-500">
                        {{ $b['isi'] }}
                    </figcaption>
                </figure>
                @break

            @case('judul')
                <h2 id="{{ $b['id'] }}" class="scroll-mt-32 border-l-4 border-brand-500 pl-3 pt-2
                                                text-lg font-extrabold tracking-tight text-ink-900">
                    {{ $b['isi'] }}
                </h2>
                @break

            @case('daftar')
                <{{ $b['urut'] ? 'ol' : 'ul' }} class="space-y-2.5">
                    @foreach ($b['isi'] as $i => $item)
                        <li class="flex gap-3">
                            @if ($b['urut'])
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg
                                             bg-brand-50 text-xs font-bold text-brand-700">{{ $i + 1 }}</span>
                            @else
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-400"></span>
                            @endif
                            <span class="text-[15px] leading-relaxed text-ink-700">{{ $item }}</span>
                        </li>
                    @endforeach
                </{{ $b['urut'] ? 'ol' : 'ul' }}>
                @break

            @case('kode')
                <pre class="scroll-slim overflow-x-auto rounded-xl bg-ink-900 p-4 text-[13px] leading-relaxed text-ink-100"><code>{{ $b['isi'] }}</code></pre>
                @break

            @default
                <p class="text-[15px] leading-[1.85] text-ink-700">{{ $b['isi'] }}</p>
        @endswitch
    @endforeach

    @if (empty($blok))
        <p class="text-sm text-ink-400">Materi ini belum ada isinya.</p>
    @endif
</div>
