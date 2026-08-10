<x-layouts.app :title="$kuis->judul" :subtitle="$kuis->kelas->nama.' · '.$kuis->soal->count().' soal'">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('kuis.edit', $kuis)">Edit detail</x-ui.btn>
        @if ($kuis->status !== 'published')
            <form action="{{ route('kuis.publish', $kuis) }}" method="POST">
                @csrf
                <x-ui.btn size="sm">Publish kuis</x-ui.btn>
            </form>
        @endif
    </x-slot:actions>

    {{-- Ringkasan --}}
    <x-ui.page-hero :title="$kuis->judul" :tone="$kuis->status === 'published' ? 'emerald' : 'dark'"
        :subtitle="$kuis->status === 'published'
            ? 'Kuis ini sudah bisa dikerjakan siswa di kelas '.$kuis->kelas->nama.'.'
            : 'Masih draft — siswa belum bisa melihatnya. Tambahkan soal lalu publish.'"
        icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25"
        :meta="array_values(array_filter([
            ['label' => $kuis->kelas->nama],
            ['label' => $kuis->soal->count().' soal · total bobot '.$kuis->soal->sum('bobot')],
            $kuis->durasi_menit ? ['label' => $kuis->durasi_menit.' menit',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'] : null,
            ['label' => $kuis->max_percobaan.'× percobaan'],
            $kuis->acak_soal ? ['label' => 'soal diacak'] : null,
        ]))" />

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Soal terpasang --}}
        <x-ui.card padding="p-0" class="lg:col-span-3">
            <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Soal di kuis ini</h3>
                <x-ui.badge>{{ $kuis->soal->count() }}</x-ui.badge>
            </div>

            @forelse ($kuis->soal as $i => $s)
                <div class="flex items-start gap-3 border-b border-ink-50 px-5 py-3.5 last:border-0">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-ink-100 text-[11px] font-bold text-ink-600">
                        {{ $i + 1 }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm leading-snug text-ink-800">{{ Str::limit(strip_tags($s->pertanyaan), 100) }}</p>
                        <div class="mt-1 flex items-center gap-1.5">
                            <x-ui.badge :color="$s->tipe === 'pg' ? 'brand' : 'amber'">{{ strtoupper($s->tipe) }}</x-ui.badge>
                            <span class="text-[11px] text-ink-400">bobot {{ $s->bobot }}</span>
                        </div>
                    </div>
                    <form action="{{ route('kuis.soal.hapus', [$kuis, $s]) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="rounded-lg p-1.5 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Lepas dari kuis">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>
                </div>
            @empty
                <x-ui.empty title="Belum ada soal di kuis ini">
                    Tambahkan soal dari bank di panel sebelah — bisa acak atau pilih manual.
                </x-ui.empty>
            @endforelse
        </x-ui.card>

        {{-- Tambah soal --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Acak --}}
            <x-ui.card>
                <h3 class="text-sm font-bold text-ink-900">Tambah acak</h3>
                <p class="mt-1 text-sm text-ink-500">Ambil soal random dari bank untuk jenjang {{ $kuis->kelas->jenjang->nama }}.</p>

                <form action="{{ route('kuis.soal.tambah', $kuis) }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="mode" value="acak">
                    <div class="grid grid-cols-3 gap-3">
                        <x-ui.field label="Jumlah" class="col-span-1">
                            <input type="number" name="jumlah" min="1" value="5" class="field">
                        </x-ui.field>
                        <x-ui.field label="Tag (opsional)" class="col-span-2">
                            <input type="text" name="tag" class="field" placeholder="mis. Algoritma">
                        </x-ui.field>
                    </div>
                    <x-ui.btn variant="soft" class="w-full">Tambah acak</x-ui.btn>
                </form>
            </x-ui.card>

            {{-- Manual --}}
            <x-ui.card padding="p-0">
                <div class="border-b border-ink-100 px-5 py-4">
                    <h3 class="text-sm font-bold text-ink-900">Pilih manual dari bank</h3>
                    <p class="mt-0.5 text-xs text-ink-500">Soal published, jenjang {{ $kuis->kelas->jenjang->nama }}.</p>
                </div>

                @if ($bank->isEmpty())
                    <x-ui.empty title="Bank soal kosong">
                        Tidak ada soal published lain untuk jenjang ini.
                        <x-slot:action>
                            <x-ui.btn :href="route('soal.create')" size="sm">+ Buat soal</x-ui.btn>
                        </x-slot:action>
                    </x-ui.empty>
                @else
                    <form action="{{ route('kuis.soal.tambah', $kuis) }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="manual">

                        <div class="scroll-slim max-h-80 divide-y divide-ink-50 overflow-y-auto">
                            @foreach ($bank as $s)
                                <label class="flex cursor-pointer items-start gap-3 px-5 py-3 hover:bg-ink-50/60 has-[:checked]:bg-brand-50/60">
                                    <input type="checkbox" name="soal_ids[]" value="{{ $s->id }}"
                                           class="mt-0.5 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm leading-snug text-ink-800">{{ Str::limit(strip_tags($s->pertanyaan), 80) }}</span>
                                        <span class="mt-1 flex items-center gap-1.5">
                                            <x-ui.badge :color="$s->tipe === 'pg' ? 'brand' : 'amber'">{{ strtoupper($s->tipe) }}</x-ui.badge>
                                            @if ($s->tag)<x-ui.badge>#{{ $s->tag }}</x-ui.badge>@endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="border-t border-ink-100 p-4">
                            <x-ui.btn class="w-full">Tambah yang dipilih</x-ui.btn>
                        </div>
                    </form>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
