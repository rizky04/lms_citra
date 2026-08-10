<x-layouts.app title="Laporan" subtitle="Rekap nilai dan soal yang paling sering salah">
    @if (! $kelas)
        <x-ui.card>
            <x-ui.empty title="Belum ada kelas"
                        icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z">
                Buat kelas dan kuis dulu, laporan akan muncul otomatis.
                <x-slot:action>
                    <x-ui.btn :href="route('kelas.index')" size="sm">Buat kelas</x-ui.btn>
                </x-slot:action>
            </x-ui.empty>
        </x-ui.card>
    @else
        @php
            $adaNilai = $rekap->filter(fn ($r) => $r['rataKuis'] !== null);
            $rataKelas = $adaNilai->count() ? round($adaNilai->avg(fn ($r) => $r['rataKuis'])) : null;
            $tuntas = $adaNilai->filter(fn ($r) => $r['rataKuis'] >= 75)->count();
        @endphp

        <x-ui.page-hero :title="'Laporan · '.$kelas->nama" tone="dark"
            subtitle="Rekap nilai kuis dan tugas, serta soal yang paling sering dijawab salah."
            icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"
            :meta="array_values(array_filter([
                ['label' => $rekap->count().' siswa'],
                ['label' => $kuisList->count().' kuis dipublish'],
                $rataKelas !== null ? ['label' => 'Rata-rata kelas '.$rataKelas] : null,
                $rataKelas !== null ? ['label' => $tuntas.' siswa ≥ 75'] : null,
            ]))" />

        {{-- Pilih kelas --}}
        <x-ui.card padding="p-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <x-ui.field label="Kelas" class="min-w-[14rem]">
                    <select name="kelas_id" class="field" onchange="this.form.submit()">
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected($kelas->id === $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.btn variant="secondary">Tampilkan</x-ui.btn>
            </form>
        </x-ui.card>

        {{-- Rekap nilai --}}
        <x-ui.card padding="p-0">
            <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Rekap nilai — {{ $kelas->nama }}</h3>
                <x-ui.badge>{{ $rekap->count() }} siswa</x-ui.badge>
            </div>

            @if ($rekap->isEmpty())
                <x-ui.empty title="Belum ada siswa di kelas ini" />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500">
                            <tr>
                                <th class="whitespace-nowrap px-5 py-3 font-semibold">Siswa</th>
                                @foreach ($kuisList as $k)
                                    <th class="whitespace-nowrap px-3 py-3 text-center font-semibold" title="{{ $k->judul }}">
                                        {{ Str::limit($k->judul, 14) }}
                                    </th>
                                @endforeach
                                <th class="whitespace-nowrap px-3 py-3 text-center font-semibold">Rata Kuis</th>
                                <th class="whitespace-nowrap px-3 py-3 text-center font-semibold">Rata Tugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-50">
                            @foreach ($rekap as $r)
                                <tr class="hover:bg-ink-50/50">
                                    <td class="whitespace-nowrap px-5 py-3 font-medium text-ink-900">{{ $r['siswa']->name }}</td>

                                    @foreach ($kuisList as $k)
                                        @php $n = $r['perKuis'][$k->id] ?? null; @endphp
                                        <td class="px-3 py-3 text-center">
                                            @if ($n === null)
                                                <span class="text-ink-300">—</span>
                                            @else
                                                <span @class([
                                                    'inline-block rounded-md px-2 py-0.5 text-xs font-semibold',
                                                    'bg-emerald-50 text-emerald-700' => $n >= 75,
                                                    'bg-amber-50 text-amber-700' => $n >= 50 && $n < 75,
                                                    'bg-rose-50 text-rose-700' => $n < 50,
                                                ])>{{ $n }}</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-3 py-3 text-center font-bold text-ink-900">
                                        {{ $r['rataKuis'] ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-ink-900">
                                        {{ $r['rataTugas'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>

        {{-- Soal tersulit --}}
        <x-ui.card padding="p-0">
            <div class="border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Soal paling sering dijawab salah</h3>
                <p class="mt-0.5 text-xs text-ink-500">Indikasi materi yang perlu diulang di kelas.</p>
            </div>

            @forelse ($soalSulit as $s)
                @php $maks = $soalSulit->max('salah') ?: 1; @endphp
                <div class="border-b border-ink-50 px-5 py-3.5 last:border-0">
                    <div class="flex items-start justify-between gap-4">
                        <p class="min-w-0 flex-1 text-sm text-ink-800">{{ Str::limit(strip_tags($s->soal->pertanyaan), 100) }}</p>
                        <span class="shrink-0 text-xs font-bold text-rose-600">{{ $s->salah }}× salah</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-ink-100">
                        <div class="h-full rounded-full bg-rose-400" style="width: {{ round($s->salah / $maks * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <x-ui.empty title="Belum ada data jawaban"
                            icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z">
                    Statistik muncul setelah siswa mengerjakan kuis.
                </x-ui.empty>
            @endforelse
        </x-ui.card>
    @endif
</x-layouts.app>
