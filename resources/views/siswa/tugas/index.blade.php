<x-layouts.app title="Tugas" subtitle="Tugas dari gurumu">
    @php
        $belum = $tugas->filter(fn ($t) => ! $submisiSaya->has($t->id))->count();
        $dinilai = $submisiSaya->filter(fn ($s) => $s->nilai !== null)->count();
    @endphp

    <x-ui.page-hero title="Tugas" :tone="$belum > 0 ? 'amber' : 'emerald'"
        :subtitle="$belum > 0
            ? 'Ada '.$belum.' tugas yang belum kamu kerjakan. Yuk diselesaikan.'
            : 'Semua tugas sudah kamu kumpulkan. Kerja bagus!'"
        icon="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18"
        :meta="[
            ['label' => $tugas->total().' tugas'],
            ['label' => $dinilai.' sudah dinilai'],
        ]" />

    @if ($tugas->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada tugas"
                        icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25">
                Tugas akan muncul di sini setelah gurumu memberikannya.
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($tugas as $t)
                @php $s = $submisiSaya->get($t->id); @endphp
                <x-ui.card class="flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('tugas.saya.show', $t) }}"
                           class="text-base font-bold leading-snug text-ink-900 hover:text-brand-600">{{ $t->judul }}</a>

                        @if ($s && $s->nilai !== null)
                            <x-ui.badge color="green">nilai {{ rtrim(rtrim($s->nilai, '0'), '.') }}</x-ui.badge>
                        @elseif ($s)
                            <x-ui.badge color="brand">terkumpul</x-ui.badge>
                        @elseif ($t->deadline && $t->deadline->isPast())
                            <x-ui.badge color="rose">terlewat</x-ui.badge>
                        @else
                            <x-ui.badge color="amber">belum</x-ui.badge>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <x-ui.badge>{{ $t->kelas->nama }}</x-ui.badge>
                        @if ($t->deadline)
                            <x-ui.badge :color="$t->deadline->isPast() ? 'rose' : 'slate'">
                                Tenggat {{ $t->deadline->translatedFormat('d M, H:i') }}
                            </x-ui.badge>
                        @endif
                    </div>

                    <div class="mt-5 border-t border-ink-100 pt-4">
                        <x-ui.btn size="sm" :href="route('tugas.saya.show', $t)" class="w-full"
                                  :variant="$s ? 'secondary' : 'primary'">
                            {{ $s ? 'Lihat pekerjaan' : 'Kerjakan' }}
                        </x-ui.btn>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div>{{ $tugas->links() }}</div>
    @endif
</x-layouts.app>
