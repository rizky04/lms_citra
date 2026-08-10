@props(['color' => 'slate'])

@php
    $colors = [
        'slate' => 'bg-ink-100 text-ink-600 ring-ink-200',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-200',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '
        .($colors[$color] ?? $colors['slate']),
]) }}>{{ $slot }}</span>
