<x-layouts.app title="Materi" subtitle="Bahan belajar dari gurumu">
    <x-ui.page-hero title="Materi" tone="brand"
        subtitle="Semua bahan belajar dari gurumu, tersusun rapi dan bisa dibaca kapan saja."
        icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
        :meta="[['label' => $materi->total().' materi']]" />

    @if ($materi->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada materi"
                        icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25">
                Materi akan muncul di sini setelah gurumu mempublikasikannya.
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($materi as $m)
                <a href="{{ route('materi.baca.show', $m) }}"
                   class="group flex flex-col rounded-2xl border border-ink-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-lift">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold leading-snug text-ink-900 group-hover:text-brand-600">{{ $m->judul }}</h3>
                            <p class="mt-0.5 text-xs text-ink-500">{{ $m->mapel->nama }}</p>
                        </div>
                    </div>

                    @if ($m->konten)
                        <p class="mt-3 line-clamp-2 text-sm text-ink-500">{{ Str::limit(strip_tags($m->konten), 100) }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @if ($m->kelas)<x-ui.badge color="amber">{{ $m->kelas->nama }}</x-ui.badge>@endif
                        @if ($m->file_path)<x-ui.badge color="green">ada lampiran</x-ui.badge>@endif
                    </div>
                </a>
            @endforeach
        </div>

        <div>{{ $materi->links() }}</div>
    @endif
</x-layouts.app>
