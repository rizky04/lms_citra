<x-layouts.app :title="'Kelas '.$kelas->nama" :subtitle="$kelas->jenjang->nama">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('kelas.index')">← Semua kelas</x-ui.btn>
    </x-slot:actions>

    <x-ui.page-hero :title="'Kelas '.$kelas->nama" tone="dark"
        icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"
        :meta="[
            ['label' => $kelas->jenjang->nama],
            ['label' => $kelas->siswa->count().' siswa'],
            ['label' => 'dibuat '.$kelas->created_at->translatedFormat('d M Y')],
        ]" />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="h-fit">
            <h3 class="text-sm font-bold text-ink-900">Kode undangan</h3>
            <p class="mt-1 text-sm text-ink-500">Bagikan kode ini agar siswa bisa gabung saat mendaftar.</p>

            <div class="mt-4 rounded-xl border-2 border-dashed border-brand-200 bg-brand-50 px-4 py-5 text-center">
                <div class="font-mono text-2xl font-extrabold tracking-[0.2em] text-brand-700">{{ $kelas->kode_undangan }}</div>
            </div>

            <ol class="mt-4 space-y-1.5 text-xs text-ink-500">
                <li>1. Siswa buka halaman <span class="font-medium text-ink-700">Daftar</span></li>
                <li>2. Pilih peran <span class="font-medium text-ink-700">Siswa</span></li>
                <li>3. Masukkan kode di atas</li>
            </ol>
        </x-ui.card>

        <x-ui.card padding="p-0" class="lg:col-span-2">
            <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Siswa</h3>
                <x-ui.badge>{{ $kelas->siswa->count() }} orang</x-ui.badge>
            </div>

            @forelse ($kelas->siswa as $s)
                <div class="flex items-center gap-3 border-b border-ink-50 px-6 py-3.5 last:border-0">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-ink-100 text-xs font-bold text-ink-600">
                        {{ Str::upper(Str::substr($s->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-ink-900">{{ $s->name }}</div>
                        <div class="truncate text-xs text-ink-500">{{ $s->email }}</div>
                    </div>
                </div>
            @empty
                <x-ui.empty title="Belum ada siswa"
                            icon="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z">
                    Bagikan kode undangan di samping supaya siswa bisa bergabung.
                </x-ui.empty>
            @endforelse
        </x-ui.card>
    </div>
</x-layouts.app>
