@props(['title' => null, 'heading' => null, 'sub' => null])

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
<div class="flex min-h-full">

    {{-- Panel brand (desktop) --}}
    <div class="relative hidden w-1/2 overflow-hidden bg-ink-900 lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-brand-400/20 blur-3xl"></div>

        <div class="relative p-10">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                    </svg>
                </span>
                <span class="text-lg font-extrabold tracking-tight text-white">LMS Citra</span>
            </a>
        </div>

        <div class="relative px-10 pb-4">
            <h2 class="max-w-md text-3xl font-extrabold leading-tight text-white">
                Kelola kelas, materi, dan soal Informatika di satu tempat.
            </h2>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-ink-300">
                Dari SD sampai SMK. Susun bank soal, rakit kuis, nilai otomatis —
                dan biarkan AI membantu menyiapkan draft materi & soal.
            </p>

            <ul class="mt-8 space-y-3">
                @foreach ([
                    'Bank soal lintas jenjang & mata pelajaran',
                    'Kuis dengan penilaian otomatis',
                    'Draft materi & soal dibantu AI',
                ] as $fitur)
                    <li class="flex items-center gap-3 text-sm text-ink-200">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-500/20">
                            <svg class="h-3.5 w-3.5 text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </span>
                        {{ $fitur }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="relative p-10 text-xs text-ink-500">
            © {{ date('Y') }} LMS Citra
        </div>
    </div>

    {{-- Panel form --}}
    <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16">
        <div class="mx-auto w-full max-w-md">
            <a href="{{ route('landing') }}" class="mb-8 inline-flex items-center gap-2 lg:hidden">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                    </svg>
                </span>
                <span class="font-extrabold tracking-tight text-ink-900">LMS Citra</span>
            </a>

            @if ($heading)
                <h1 class="text-2xl font-extrabold tracking-tight text-ink-900">{{ $heading }}</h1>
                @if ($sub)
                    <p class="mt-1.5 text-sm text-ink-500">{{ $sub }}</p>
                @endif
            @endif

            @if (session('status'))
                <x-ui.alert type="info" class="mt-6">{{ session('status') }}</x-ui.alert>
            @endif

            <div class="mt-8">{{ $slot }}</div>
        </div>
    </div>
</div>
</body>
</html>
