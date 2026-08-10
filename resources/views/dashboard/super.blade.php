<x-layouts.app title="Dashboard Platform" subtitle="Super Admin">
    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.stat label="Sekolah" :value="$stats['sekolah']"
                   icon="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        <x-ui.stat label="Total Pengguna" :value="$stats['user']" color="green"
                   icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
    </div>

    <x-ui.card padding="p-0">
        <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
            <h3 class="text-sm font-bold text-ink-900">Sekolah terdaftar</h3>
            <a href="{{ route('superadmin.sekolah.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Kelola semua</a>
        </div>
        @forelse ($sekolahList as $s)
            <div class="flex items-center justify-between gap-3 border-b border-ink-50 px-6 py-3.5 last:border-0">
                <div>
                    <div class="text-sm font-semibold text-ink-900">{{ $s->nama }}</div>
                    <div class="text-xs text-ink-500">{{ $s->users_count }} pengguna</div>
                </div>
                <x-ui.badge :color="$s->status === 'active' ? 'green' : 'rose'">{{ $s->status }}</x-ui.badge>
            </div>
        @empty
            <x-ui.empty title="Belum ada sekolah">
                Sekolah dibuat otomatis saat guru pertama mendaftar.
            </x-ui.empty>
        @endforelse
    </x-ui.card>
</x-layouts.app>
