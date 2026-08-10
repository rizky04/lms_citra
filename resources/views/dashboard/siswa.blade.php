<x-layouts.app title="Dashboard" :subtitle="$user->sekolah?->nama">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-6 sm:p-8">
        <div class="absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative">
            <p class="text-sm text-brand-200">Siswa</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                Halo, {{ $user->name }} 👋
            </h2>
            <p class="mt-2 text-sm text-brand-100">
                {{ $kuisTersedia->count() ? 'Ada '.$kuisTersedia->count().' kuis menunggu dikerjakan.' : 'Belum ada kuis baru untukmu.' }}
            </p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card padding="p-0" class="lg:col-span-2">
            <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Kuis tersedia</h3>
                <a href="{{ route('kerjakan.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat semua</a>
            </div>

            @forelse ($kuisTersedia as $k)
                <div class="flex items-center gap-4 border-b border-ink-50 px-6 py-4 last:border-0">
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-ink-900">{{ $k->judul }}</div>
                        <div class="text-xs text-ink-500">
                            {{ $k->kelas->nama }} · {{ $k->soal_count }} soal
                            @if ($k->durasi_menit) · {{ $k->durasi_menit }} menit @endif
                        </div>
                    </div>
                    <x-ui.btn size="sm" :href="route('kerjakan.show', $k)">Kerjakan</x-ui.btn>
                </div>
            @empty
                <x-ui.empty title="Belum ada kuis"
                            icon="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z">
                    Kuis akan muncul di sini setelah gurumu mempublikasikannya.
                </x-ui.empty>
            @endforelse
        </x-ui.card>

        <x-ui.card padding="p-0">
            <div class="border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Kelas saya</h3>
            </div>
            @forelse ($kelasSaya as $k)
                <div class="flex items-center justify-between gap-3 border-b border-ink-50 px-6 py-3.5 last:border-0">
                    <span class="truncate text-sm font-medium text-ink-800">{{ $k->nama }}</span>
                    <x-ui.badge color="brand">{{ $k->jenjang->nama }}</x-ui.badge>
                </div>
            @empty
                <x-ui.empty title="Belum gabung kelas">
                    Minta kode kelas ke gurumu.
                </x-ui.empty>
            @endforelse
        </x-ui.card>
    </div>
</x-layouts.app>
