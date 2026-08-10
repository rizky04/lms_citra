@props(['label', 'value', 'icon' => null, 'href' => null, 'color' => 'brand'])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'group block rounded-2xl border border-ink-200/70 bg-white p-5 shadow-card transition '.($href ? 'hover:-translate-y-0.5 hover:shadow-lift' : '')]) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-xs font-medium uppercase tracking-wide text-ink-400">{{ $label }}</div>
            <div class="mt-1.5 text-3xl font-extrabold tracking-tight text-ink-900">{{ $value }}</div>
        </div>
        @if ($icon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$color] ?? $tones['brand'] }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                </svg>
            </span>
        @endif
    </div>
</{{ $tag }}>
