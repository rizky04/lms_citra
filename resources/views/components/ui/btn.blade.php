@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition
             disabled:cursor-not-allowed disabled:opacity-50';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-3 text-sm',
    ];

    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-lift hover:bg-brand-700 active:bg-brand-800',
        'secondary' => 'border border-ink-200 bg-white text-ink-700 shadow-soft hover:bg-ink-50',
        'ghost' => 'text-ink-600 hover:bg-ink-100 hover:text-ink-900',
        'danger' => 'bg-rose-600 text-white shadow-soft hover:bg-rose-700',
        'soft' => 'bg-brand-50 text-brand-700 hover:bg-brand-100',
    ];

    $classes = $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
