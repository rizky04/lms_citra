<x-layouts.app title="Perangkat Pembelajaran" subtitle="Modul Ajar, Prota, Prosem, ATP, KKTP — siap cetak">
    <x-slot:actions>
        <x-ui.btn size="sm" variant="secondary" :href="route('ai.index')">Buat dengan AI</x-ui.btn>
        <x-ui.btn size="sm" :href="route('perangkat.create')">+ Dokumen baru</x-ui.btn>
    </x-slot:actions>

    <x-ui.page-hero title="Perangkat Pembelajaran" tone="brand"
        subtitle="Modul Ajar, Prota, Prosem, ATP, dan KKTP — susun sendiri atau minta AI, lalu cetak PDF berkop sekolah."
        icon="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
        :meta="[['label' => $perangkat->total().' dokumen tersimpan']]" />

    <x-ui.card padding="p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.field label="Jenis dokumen" class="min-w-[14rem]">
                <select name="jenis" class="field">
                    <option value="">Semua jenis</option>
                    @foreach ($jenisList as $k => $label)
                        <option value="{{ $k }}" @selected(request('jenis') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.btn variant="secondary">Filter</x-ui.btn>
            @if (request('jenis'))
                <x-ui.btn variant="ghost" :href="route('perangkat.index')">Reset</x-ui.btn>
            @endif
        </form>
    </x-ui.card>

    @if ($perangkat->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada dokumen"
                        icon="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z">
                Susun Modul Ajar, Prota, atau Prosem — bisa ditulis sendiri atau dibuatkan AI, lalu dicetak PDF.
                <x-slot:action>
                    <x-ui.btn :href="route('ai.index')" size="sm">Buat dengan AI</x-ui.btn>
                </x-slot:action>
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($perangkat as $p)
                <x-ui.card class="flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('perangkat.show', $p) }}"
                           class="text-base font-bold leading-snug text-ink-900 hover:text-brand-600">{{ $p->judul }}</a>
                        <x-ui.badge :color="$p->status === 'published' ? 'green' : 'slate'">{{ $p->status }}</x-ui.badge>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <x-ui.badge color="brand">{{ $jenisList[$p->jenis] ?? $p->jenis }}</x-ui.badge>
                        <x-ui.badge>{{ $p->mapel?->nama }}</x-ui.badge>
                        @if ($p->semester)<x-ui.badge color="amber">{{ ucfirst($p->semester) }}</x-ui.badge>@endif
                        @if ($p->sumber === 'ai_generated')<x-ui.badge color="green">AI</x-ui.badge>@endif
                    </div>

                    <div class="mt-5 flex items-center gap-2 border-t border-ink-100 pt-4">
                        <x-ui.btn size="sm" variant="soft" :href="route('perangkat.pdf', $p)" class="flex-1">Unduh PDF</x-ui.btn>
                        <x-ui.btn size="sm" variant="ghost" :href="route('perangkat.edit', $p)">Edit</x-ui.btn>
                        <form action="{{ route('perangkat.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg p-2 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div>{{ $perangkat->links() }}</div>
    @endif
</x-layouts.app>
