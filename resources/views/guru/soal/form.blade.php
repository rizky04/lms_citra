<x-layouts.app :title="$soal->exists ? 'Edit Soal' : 'Soal Baru'"
               subtitle="Soal tersimpan di bank dan bisa dipakai di banyak kuis">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('soal.index')">← Bank soal</x-ui.btn>
    </x-slot:actions>

    <form method="POST" action="{{ $soal->exists ? route('soal.update', $soal) : route('soal.store') }}"
          x-data="{ tipe: '{{ old('tipe', $soal->tipe) }}' }" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($soal->exists) @method('PUT') @endif

        {{-- Kolom utama --}}
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card>
                <h3 class="text-sm font-bold text-ink-900">Pertanyaan</h3>

                <div class="mt-4 space-y-4">
                    <x-ui.field label="Tipe soal">
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ([['pg', 'Pilihan Ganda'], ['esai', 'Esai'], ['praktik', 'Praktik']] as [$val, $judul])
                                <label class="cursor-pointer">
                                    <input type="radio" name="tipe" value="{{ $val }}" x-model="tipe" class="peer sr-only">
                                    <div class="rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-center text-sm font-medium text-ink-600 transition
                                                peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700
                                                hover:border-ink-300">{{ $judul }}</div>
                                </label>
                            @endforeach
                        </div>
                        @error('tipe')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </x-ui.field>

                    <x-ui.field label="Teks pertanyaan" name="pertanyaan">
                        <textarea name="pertanyaan" rows="4" required class="field"
                                  placeholder="Tulis pertanyaan di sini…">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>

            {{-- Opsi PG --}}
            <x-ui.card x-cloak x-show="tipe === 'pg'">
                <h3 class="text-sm font-bold text-ink-900">Opsi jawaban</h3>
                <p class="mt-1 text-sm text-ink-500">Klik lingkaran di kiri untuk menandai kunci jawaban.</p>

                <div class="mt-4 space-y-2.5">
                    @foreach (['A', 'B', 'C', 'D'] as $huruf)
                        <label class="flex items-center gap-3 rounded-xl border border-ink-200 p-2.5 transition
                                      focus-within:border-brand-400 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60">
                            <input type="radio" name="jawaban_benar" value="{{ $huruf }}"
                                   @checked(old('jawaban_benar', $soal->jawaban_benar) === $huruf)
                                   class="h-4 w-4 shrink-0 border-ink-300 text-brand-600 focus:ring-brand-500">
                            <span class="w-5 shrink-0 text-sm font-bold text-ink-500">{{ $huruf }}</span>
                            <input type="text" name="opsi[{{ $huruf }}]"
                                   value="{{ old('opsi.'.$huruf, $soal->opsi_json[$huruf] ?? '') }}"
                                   class="w-full border-0 bg-transparent p-0 text-sm text-ink-800 placeholder:text-ink-400 focus:ring-0"
                                   placeholder="Opsi {{ $huruf }}">
                        </label>
                    @endforeach
                </div>
                @error('jawaban_benar')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                @error('opsi')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
            </x-ui.card>

            <div x-cloak x-show="tipe !== 'pg'">
                <x-ui.alert type="info">
                    Soal esai/praktik tidak dinilai otomatis — kamu yang menilai jawaban siswa.
                </x-ui.alert>
            </div>
        </div>

        {{-- Sidebar meta --}}
        <div class="space-y-6">
            <x-ui.card>
                <h3 class="text-sm font-bold text-ink-900">Klasifikasi</h3>

                <div class="mt-4 space-y-4">
                    <x-ui.field label="Jenjang" name="jenjang_id">
                        <select name="jenjang_id" class="field">
                            @foreach ($jenjangList as $j)
                                <option value="{{ $j->id }}" @selected(old('jenjang_id', $soal->jenjang_id) == $j->id)>{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field label="Mata pelajaran" name="mapel_nama" hint="Ketik baru atau pilih yang sudah ada.">
                        <input type="text" name="mapel_nama" list="mapel-list" required class="field"
                               value="{{ old('mapel_nama', $soal->mapel?->nama) }}" placeholder="mis. Informatika">
                        <datalist id="mapel-list">
                            @foreach ($mapelList as $m)<option value="{{ $m->nama }}">@endforeach
                        </datalist>
                    </x-ui.field>

                    <x-ui.field label="Tag / bab" name="tag">
                        <input type="text" name="tag" value="{{ old('tag', $soal->tag) }}"
                               class="field" placeholder="mis. Algoritma Dasar">
                    </x-ui.field>

                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.field label="Bobot" name="bobot">
                            <input type="number" name="bobot" min="1" required class="field"
                                   value="{{ old('bobot', $soal->bobot ?? 1) }}">
                        </x-ui.field>

                        <x-ui.field label="Tingkat" name="tingkat">
                            <select name="tingkat" class="field">
                                <option value="">—</option>
                                @foreach (['mudah', 'sedang', 'sulit'] as $t)
                                    <option value="{{ $t }}" @selected(old('tingkat', $soal->tingkat) === $t)>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Status" name="status">
                        <select name="status" class="field">
                            <option value="published" @selected(old('status', $soal->status) === 'published')>Published — bisa dipakai di kuis</option>
                            <option value="draft" @selected(old('status', $soal->status) === 'draft')>Draft</option>
                        </select>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <div class="flex gap-2">
                <x-ui.btn class="flex-1">{{ $soal->exists ? 'Simpan perubahan' : 'Simpan soal' }}</x-ui.btn>
                <x-ui.btn variant="secondary" :href="route('soal.index')">Batal</x-ui.btn>
            </div>
        </div>
    </form>
</x-layouts.app>
