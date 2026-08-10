<x-layouts.app :title="$tugas->exists ? 'Edit Tugas' : 'Tugas Baru'">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('tugas.index')">← Semua tugas</x-ui.btn>
    </x-slot:actions>

    <div class="mx-auto w-full max-w-2xl">
        @if ($kelasList->isEmpty())
            <x-ui.alert type="error" class="mb-6">
                Belum ada kelas. <a href="{{ route('kelas.index') }}" class="font-semibold underline">Buat kelas dulu</a>.
            </x-ui.alert>
        @endif

        <form method="POST" enctype="multipart/form-data"
              action="{{ $tugas->exists ? route('tugas.update', $tugas) : route('tugas.store') }}">
            @csrf
            @if ($tugas->exists) @method('PUT') @endif

            <x-ui.card class="space-y-4">
                <x-ui.field label="Judul tugas" name="judul">
                    <input type="text" name="judul" value="{{ old('judul', $tugas->judul) }}" required
                           class="field" placeholder="mis. Praktikum 1 — Membuat Flowchart">
                </x-ui.field>

                <x-ui.field label="Kelas" name="kelas_id">
                    <select name="kelas_id" class="field" required>
                        <option value="">— pilih kelas —</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected(old('kelas_id', $tugas->kelas_id) == $k->id)>
                                {{ $k->nama }} ({{ $k->jenjang->nama }})
                            </option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Instruksi" name="instruksi">
                    <textarea name="instruksi" rows="8" class="field"
                              placeholder="Jelaskan apa yang harus dikerjakan siswa…">{{ old('instruksi', $tugas->instruksi) }}</textarea>
                </x-ui.field>

                <x-ui.field label="Tenggat" name="deadline" hint="Kosongkan bila tanpa batas waktu.">
                    <input type="datetime-local" name="deadline" class="field"
                           value="{{ old('deadline', $tugas->deadline?->format('Y-m-d\TH:i')) }}">
                </x-ui.field>

                <x-ui.field label="Lampiran instruksi" name="lampiran" hint="Opsional. Maks 10 MB.">
                    @if ($tugas->file_path)
                        <a href="{{ Storage::url($tugas->file_path) }}" target="_blank"
                           class="mb-2 block truncate text-sm font-medium text-brand-700 hover:underline">
                            {{ basename($tugas->file_path) }} (unggah baru untuk mengganti)
                        </a>
                    @endif
                    <input type="file" name="lampiran"
                           class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0
                                  file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold
                                  file:text-brand-700 hover:file:bg-brand-100">
                </x-ui.field>

                <div class="flex gap-2 border-t border-ink-100 pt-4">
                    <x-ui.btn class="flex-1">{{ $tugas->exists ? 'Simpan perubahan' : 'Buat tugas' }}</x-ui.btn>
                    <x-ui.btn variant="secondary" :href="route('tugas.index')">Batal</x-ui.btn>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
