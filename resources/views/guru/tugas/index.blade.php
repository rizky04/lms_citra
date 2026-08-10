<x-layouts.app title="Tugas" subtitle="Beri tugas dan nilai pekerjaan siswa">
    <x-slot:actions>
        <x-ui.btn size="sm" :href="route('tugas.create')">+ Tugas baru</x-ui.btn>
    </x-slot:actions>

    <x-ui.page-hero title="Tugas" tone="dark"
        subtitle="Beri tugas ke kelas, siswa mengumpulkan teks atau berkas, lalu kamu menilainya."
        icon="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18"
        :meta="[
            ['label' => $tugas->total().' tugas'],
            ['label' => $tugas->sum('belum_dinilai_count').' pekerjaan menunggu dinilai'],
        ]" />

    @if ($tugas->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada tugas"
                        icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25">
                Buat tugas untuk kelasmu — siswa bisa mengumpulkan teks atau file.
                <x-slot:action>
                    <x-ui.btn :href="route('tugas.create')" size="sm">+ Tugas baru</x-ui.btn>
                </x-slot:action>
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($tugas as $t)
                <x-ui.card class="flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('tugas.show', $t) }}"
                           class="text-base font-bold leading-snug text-ink-900 hover:text-brand-600">{{ $t->judul }}</a>
                        @if ($t->belum_dinilai_count > 0)
                            <x-ui.badge color="amber">{{ $t->belum_dinilai_count }} belum dinilai</x-ui.badge>
                        @elseif ($t->submisi_count > 0)
                            <x-ui.badge color="green">selesai dinilai</x-ui.badge>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <x-ui.badge color="brand">{{ $t->kelas->nama }}</x-ui.badge>
                        <x-ui.badge>{{ $t->submisi_count }} terkumpul</x-ui.badge>
                        @if ($t->deadline)
                            <x-ui.badge :color="$t->deadline->isPast() ? 'rose' : 'slate'">
                                {{ $t->deadline->isPast() ? 'lewat' : 'tenggat' }} {{ $t->deadline->format('d M H:i') }}
                            </x-ui.badge>
                        @endif
                    </div>

                    <div class="mt-5 flex items-center gap-2 border-t border-ink-100 pt-4">
                        <x-ui.btn size="sm" variant="soft" :href="route('tugas.show', $t)" class="flex-1">Lihat submisi</x-ui.btn>
                        <x-ui.btn size="sm" variant="ghost" :href="route('tugas.edit', $t)">Edit</x-ui.btn>
                        <form action="{{ route('tugas.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus tugas ini beserta submisinya?')">
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

        <div>{{ $tugas->links() }}</div>
    @endif
</x-layouts.app>
