@php
    $persen = $totalBobot > 0 ? round($nilaiPg / $totalBobot * 100) : 0;
    // Kelas ditulis utuh, bukan dirangkai string — supaya ke-scan Tailwind JIT.
    $warnaCincin = match (true) {
        $persen >= 75 => 'text-emerald-500',
        $persen >= 50 => 'text-amber-500',
        default => 'text-rose-500',
    };
@endphp

<x-layouts.app :title="'Hasil: '.$kuis->judul" :subtitle="$kuis->kelas->nama">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('kerjakan.index')">← Daftar kuis</x-ui.btn>
    </x-slot:actions>

    <div class="mx-auto w-full max-w-3xl space-y-6">
        {{-- Skor --}}
        <x-ui.card class="text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-ink-400">
                Nilai pilihan ganda · percobaan {{ $percobaanTerakhir }}
            </p>

            <div class="mt-4 flex items-center justify-center gap-6">
                {{-- Cincin skor --}}
                <div class="relative h-28 w-28">
                    <svg class="h-28 w-28 -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor"
                                class="text-ink-100" stroke-width="3"></circle>
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor"
                                class="{{ $warnaCincin }}" stroke-width="3" stroke-linecap="round"
                                stroke-dasharray="{{ $persen }}, 100"></circle>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-extrabold text-ink-900">{{ $persen }}%</span>
                    </div>
                </div>

                <div class="text-left">
                    <div class="text-4xl font-extrabold tracking-tight text-ink-900">
                        {{ $nilaiPg }}<span class="text-xl text-ink-400"> / {{ $totalBobot }}</span>
                    </div>
                    <p class="mt-1 text-sm text-ink-500">poin pilihan ganda</p>
                </div>
            </div>

            @if ($adaManual)
                <div class="mt-5">
                    <x-ui.alert type="info">
                        Ada soal esai/praktik yang masih menunggu penilaian guru. Nilai akhir bisa berubah.
                    </x-ui.alert>
                </div>
            @endif
        </x-ui.card>

        {{-- Rincian --}}
        <x-ui.card padding="p-0">
            <div class="border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Rincian jawaban</h3>
            </div>

            <div class="divide-y divide-ink-50">
                @foreach ($set as $j)
                    <div class="flex items-start gap-3 px-5 py-4">
                        @if ($j->soal->tipe === 'pg')
                            <span @class([
                                'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full',
                                'bg-emerald-100 text-emerald-700' => $j->benar,
                                'bg-rose-100 text-rose-700' => ! $j->benar,
                            ])>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="{{ $j->benar ? 'm4.5 12.75 6 6 9-13.5' : 'M6 18 18 6M6 6l12 12' }}" />
                                </svg>
                            </span>
                        @else
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium leading-snug text-ink-900">
                                {{ $loop->iteration }}. {{ Str::limit(strip_tags($j->soal->pertanyaan), 110) }}
                            </p>

                            <p class="mt-1.5 text-sm text-ink-600">
                                Jawabanmu:
                                <span class="font-mono font-semibold text-ink-800">{{ Str::limit($j->jawaban, 120) ?: '—' }}</span>
                            </p>

                            @if ($j->soal->tipe === 'pg')
                                @if ($j->benar)
                                    <span class="mt-1 inline-block text-xs font-medium text-emerald-700">Benar · +{{ $j->nilai }} poin</span>
                                @else
                                    <span class="mt-1 inline-block text-xs font-medium text-rose-600">
                                        Salah · kunci: {{ $j->soal->jawaban_benar }}
                                    </span>
                                @endif
                            @else
                                <span class="mt-1 inline-block text-xs font-medium text-amber-700">Menunggu penilaian guru</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
