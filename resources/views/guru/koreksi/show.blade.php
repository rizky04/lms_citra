<x-layouts.app :title="'Koreksi: '.$kuis->judul" :subtitle="$kuis->kelas->nama">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('koreksi.index')">← Daftar koreksi</x-ui.btn>
    </x-slot:actions>

    @if ($jawaban->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Tidak ada jawaban esai/praktik">
                Kuis ini hanya berisi soal pilihan ganda — sudah dinilai otomatis.
            </x-ui.empty>
        </x-ui.card>
    @else
        <form method="POST" action="{{ route('koreksi.nilai', $kuis) }}" class="mx-auto w-full max-w-4xl space-y-6">
            @csrf

            @foreach ($jawaban as $userId => $jawabanSiswa)
                @php $siswa = $jawabanSiswa->first()->siswa; @endphp

                <x-ui.card padding="p-0">
                    <div class="flex items-center gap-3 border-b border-ink-100 px-6 py-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">
                            {{ Str::upper(Str::substr($siswa->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-bold text-ink-900">{{ $siswa->name }}</div>
                            <div class="truncate text-xs text-ink-500">{{ $jawabanSiswa->count() }} jawaban perlu dilihat</div>
                        </div>
                    </div>

                    @foreach ($jawabanSiswa as $j)
                        <div class="border-b border-ink-50 px-6 py-4 last:border-0">
                            <p class="text-sm font-medium text-ink-900">{{ $j->soal->pertanyaan }}</p>
                            <div class="mt-1 flex items-center gap-1.5">
                                <x-ui.badge color="amber">{{ strtoupper($j->soal->tipe) }}</x-ui.badge>
                                <span class="text-[11px] text-ink-400">bobot maksimal {{ $j->soal->bobot }}</span>
                            </div>

                            <div class="mt-3 whitespace-pre-line rounded-xl bg-ink-50 px-4 py-3 text-sm leading-relaxed text-ink-700">
                                {{ $j->jawaban ?: '— tidak dijawab —' }}
                            </div>

                            <div class="mt-3 flex items-end gap-3">
                                <x-ui.field :label="'Nilai (maks '.$j->soal->bobot.')'" class="w-40">
                                    <input type="number" name="nilai[{{ $j->id }}]" min="0" max="{{ $j->soal->bobot }}"
                                           step="0.5" class="field" value="{{ $j->nilai }}" placeholder="0">
                                </x-ui.field>
                                @if ($j->nilai !== null)
                                    <span class="pb-2 text-xs font-medium text-emerald-600">sudah dinilai</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </x-ui.card>
            @endforeach

            <x-ui.card padding="p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-ink-500">Kosongkan nilai untuk melewati jawaban tertentu.</p>
                    <x-ui.btn size="lg">Simpan semua nilai</x-ui.btn>
                </div>
            </x-ui.card>
        </form>
    @endif
</x-layouts.app>
