<x-layouts.app title="Notifikasi">
    <x-slot:actions>
        @if (auth()->user()->unreadNotifications->isNotEmpty())
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                @csrf
                <x-ui.btn size="sm" variant="secondary">Tandai semua terbaca</x-ui.btn>
            </form>
        @endif
    </x-slot:actions>

    @php $belum = auth()->user()->unreadNotifications->count(); @endphp

    <div class="mx-auto w-full max-w-3xl space-y-6">
        <x-ui.page-hero title="Notifikasi" :tone="$belum > 0 ? 'brand' : 'dark'"
            :subtitle="$belum > 0 ? $belum.' notifikasi belum dibaca.' : 'Semua notifikasi sudah kamu baca.'"
            icon="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />

        <x-ui.card padding="p-0">
            @forelse ($notifikasi as $n)
                @php $d = $n->data; @endphp
                <a href="{{ route('notifikasi.baca', $n->id) }}"
                   class="flex items-start gap-3 border-b border-ink-50 px-5 py-4 last:border-0 hover:bg-ink-50/60
                          {{ $n->read_at ? '' : 'bg-brand-50/40' }}">
                    <span @class([
                        'mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl',
                        'bg-emerald-50 text-emerald-600' => $d['sukses'] ?? true,
                        'bg-rose-50 text-rose-600' => ! ($d['sukses'] ?? true),
                    ])>
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="{{ ($d['tipe'] ?? '') === 'ai'
                                      ? 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z'
                                      : 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25' }}" />
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-ink-900">{{ $d['judul'] ?? 'Notifikasi' }}</div>
                        <div class="mt-0.5 text-sm text-ink-500">{{ $d['pesan'] ?? '' }}</div>
                        <div class="mt-1 text-xs text-ink-400">{{ $n->created_at->diffForHumans() }}</div>
                    </div>

                    @unless ($n->read_at)
                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-brand-500"></span>
                    @endunless
                </a>
            @empty
                <x-ui.empty title="Belum ada notifikasi"
                            icon="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0">
                    Notifikasi hasil AI, tugas baru, dan pengumuman lain akan muncul di sini.
                </x-ui.empty>
            @endforelse
        </x-ui.card>

        <div>{{ $notifikasi->links() }}</div>
    </div>
</x-layouts.app>
