<x-layouts.app title="Import / Export Soal" subtitle="Pindahkan bank soal lewat berkas CSV">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('soal.index')">← Bank soal</x-ui.btn>
    </x-slot:actions>

    <x-ui.page-hero title="Import / Export Soal" tone="dark"
        subtitle="Pindahkan bank soal lewat berkas CSV — untuk arsip, berbagi dengan guru lain, atau memindahkan antar sekolah."
        icon="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Import --}}
        <x-ui.card>
            <h3 class="text-sm font-bold text-ink-900">Import soal</h3>
            <p class="mt-1 text-sm text-ink-500">
                Unggah CSV. Baris yang tidak valid dilewati dan dilaporkan, sisanya tetap masuk.
            </p>

            <div class="mt-4 rounded-xl bg-ink-50 p-4">
                <p class="text-xs font-semibold text-ink-700">Kolom yang dikenali</p>
                <p class="mt-1 font-mono text-[11px] leading-relaxed text-ink-500">
                    tipe, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d,<br>jawaban_benar, bobot, tingkat, tag
                </p>
                <ul class="mt-2 space-y-0.5 text-[11px] text-ink-500">
                    <li>• <span class="font-medium">tipe</span>: pg / esai / praktik</li>
                    <li>• <span class="font-medium">jawaban_benar</span>: A, B, C, atau D (khusus pg)</li>
                    <li>• Punya file Excel? Simpan dulu sebagai CSV.</li>
                </ul>
                <x-ui.btn variant="secondary" size="sm" class="mt-3" :href="route('soal.io.template')">
                    Unduh template CSV
                </x-ui.btn>
            </div>

            <form method="POST" action="{{ route('soal.io.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <x-ui.field label="Berkas CSV" name="berkas">
                    <input type="file" name="berkas" accept=".csv,text/csv" required
                           class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0
                                  file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold
                                  file:text-brand-700 hover:file:bg-brand-100">
                </x-ui.field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Jenjang" name="jenjang_id">
                        <select name="jenjang_id" class="field">
                            @foreach ($jenjangList as $j)
                                <option value="{{ $j->id }}">{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field label="Mata pelajaran" name="mapel_nama">
                        <input type="text" name="mapel_nama" list="mapel-list" required class="field" value="Informatika">
                        <datalist id="mapel-list">
                            @foreach ($mapelList as $m)<option value="{{ $m->nama }}">@endforeach
                        </datalist>
                    </x-ui.field>
                </div>

                <x-ui.field label="Status soal hasil import" name="status">
                    <select name="status" class="field">
                        <option value="draft">Draft — review dulu</option>
                        <option value="published">Published — langsung bisa dipakai</option>
                    </select>
                </x-ui.field>

                <x-ui.btn class="w-full">Import sekarang</x-ui.btn>
            </form>
        </x-ui.card>

        {{-- Export --}}
        <x-ui.card class="h-fit">
            <h3 class="text-sm font-bold text-ink-900">Export soal</h3>
            <p class="mt-1 text-sm text-ink-500">
                Unduh bank soal sebagai CSV — untuk arsip, dibagikan ke guru lain,
                atau dipindahkan ke sekolah lain (format sama dengan import).
            </p>

            <form method="GET" action="{{ route('soal.io.export') }}" class="mt-5 space-y-4">
                <x-ui.field label="Tipe soal">
                    <select name="tipe" class="field">
                        <option value="">Semua tipe</option>
                        <option value="pg">Pilihan Ganda</option>
                        <option value="esai">Esai</option>
                        <option value="praktik">Praktik</option>
                    </select>
                </x-ui.field>

                <x-ui.btn variant="secondary" class="w-full">Unduh CSV</x-ui.btn>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
