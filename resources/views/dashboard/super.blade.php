<x-layouts.app title="Dashboard Platform" subtitle="Super Admin">
    <x-ui.page-hero title="Statistik Platform" tone="dark"
        subtitle="Ringkasan seluruh sekolah, pengguna, dan aktivitas di platform."
        icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"
        :meta="array_values(array_filter([
            ['label' => $stats['sekolah'].' sekolah ('.$stats['sekolah_aktif'].' aktif)'],
            $stats['pending'] ? ['label' => $stats['pending'].' guru menunggu approval'] : null,
            ['label' => $stats['ai_hari_ini'].' generate AI hari ini'],
        ]))" />

    {{-- KPI platform --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Sekolah" :value="$stats['sekolah']" :href="route('superadmin.sekolah.index')"
                   icon="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        <x-ui.stat label="Guru & Admin" :value="$stats['guru']" :href="route('superadmin.pengguna.index', ['role' => 'guru'])" color="green"
                   icon="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
        <x-ui.stat label="Siswa" :value="$stats['siswa']" :href="route('superadmin.pengguna.index', ['role' => 'siswa'])" color="amber"
                   icon="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        <x-ui.stat label="Guru Pending" :value="$stats['pending']" :href="route('superadmin.pengguna.index', ['status' => 'pending'])" color="rose"
                   icon="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
    </div>

    {{-- Konten platform --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Total Soal" :value="$stats['soal']"
                   icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
        <x-ui.stat label="Total Kuis" :value="$stats['kuis']"
                   icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25" />
        <x-ui.stat label="Total Materi" :value="$stats['materi']"
                   icon="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        <x-ui.stat label="Total Tugas" :value="$stats['tugas']"
                   icon="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18" />
    </div>

    {{-- Sekolah paling aktif --}}
    <x-ui.section title="Sekolah paling aktif" desc="Berdasarkan jumlah siswa terdaftar." padding="p-0"
                  icon="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15">
        <x-slot:action>
            <a href="{{ route('superadmin.sekolah.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Kelola semua</a>
        </x-slot:action>

        @forelse ($sekolahAktif as $s)
            <div class="flex items-center justify-between gap-3 border-b border-ink-50 px-6 py-3.5 last:border-0">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-xs font-bold text-brand-700">
                        {{ Str::upper(Str::substr($s->nama, 0, 2)) }}
                    </span>
                    <div class="text-sm font-semibold text-ink-900">{{ $s->nama }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <x-ui.badge>{{ $s->siswa_count }} siswa</x-ui.badge>
                    <x-ui.badge :color="$s->status === 'active' ? 'green' : 'rose'">{{ $s->status }}</x-ui.badge>
                </div>
            </div>
        @empty
            <x-ui.empty title="Belum ada sekolah">
                Sekolah dibuat otomatis saat guru pertama mendaftar.
            </x-ui.empty>
        @endforelse
    </x-ui.section>
</x-layouts.app>
