@props(['title' => null, 'desc' => null, 'icon' => null, 'padding' => 'p-6'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-ink-200/70 bg-white shadow-card']) }}>
    @if ($title)
        <div class="flex items-start gap-3 border-b border-ink-100 px-6 py-4">
            @if ($icon)
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                    </svg>
                </span>
            @endif
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-ink-900">{{ $title }}</h3>
                @if ($desc)<p class="mt-0.5 text-xs leading-relaxed text-ink-500">{{ $desc }}</p>@endif
            </div>
            @isset($action)
                <div class="shrink-0">{{ $action }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding }}">{{ $slot }}</div>
</div>
