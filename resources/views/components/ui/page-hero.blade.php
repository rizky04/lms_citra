@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'tone' => 'dark',   // dark | brand | emerald | amber
    'meta' => [],       // ['5 kelas', '12 siswa'] atau [['label'=>..,'icon'=>..], ..]
])

@php
    $tones = [
        'dark' => ['bg-ink-900', 'bg-brand-600/25', 'text-brand-300', 'text-ink-300'],
        'brand' => ['bg-gradient-to-br from-brand-600 to-brand-800', 'bg-white/10', 'text-brand-200', 'text-brand-100'],
        'emerald' => ['bg-gradient-to-br from-emerald-600 to-emerald-800', 'bg-white/10', 'text-emerald-200', 'text-emerald-100'],
        'amber' => ['bg-gradient-to-br from-amber-500 to-orange-700', 'bg-white/10', 'text-amber-100', 'text-amber-50'],
    ];
    [$latar, $blur, $warnaIkon, $warnaMeta] = $tones[$tone] ?? $tones['dark'];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl p-6 sm:p-8 '.$latar]) }}>
    <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full blur-3xl {{ $blur }}"></div>
    <div class="absolute -bottom-24 -left-10 h-56 w-56 rounded-full blur-3xl {{ $blur }}"></div>

    {{-- basis: judul minta ~20rem, jadi blok aksi turun ke baris bawah kalau sempit
         (tanpa ini judul tergencet dan patah jadi banyak baris). --}}
    <div class="relative flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 flex-1 basis-80">
            @if ($icon)
                <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 {{ $warnaIkon }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                    </svg>
                </span>
            @endif

            <h2 class="text-2xl font-extrabold leading-tight tracking-tight text-white sm:text-3xl">{{ $title }}</h2>

            @if ($subtitle)
                <p class="mt-2 max-w-xl text-sm leading-relaxed {{ $warnaMeta }}">{{ $subtitle }}</p>
            @endif

            @if ($meta)
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm {{ $warnaMeta }}">
                    @foreach ($meta as $m)
                        @php $teks = is_array($m) ? $m['label'] : $m; $ikon = is_array($m) ? ($m['icon'] ?? null) : null; @endphp
                        <span class="flex items-center gap-1.5">
                            @if ($ikon)
                                <svg class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
                                </svg>
                            @endif
                            {{ $teks }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @isset($action)
            <div class="flex shrink-0 flex-wrap gap-2">{{ $action }}</div>
        @endisset
    </div>
</div>
