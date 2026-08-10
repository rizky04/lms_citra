@props(['type' => 'success'])

@php
    $styles = [
        'success' => ['bg-emerald-50 text-emerald-800 ring-emerald-200', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'error' => ['bg-rose-50 text-rose-800 ring-rose-200', 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z'],
        'info' => ['bg-brand-50 text-brand-800 ring-brand-200', 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z'],
    ];
    [$class, $path] = $styles[$type] ?? $styles['success'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl px-4 py-3 text-sm ring-1 ring-inset animate-fade-up '.$class]) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
    </svg>
    <div>{{ $slot }}</div>
</div>
