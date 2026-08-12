@props(['title' => null, 'subtitle' => null])

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div x-data="{ sidebar: false }" class="min-h-full lg:flex">

    {{-- Overlay mobile --}}
    <div x-cloak x-show="sidebar" x-transition.opacity @click="sidebar = false"
         class="fixed inset-0 z-30 bg-ink-900/50 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside x-cloak
           :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-ink-900 transition-transform duration-200 lg:static lg:translate-x-0">
        @include('partials.sidebar')
    </aside>

    {{-- Kolom konten --}}
    <div class="flex min-w-0 flex-1 flex-col">
        @include('partials.banner-impersonasi')

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 border-b border-ink-200/80 bg-white/85 backdrop-blur">
            <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                <button @click="sidebar = true" class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 lg:hidden"
                        aria-label="Buka menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    @if ($title)
                        <h1 class="truncate text-base font-bold text-ink-900 sm:text-lg">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="truncate text-xs text-ink-500">{{ $subtitle }}</p>
                        @endif
                    @endif
                </div>

                @isset($actions)
                    <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
                @endisset

                @include('partials.lonceng')
                @include('partials.user-menu')
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl space-y-6">
                @if (session('status'))
                    <x-ui.alert type="success">{{ session('status') }}</x-ui.alert>
                @endif
                @if ($errors->any() && ! $errors->has('_form_only'))
                    @foreach ($errors->getBag('default')->get('publish') as $m)
                        <x-ui.alert type="error">{{ $m }}</x-ui.alert>
                    @endforeach
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</div>

@stack('skrip')
</body>
</html>
