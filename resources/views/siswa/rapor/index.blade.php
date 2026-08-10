<x-layouts.app title="Rapor Saya">
    <x-ui.page-hero title="Rapor Saya" tone="brand"
        subtitle="Rekap semua nilai kuis dan tugasmu dalam satu tempat."
        icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25"
        :meta="array_values(array_filter([
            $ringkas['rataKuis'] !== null ? ['label' => 'Rata-rata kuis '.$ringkas['rataKuis']] : null,
            $ringkas['rataTugas'] !== null ? ['label' => 'Rata-rata tugas '.$ringkas['rataTugas']] : null,
            ['label' => $ringkas['jumlahKuis'].' kuis · '.$ringkas['jumlahTugas'].' tugas'],
        ]))" />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Kuis --}}
        <x-ui.section title="Nilai Kuis" padding="p-0"
                      icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25">
            @forelse ($nilaiKuis as $r)
                <div class="flex items-center gap-4 border-b border-ink-50 px-5 py-3.5 last:border-0">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('kerjakan.hasil', $r['kuis']) }}"
                           class="block truncate text-sm font-semibold text-ink-900 hover:text-brand-600">{{ $r['kuis']->judul }}</a>
                        <div class="text-xs text-ink-500">{{ $r['kuis']->kelas->nama }}
                            @if ($r['menunggu']) · sebagian menunggu dinilai @endif
                        </div>
                    </div>
                    @if ($r['persen'] === null)
                        <span class="text-sm text-ink-300">—</span>
                    @else
                        <span @class([
                            'rounded-lg px-2.5 py-1 text-sm font-bold',
                            'bg-emerald-50 text-emerald-700' => $r['persen'] >= 75,
                            'bg-amber-50 text-amber-700' => $r['persen'] >= 50 && $r['persen'] < 75,
                            'bg-rose-50 text-rose-700' => $r['persen'] < 50,
                        ])>{{ $r['persen'] }}</span>
                    @endif
                </div>
            @empty
                <x-ui.empty title="Belum ada nilai kuis">Kerjakan kuis dulu, nilainya muncul di sini.</x-ui.empty>
            @endforelse
        </x-ui.section>

        {{-- Tugas --}}
        <x-ui.section title="Nilai Tugas" padding="p-0"
                      icon="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18">
            @forelse ($nilaiTugas as $s)
                <div class="flex items-center gap-4 border-b border-ink-50 px-5 py-3.5 last:border-0">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('tugas.saya.show', $s->tugas) }}"
                           class="block truncate text-sm font-semibold text-ink-900 hover:text-brand-600">{{ $s->tugas->judul }}</a>
                        <div class="text-xs text-ink-500">{{ $s->tugas->kelas->nama }}</div>
                    </div>
                    @if ($s->nilai === null)
                        <x-ui.badge color="amber">menunggu</x-ui.badge>
                    @else
                        <span @class([
                            'rounded-lg px-2.5 py-1 text-sm font-bold',
                            'bg-emerald-50 text-emerald-700' => $s->nilai >= 75,
                            'bg-amber-50 text-amber-700' => $s->nilai >= 50 && $s->nilai < 75,
                            'bg-rose-50 text-rose-700' => $s->nilai < 50,
                        ])>{{ rtrim(rtrim($s->nilai, '0'), '.') }}</span>
                    @endif
                </div>
            @empty
                <x-ui.empty title="Belum ada nilai tugas">Kumpulkan tugas dulu, nilainya muncul di sini.</x-ui.empty>
            @endforelse
        </x-ui.section>
    </div>
</x-layouts.app>
