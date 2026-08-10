@props(['padding' => 'p-6'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-ink-200/70 bg-white shadow-card '.$padding]) }}>
    {{ $slot }}
</div>
