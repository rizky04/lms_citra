<x-layouts.app title="Kuis" subtitle="Rakit dari bank soal, publish ke kelas">
    <x-slot:actions>
        <x-ui.btn size="sm" :href="route('kuis.create')">+ Kuis baru</x-ui.btn>
    </x-slot:actions>

    <x-ui.page-hero title="Kuis" tone="dark"
        subtitle="Tarik soal dari bank, atur waktunya, publish ke kelas. Pilihan ganda dinilai otomatis."
        icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25"
        :meta="[
            ['label' => $kuis->total().' kuis'],
            ['label' => $kuis->where('status', 'published')->count().' sudah dipublish di halaman ini'],
        ]">
        <x-slot:action>
            <x-ui.btn variant="secondary" size="sm" :href="route('koreksi.index')">Koreksi</x-ui.btn>
        </x-slot:action>
    </x-ui.page-hero>

    @if ($kuis->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada kuis"
                        icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25">
                Buat kuis, tarik soal dari bank, lalu publish ke kelas.
                <x-slot:action>
                    <x-ui.btn :href="route('kuis.create')" size="sm">+ Kuis baru</x-ui.btn>
                </x-slot:action>
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($kuis as $k)
                <x-ui.card class="flex flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('kuis.show', $k) }}"
                           class="text-base font-bold leading-snug text-ink-900 hover:text-brand-600">{{ $k->judul }}</a>
                        <x-ui.badge :color="$k->status === 'published' ? 'green' : 'slate'">{{ $k->status }}</x-ui.badge>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <x-ui.badge color="brand">{{ $k->kelas->nama }}</x-ui.badge>
                        <x-ui.badge>{{ $k->soal_count }} soal</x-ui.badge>
                        @if ($k->durasi_menit)<x-ui.badge color="amber">{{ $k->durasi_menit }} mnt</x-ui.badge>@endif
                    </div>

                    <div class="mt-5 flex items-center gap-2 border-t border-ink-100 pt-4">
                        <x-ui.btn size="sm" variant="soft" :href="route('kuis.show', $k)" class="flex-1">Kelola soal</x-ui.btn>
                        <x-ui.btn size="sm" variant="ghost" :href="route('kuis.edit', $k)">Edit</x-ui.btn>
                        <form action="{{ route('kuis.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kuis ini?')">
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

        <div>{{ $kuis->links() }}</div>
    @endif
</x-layouts.app>
