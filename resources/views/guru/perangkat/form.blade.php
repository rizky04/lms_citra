<x-layouts.app :title="$perangkat->exists ? 'Edit Dokumen' : 'Dokumen Baru'"
               subtitle="Perangkat pembelajaran">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('perangkat.index')">← Semua dokumen</x-ui.btn>
    </x-slot:actions>

    <form method="POST" action="{{ $perangkat->exists ? route('perangkat.update', $perangkat) : route('perangkat.store') }}"
          class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($perangkat->exists) @method('PUT') @endif

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card class="space-y-4">
                <x-ui.field label="Judul dokumen" name="judul">
                    <input type="text" name="judul" value="{{ old('judul', $perangkat->judul) }}" required
                           class="field" placeholder="mis. Modul Ajar Informatika — Berpikir Komputasional">
                </x-ui.field>

                <x-ui.field label="Isi dokumen" name="konten"
                            hint="Teks biasa. Akan dicetak apa adanya ke PDF berkop sekolah.">
                    <textarea name="konten" rows="24" class="field font-mono text-[13px]"
                              placeholder="Tulis isi dokumen di sini…">{{ old('konten', $perangkat->konten) }}</textarea>
                </x-ui.field>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card class="space-y-4">
                <x-ui.field label="Jenis dokumen" name="jenis">
                    <select name="jenis" class="field">
                        @foreach ($jenisList as $k => $label)
                            <option value="{{ $k }}" @selected(old('jenis', $perangkat->jenis) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Jenjang" name="jenjang_id">
                    <select name="jenjang_id" class="field">
                        @foreach ($jenjangList as $j)
                            <option value="{{ $j->id }}" @selected(old('jenjang_id', $perangkat->jenjang_id) == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Mata pelajaran" name="mapel_nama">
                    <input type="text" name="mapel_nama" list="mapel-list" required class="field"
                           value="{{ old('mapel_nama', $perangkat->mapel?->nama ?? 'Informatika') }}">
                    <datalist id="mapel-list">
                        @foreach ($mapelList as $m)<option value="{{ $m->nama }}">@endforeach
                    </datalist>
                </x-ui.field>

                <div class="grid grid-cols-2 gap-3">
                    <x-ui.field label="Tahun ajaran" name="tahun_ajaran">
                        <input type="text" name="tahun_ajaran" class="field"
                               value="{{ old('tahun_ajaran', $perangkat->tahun_ajaran) }}" placeholder="2025/2026">
                    </x-ui.field>
                    <x-ui.field label="Semester" name="semester">
                        <select name="semester" class="field">
                            <option value="ganjil" @selected(old('semester', $perangkat->semester) === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected(old('semester', $perangkat->semester) === 'genap')>Genap</option>
                        </select>
                    </x-ui.field>
                </div>

                <x-ui.field label="Status" name="status">
                    <select name="status" class="field">
                        <option value="draft" @selected(old('status', $perangkat->status) === 'draft')>Draft</option>
                        <option value="published" @selected(old('status', $perangkat->status) === 'published')>Final</option>
                    </select>
                </x-ui.field>
            </x-ui.card>

            <div class="flex gap-2">
                <x-ui.btn class="flex-1">{{ $perangkat->exists ? 'Simpan perubahan' : 'Simpan dokumen' }}</x-ui.btn>
                <x-ui.btn variant="secondary" :href="route('perangkat.index')">Batal</x-ui.btn>
            </div>
        </div>
    </form>
</x-layouts.app>
