<x-layouts.app title="Kelola Sekolah" subtitle="Super Admin">
    <x-ui.page-hero title="Kelola Sekolah" tone="dark"
        subtitle="Pantau seluruh sekolah di platform. Suspend menonaktifkan akses login semua penggunanya."
        icon="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
        :meta="[['label' => $sekolah->total().' sekolah terdaftar']]" />

    <x-ui.card padding="p-0">
        <div class="divide-y divide-ink-50">
            @forelse ($sekolah as $s)
                <div class="flex flex-wrap items-center gap-4 px-5 py-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold
                                 {{ $s->status === 'active' ? 'bg-brand-50 text-brand-700' : 'bg-rose-50 text-rose-600' }}">
                        {{ Str::upper(Str::substr($s->nama, 0, 2)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="truncate text-sm font-bold text-ink-900">{{ $s->nama }}</span>
                            <x-ui.badge :color="$s->status === 'active' ? 'green' : 'rose'">{{ $s->status }}</x-ui.badge>
                        </div>
                        <div class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-ink-500">
                            <span>{{ $s->guru_count }} guru</span>
                            <span>{{ $s->siswa_count }} siswa</span>
                            <span>dibuat {{ $s->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('superadmin.sekolah.toggle', $s) }}"
                          onsubmit="return confirm('{{ $s->status === 'active' ? 'Suspend' : 'Aktifkan' }} sekolah {{ $s->nama }}?')">
                        @csrf
                        <x-ui.btn size="sm" :variant="$s->status === 'active' ? 'danger' : 'primary'">
                            {{ $s->status === 'active' ? 'Suspend' : 'Aktifkan' }}
                        </x-ui.btn>
                    </form>
                </div>
            @empty
                <x-ui.empty title="Belum ada sekolah">
                    Sekolah dibuat otomatis saat guru pertama mendaftar.
                </x-ui.empty>
            @endforelse
        </div>

        @if ($sekolah->hasPages())
            <div class="border-t border-ink-100 p-4">{{ $sekolah->links() }}</div>
        @endif
    </x-ui.card>
</x-layouts.app>
