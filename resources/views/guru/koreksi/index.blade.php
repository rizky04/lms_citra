<x-layouts.app title="Koreksi" subtitle="Jawaban esai & praktik yang menunggu penilaian">
    <x-ui.page-hero title="Koreksi" :tone="$kuis->isEmpty() ? 'emerald' : 'amber'"
        subtitle="Soal pilihan ganda sudah dinilai otomatis. Yang muncul di sini hanya esai dan praktik."
        icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        :meta="[
            ['label' => $kuis->isEmpty() ? 'Tidak ada antrean koreksi' : $kuis->sum('perlu_koreksi_count').' jawaban menunggu dinilai'],
        ]" />

    @if ($kuis->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Tidak ada yang perlu dikoreksi"
                        icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z">
                Semua jawaban esai/praktik sudah dinilai. Soal pilihan ganda dinilai otomatis.
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($kuis as $k)
                <x-ui.card class="flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('koreksi.show', $k) }}"
                           class="text-base font-bold leading-snug text-ink-900 hover:text-brand-600">{{ $k->judul }}</a>
                        <x-ui.badge color="amber">{{ $k->perlu_koreksi_count }}</x-ui.badge>
                    </div>
                    <p class="mt-1 text-xs text-ink-500">{{ $k->kelas->nama }}</p>

                    <div class="mt-5 border-t border-ink-100 pt-4">
                        <x-ui.btn size="sm" :href="route('koreksi.show', $k)" class="w-full">Koreksi sekarang</x-ui.btn>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
