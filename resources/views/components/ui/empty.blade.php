@props(['title' => 'Belum ada data', 'icon' => 'M12 4.5v15m7.5-7.5h-15'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-500">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </div>
    <h3 class="text-sm font-semibold text-ink-800">{{ $title }}</h3>
    @if (trim($slot))
        <p class="mt-1 max-w-sm text-sm text-ink-500">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
