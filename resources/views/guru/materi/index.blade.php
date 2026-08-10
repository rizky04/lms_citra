<x-layouts.app title="Materi" subtitle="Bahan ajar untuk kelasmu">
    <x-slot:actions>
        <x-ui.btn size="sm" :href="route('materi.create')">+ Materi baru</x-ui.btn>
    </x-slot:actions>

    <x-ui.page-hero title="Materi" tone="dark"
        subtitle="Tulis bahan ajar, unggah file yang sudah ada, atau minta AI menyusun draftnya."
        icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
        :meta="[['label' => $materi->total().' materi']]">
        <x-slot:action>
            <x-ui.btn variant="secondary" size="sm" :href="route('ai.index')">Buat dengan AI</x-ui.btn>
        </x-slot:action>
    </x-ui.page-hero>

    <x-ui.card padding="p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.field label="Cari judul" class="min-w-[14rem] flex-1">
                <input type="text" name="q" value="{{ request('q') }}" class="field" placeholder="mis. Algoritma">
            </x-ui.field>
            <x-ui.field label="Status" class="w-44">
                <select name="status" class="field">
                    <option value="">Semua</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                </select>
            </x-ui.field>
            <x-ui.btn variant="secondary">Filter</x-ui.btn>
            @if (request()->hasAny(['q', 'status']))
                <x-ui.btn variant="ghost" :href="route('materi.index')">Reset</x-ui.btn>
            @endif
        </form>
    </x-ui.card>

    @if ($materi->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada materi"
                        icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25">
                Tulis materi atau unggah file bahan ajar yang sudah kamu punya.
                <x-slot:action>
                    <x-ui.btn :href="route('materi.create')" size="sm">+ Materi baru</x-ui.btn>
                </x-slot:action>
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($materi as $m)
                <x-ui.card class="flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('materi.show', $m) }}"
                           class="text-base font-bold leading-snug text-ink-900 hover:text-brand-600">{{ $m->judul }}</a>
                        <x-ui.badge :color="$m->status === 'published' ? 'green' : 'slate'">{{ $m->status }}</x-ui.badge>
                    </div>

                    @if ($m->konten)
                        <p class="mt-2 line-clamp-2 text-sm text-ink-500">{{ Str::limit(strip_tags($m->konten), 110) }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <x-ui.badge>{{ $m->mapel->nama }}</x-ui.badge>
                        <x-ui.badge color="brand">{{ $m->mapel->jenjang->nama }}</x-ui.badge>
                        @if ($m->kelas)<x-ui.badge color="amber">{{ $m->kelas->nama }}</x-ui.badge>@endif
                        @if ($m->file_path)<x-ui.badge color="green">ada lampiran</x-ui.badge>@endif
                    </div>

                    <div class="mt-5 flex items-center gap-2 border-t border-ink-100 pt-4">
                        <x-ui.btn size="sm" variant="soft" :href="route('materi.show', $m)" class="flex-1">Lihat</x-ui.btn>
                        <x-ui.btn size="sm" variant="ghost" :href="route('materi.edit', $m)">Edit</x-ui.btn>
                        <form action="{{ route('materi.destroy', $m) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg p-2 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.2v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div>{{ $materi->links() }}</div>
    @endif
</x-layouts.app>
