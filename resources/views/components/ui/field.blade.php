@props(['label' => null, 'name' => null, 'hint' => null])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label class="label" @if ($name) for="{{ $name }}" @endif>{{ $label }}</label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="mt-1 text-xs text-ink-400">{{ $hint }}</p>
    @endif

    @if ($name)
        @error($name)
            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    @endif
</div>
