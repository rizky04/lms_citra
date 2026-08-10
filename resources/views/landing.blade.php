<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — LMS Guru Informatika</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white">

<header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 shadow-lift">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
            </svg>
        </span>
        <span class="text-lg font-extrabold tracking-tight text-ink-900">LMS Citra</span>
    </div>

    <nav class="flex items-center gap-2">
        @auth
            <x-ui.btn :href="route('dashboard')">Ke Dashboard</x-ui.btn>
        @else
            <x-ui.btn variant="ghost" :href="route('login')">Masuk</x-ui.btn>
            <x-ui.btn :href="route('register')">Daftar</x-ui.btn>
        @endauth
    </nav>
</header>

<main>
    <section class="relative overflow-hidden px-6 py-20 sm:py-28">
        <div class="absolute -top-40 left-1/2 h-96 w-[42rem] -translate-x-1/2 rounded-full bg-brand-100/60 blur-3xl"></div>

        <div class="relative mx-auto max-w-3xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-200">
                Untuk guru Informatika · SD sampai SMK
            </span>

            <h1 class="mt-6 text-4xl font-extrabold leading-[1.1] tracking-tight text-ink-900 sm:text-6xl">
                Siapkan kelas Informatika
                <span class="bg-gradient-to-r from-brand-500 to-brand-700 bg-clip-text text-transparent">tanpa lembur</span>
            </h1>

            <p class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-ink-500 sm:text-lg">
                Satu platform untuk kelas, bank soal, kuis dengan penilaian otomatis,
                dan perangkat pembelajaran — dengan bantuan AI untuk menyusun draft.
            </p>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <x-ui.btn size="lg" :href="route('register')">Mulai gratis</x-ui.btn>
                <x-ui.btn size="lg" variant="secondary" :href="route('login')">Saya sudah punya akun</x-ui.btn>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-24">
        <div class="grid gap-5 sm:grid-cols-3">
            @foreach ([
                ['Bank soal terorganisir', 'Simpan soal PG, esai, dan praktik per jenjang, mapel, dan bab. Pakai ulang kapan saja.', 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
                ['Kuis nilai otomatis', 'Rakit kuis manual atau acak dari bank soal. Pilihan ganda dinilai seketika.', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Dibantu AI', 'Draft materi, soal, dan perangkat ajar dibuat AI — kamu tinggal review dan revisi.', 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z'],
            ] as [$judul, $ket, $path])
                <x-ui.card>
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-ink-900">{{ $judul }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-500">{{ $ket }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </section>
</main>

<footer class="border-t border-ink-200 py-8 text-center text-xs text-ink-400">
    © {{ date('Y') }} LMS Citra
</footer>

</body>
</html>
