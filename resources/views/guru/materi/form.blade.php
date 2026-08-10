<x-layouts.app :title="$materi->exists ? 'Edit Materi' : 'Materi Baru'">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('materi.index')">← Semua materi</x-ui.btn>
    </x-slot:actions>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $materi->exists ? route('materi.update', $materi) : route('materi.store') }}"
          class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($materi->exists) @method('PUT') @endif

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card class="space-y-4">
                <x-ui.field label="Judul materi" name="judul">
                    <input type="text" name="judul" value="{{ old('judul', $materi->judul) }}" required
                           class="field" placeholder="mis. Bab 1 — Algoritma dan Pemrograman">
                </x-ui.field>

                <x-ui.field label="Isi materi" name="konten" hint="Boleh dikosongkan bila hanya mengunggah file.">
                    <textarea name="konten" rows="14" class="field"
                              placeholder="Tulis materi di sini…">{{ old('konten', $materi->konten) }}</textarea>
                </x-ui.field>
            </x-ui.card>

            {{-- Slot ilustrasi dari penanda [GAMBAR: ...] --}}
            @php $slot = \App\Support\RichText::slotGambar($materi->konten); @endphp
            @if ($slot)
                <x-ui.section id="gambar" title="Ilustrasi materi"
                              :desc="count($slot).' slot gambar ditandai di dalam materi. Unggah gambarnya di sini.'"
                              icon="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 9h.008v.008H18V9Zm2.25 9V6a2.25 2.25 0 0 0-2.25-2.25H4.5A2.25 2.25 0 0 0 2.25 6v12A2.25 2.25 0 0 0 4.5 20.25h13.5A2.25 2.25 0 0 0 20.25 18Z">
                    <div class="space-y-4">
                        @foreach ($slot as $i => $deskripsi)
                            @php $terpasang = $materi->gambar[$i] ?? null; @endphp
                            <div class="flex flex-col gap-3 rounded-xl border border-ink-200 p-3 sm:flex-row sm:items-center">
                                <div class="flex h-24 w-full shrink-0 items-center justify-center overflow-hidden
                                            rounded-lg bg-ink-50 sm:w-36">
                                    @if ($terpasang)
                                        <img src="{{ Storage::url($terpasang) }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-xs font-semibold text-ink-400">slot {{ $i + 1 }}</span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm leading-relaxed text-ink-700">{{ $deskripsi }}</p>
                                    <input type="file" name="gambar[{{ $i }}]" accept="image/*"
                                           class="mt-2 block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg
                                                  file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs
                                                  file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                                    @error('gambar.'.$i)<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.section>
            @endif

            <x-ui.card>
                <h3 class="text-sm font-bold text-ink-900">Lampiran</h3>
                <p class="mt-1 text-sm text-ink-500">PDF, Word, PowerPoint, Excel, gambar, atau ZIP. Maks 10 MB.</p>

                @if ($materi->file_path)
                    <div class="mt-3 flex items-center gap-3 rounded-xl bg-ink-50 px-4 py-3">
                        <svg class="h-5 w-5 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                        <a href="{{ Storage::url($materi->file_path) }}" target="_blank"
                           class="flex-1 truncate text-sm font-medium text-brand-700 hover:underline">
                            {{ basename($materi->file_path) }}
                        </a>
                        <span class="text-xs text-ink-400">unggah baru untuk mengganti</span>
                    </div>
                @endif

                <x-ui.field name="lampiran" class="mt-3">
                    <input type="file" name="lampiran"
                           class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0
                                  file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold
                                  file:text-brand-700 hover:file:bg-brand-100">
                </x-ui.field>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card class="space-y-4">
                <h3 class="text-sm font-bold text-ink-900">Klasifikasi</h3>

                <x-ui.field label="Jenjang" name="jenjang_id">
                    <select name="jenjang_id" class="field">
                        @foreach ($jenjangList as $j)
                            <option value="{{ $j->id }}" @selected(old('jenjang_id', $materi->mapel?->jenjang_id) == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Mata pelajaran" name="mapel_nama">
                    <input type="text" name="mapel_nama" list="mapel-list" required class="field"
                           value="{{ old('mapel_nama', $materi->mapel?->nama) }}" placeholder="mis. Informatika">
                    <datalist id="mapel-list">
                        @foreach ($mapelList as $m)<option value="{{ $m->nama }}">@endforeach
                    </datalist>
                </x-ui.field>

                <x-ui.field label="Kelas" name="kelas_id" hint="Kosongkan agar terlihat semua siswa di sekolah.">
                    <select name="kelas_id" class="field">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected(old('kelas_id', $materi->kelas_id) == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <div class="grid grid-cols-2 gap-3">
                    <x-ui.field label="Urutan" name="urutan">
                        <input type="number" name="urutan" min="0" required class="field"
                               value="{{ old('urutan', $materi->urutan ?? 0) }}">
                    </x-ui.field>
                    <x-ui.field label="Status" name="status">
                        <select name="status" class="field">
                            <option value="published" @selected(old('status', $materi->status) === 'published')>Published</option>
                            <option value="draft" @selected(old('status', $materi->status) === 'draft')>Draft</option>
                        </select>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <div class="flex gap-2">
                <x-ui.btn class="flex-1">{{ $materi->exists ? 'Simpan perubahan' : 'Simpan materi' }}</x-ui.btn>
                <x-ui.btn variant="secondary" :href="route('materi.index')">Batal</x-ui.btn>
            </div>
        </div>
    </form>
</x-layouts.app>
