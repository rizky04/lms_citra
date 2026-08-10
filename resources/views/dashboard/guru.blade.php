<x-layouts.app title="Dashboard" :subtitle="$user->sekolah?->nama">
    {{-- Sapaan --}}
    <div class="relative overflow-hidden rounded-2xl bg-ink-900 p-6 sm:p-8">
        <div class="absolute -right-10 -top-16 h-56 w-56 rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="relative">
            <p class="text-sm text-brand-300">{{ $user->labelRole() }}</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                Halo, {{ $user->name }} 👋
            </h2>
            <p class="mt-2 max-w-lg text-sm text-ink-300">
                Kelola bank soal dan rakit kuis untuk kelasmu. Semua tersimpan rapi per jenjang.
            </p>
            <div class="mt-6 flex flex-wrap gap-2">
                <x-ui.btn :href="route('soal.create')" size="sm">+ Soal baru</x-ui.btn>
                <x-ui.btn :href="route('kuis.create')" size="sm" variant="secondary">Rakit kuis</x-ui.btn>
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Bank Soal" :value="$stats['soal']" :href="route('soal.index')"
                   icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
        <x-ui.stat label="Kuis" :value="$stats['kuis']" :href="route('kuis.index')" color="green"
                   icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25m0 0h-3.375c-.621 0-1.125-.504-1.125-1.125V9.375c0-.621.504-1.125 1.125-1.125H8.25" />
        <x-ui.stat label="Kelas" :value="$stats['kelas']" :href="route('kelas.index')" color="amber"
                   icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        <x-ui.stat label="Siswa" :value="$stats['siswa']" color="rose"
                   icon="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Kuis terbaru --}}
        <x-ui.card padding="p-0" class="lg:col-span-2">
            <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Kuis terbaru</h3>
                <a href="{{ route('kuis.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat semua</a>
            </div>

            @forelse ($kuisTerbaru as $k)
                <a href="{{ route('kuis.show', $k) }}"
                   class="flex items-center gap-4 border-b border-ink-50 px-6 py-3.5 last:border-0 hover:bg-ink-50/60">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-xs font-bold text-brand-700">
                        {{ $k->soal_count }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-ink-900">{{ $k->judul }}</div>
                        <div class="text-xs text-ink-500">{{ $k->kelas->nama }} · {{ $k->soal_count }} soal</div>
                    </div>
                    <x-ui.badge :color="$k->status === 'published' ? 'green' : 'slate'">{{ $k->status }}</x-ui.badge>
                </a>
            @empty
                <x-ui.empty title="Belum ada kuis"
                            icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25">
                    Rakit kuis pertamamu dari bank soal.
                    <x-slot:action>
                        <x-ui.btn :href="route('kuis.create')" size="sm">Rakit kuis</x-ui.btn>
                    </x-slot:action>
                </x-ui.empty>
            @endforelse
        </x-ui.card>

        {{-- Kelas --}}
        <x-ui.card padding="p-0">
            <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Kelas</h3>
                <a href="{{ route('kelas.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Kelola</a>
            </div>

            @forelse ($kelasList as $k)
                <a href="{{ route('kelas.show', $k) }}"
                   class="flex items-center justify-between gap-3 border-b border-ink-50 px-6 py-3.5 last:border-0 hover:bg-ink-50/60">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-ink-900">{{ $k->nama }}</div>
                        <div class="font-mono text-[11px] text-ink-400">{{ $k->kode_undangan }}</div>
                    </div>
                    <x-ui.badge>{{ $k->siswa_count }} siswa</x-ui.badge>
                </a>
            @empty
                <x-ui.empty title="Belum ada kelas">
                    Buat kelas untuk mulai mengundang siswa.
                    <x-slot:action>
                        <x-ui.btn :href="route('kelas.index')" size="sm">Buat kelas</x-ui.btn>
                    </x-slot:action>
                </x-ui.empty>
            @endforelse
        </x-ui.card>
    </div>
</x-layouts.app>
