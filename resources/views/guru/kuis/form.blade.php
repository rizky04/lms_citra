<x-layouts.app :title="$kuis->exists ? 'Edit Kuis' : 'Kuis Baru'"
               subtitle="Atur dulu detailnya, soal ditambahkan setelah ini">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('kuis.index')">← Semua kuis</x-ui.btn>
    </x-slot:actions>

    <div class="mx-auto w-full max-w-2xl">
        @if ($kelasList->isEmpty())
            <x-ui.alert type="error" class="mb-6">
                Belum ada kelas. <a href="{{ route('kelas.index') }}" class="font-semibold underline">Buat kelas dulu</a>
                sebelum membuat kuis.
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ $kuis->exists ? route('kuis.update', $kuis) : route('kuis.store') }}">
            @csrf
            @if ($kuis->exists) @method('PUT') @endif

            <x-ui.card class="space-y-4">
                <x-ui.field label="Judul kuis" name="judul">
                    <input type="text" name="judul" value="{{ old('judul', $kuis->judul) }}" required
                           class="field" placeholder="mis. Ulangan Harian 1 — Algoritma">
                </x-ui.field>

                <x-ui.field label="Kelas" name="kelas_id" hint="Hanya siswa di kelas ini yang bisa mengerjakan.">
                    <select name="kelas_id" class="field" required>
                        <option value="">— pilih kelas —</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected(old('kelas_id', $kuis->kelas_id) == $k->id)>
                                {{ $k->nama }} ({{ $k->jenjang->nama }})
                            </option>
                        @endforeach
                    </select>
                </x-ui.field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Durasi (menit)" name="durasi_menit" hint="Kosongkan bila tanpa batas waktu.">
                        <input type="number" name="durasi_menit" min="1" class="field"
                               value="{{ old('durasi_menit', $kuis->durasi_menit) }}" placeholder="mis. 60">
                    </x-ui.field>

                    <x-ui.field label="Maks percobaan" name="max_percobaan">
                        <input type="number" name="max_percobaan" min="1" required class="field"
                               value="{{ old('max_percobaan', $kuis->max_percobaan ?? 1) }}">
                    </x-ui.field>
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-ink-50 p-3.5">
                    <input type="checkbox" name="acak_soal" value="1" @checked(old('acak_soal', $kuis->acak_soal))
                           class="mt-0.5 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                    <span>
                        <span class="block text-sm font-medium text-ink-800">Acak urutan soal</span>
                        <span class="block text-xs text-ink-500">Tiap siswa mendapat urutan berbeda.</span>
                    </span>
                </label>

                <div class="flex gap-2 border-t border-ink-100 pt-4">
                    <x-ui.btn class="flex-1">{{ $kuis->exists ? 'Simpan perubahan' : 'Lanjut tambah soal' }}</x-ui.btn>
                    <x-ui.btn variant="secondary" :href="route('kuis.index')">Batal</x-ui.btn>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
