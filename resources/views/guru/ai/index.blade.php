<x-layouts.app title="Asisten AI" subtitle="Buat draft soal, materi, dan perangkat ajar dengan Gemini">
    <x-ui.page-hero title="Asisten AI" tone="brand"
        subtitle="Sebutkan jenjang dan topiknya, AI menyusun draft soal, materi, atau perangkat ajar. Kamu tinggal review."
        icon="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"
        :meta="[
            ['label' => 'Model '.config('services.gemini.model')],
            ['label' => 'Sisa kuota hari ini: '.$sisaKuota],
            ['label' => $apiSiap ? 'API terhubung' : 'API key belum diisi'],
        ]" />

    @if ($workerMacet)
        <x-ui.alert type="error">
            <strong>Ada permintaan yang mengendap di antrean.</strong>
            Pemroses antrean sepertinya tidak berjalan. Jalankan perintah ini di terminal
            dan biarkan terbuka: <code class="font-mono">php artisan queue:work</code>
        </x-ui.alert>
    @endif

    @unless ($apiSiap)
        <x-ui.alert type="error">
            <strong>API key Gemini belum diisi.</strong>
            Buka file <code class="font-mono">.env</code>, isi <code class="font-mono">GEMINI_API_KEY=</code>
            dengan kunci dari Google AI Studio, lalu jalankan <code class="font-mono">php artisan config:clear</code>.
        </x-ui.alert>
    @endunless

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Form generate --}}
        <div class="lg:col-span-3">
            <form method="POST" action="{{ route('ai.store') }}"
                  x-data="{ jenis: '{{ old('jenis', 'soal') }}' }">
                @csrf

                <x-ui.card class="space-y-5">
                    <div>
                        <span class="label">Mau membuat apa?</span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ([
                                ['soal', 'Soal', 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
                                ['materi', 'Materi', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
                                ['perangkat', 'Perangkat', 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z'],
                            ] as [$val, $judul, $path])
                                <label class="cursor-pointer">
                                    <input type="radio" name="jenis" value="{{ $val }}" x-model="jenis" class="peer sr-only">
                                    <div class="rounded-xl border border-ink-200 bg-white px-3 py-3 text-center transition
                                                peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-500
                                                hover:border-ink-300">
                                        <svg class="mx-auto h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                                        </svg>
                                        <div class="mt-1.5 text-sm font-semibold text-ink-900">{{ $judul }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('jenis')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Jenjang" name="jenjang_id" hint="Menentukan bahasa & kedalaman materi.">
                            <select name="jenjang_id" class="field">
                                @foreach ($jenjangList as $j)
                                    <option value="{{ $j->id }}" @selected(old('jenjang_id') == $j->id)>{{ $j->nama }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>

                        <x-ui.field label="Mata pelajaran" name="mapel_nama">
                            <input type="text" name="mapel_nama" list="mapel-list" required class="field"
                                   value="{{ old('mapel_nama', 'Informatika') }}">
                            <datalist id="mapel-list">
                                @foreach ($mapelList as $m)<option value="{{ $m->nama }}">@endforeach
                            </datalist>
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Topik / bab" name="topik" hint="Makin spesifik, makin bagus hasilnya.">
                        <input type="text" name="topik" required class="field"
                               value="{{ old('topik') }}" placeholder="mis. Struktur perulangan (looping) pada Python">
                    </x-ui.field>

                    {{-- Opsi khusus SOAL --}}
                    <div x-show="jenis === 'soal'" class="grid gap-4 sm:grid-cols-4">
                        <x-ui.field label="Jumlah" name="jumlah">
                            <input type="number" name="jumlah" min="1" max="30" class="field" value="{{ old('jumlah', 10) }}">
                        </x-ui.field>
                        <x-ui.field label="Tipe" name="tipe">
                            <select name="tipe" class="field">
                                <option value="pg">Pilihan Ganda</option>
                                <option value="esai" @selected(old('tipe') === 'esai')>Esai</option>
                                <option value="praktik" @selected(old('tipe') === 'praktik')>Praktik</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Tingkat" name="tingkat">
                            <select name="tingkat" class="field">
                                @foreach (['mudah', 'sedang', 'sulit'] as $t)
                                    <option value="{{ $t }}" @selected(old('tingkat', 'sedang') === $t)>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Bobot" name="bobot">
                            <input type="number" name="bobot" min="1" class="field" value="{{ old('bobot', 1) }}">
                        </x-ui.field>
                    </div>

                    {{-- Opsi khusus MATERI --}}
                    <div x-cloak x-show="jenis === 'materi'" class="space-y-4">
                        <x-ui.field label="Untuk kelas" name="kelas_id" hint="Opsional — kosongkan untuk semua kelas.">
                            <select name="kelas_id" class="field">
                                <option value="">Semua kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-ink-50 p-3.5">
                            <input type="checkbox" name="sertakan_gambar" value="1" @checked(old('sertakan_gambar'))
                                   class="mt-0.5 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="block text-sm font-medium text-ink-800">Sertakan ilustrasi</span>
                                <span class="block text-xs leading-relaxed text-ink-500">
                                    AI menandai bagian yang sebaiknya bergambar beserta deskripsinya.
                                    Gambarnya kamu unggah sendiri lewat halaman edit materi —
                                    model Gemini pada API key ini belum berkuota untuk membuat gambar.
                                </span>
                            </span>
                        </label>
                    </div>

                    {{-- Opsi khusus PERANGKAT --}}
                    <div x-cloak x-show="jenis === 'perangkat'" class="grid gap-4 sm:grid-cols-3">
                        <x-ui.field label="Jenis dokumen" name="jenis_perangkat" class="sm:col-span-3">
                            <select name="jenis_perangkat" class="field">
                                @foreach ($jenisPerangkat as $k => $label)
                                    <option value="{{ $k }}" @selected(old('jenis_perangkat') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Tahun ajaran" name="tahun_ajaran" class="sm:col-span-2">
                            <input type="text" name="tahun_ajaran" class="field"
                                   value="{{ old('tahun_ajaran', date('Y').'/'.(date('Y') + 1)) }}">
                        </x-ui.field>
                        <x-ui.field label="Semester" name="semester">
                            <select name="semester" class="field">
                                <option value="ganjil">Ganjil</option>
                                <option value="genap" @selected(old('semester') === 'genap')>Genap</option>
                            </select>
                        </x-ui.field>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-4">
                        <p class="text-xs text-ink-500">
                            Sisa kuota hari ini: <strong class="text-ink-800">{{ $sisaKuota }}</strong>
                        </p>
                        <x-ui.btn :disabled="! $apiSiap || $sisaKuota <= 0">Buat draft dengan AI</x-ui.btn>
                    </div>
                </x-ui.card>
            </form>

            <x-ui.alert type="info" class="mt-4">
                Hasil AI selalu masuk sebagai <strong>draft</strong>. Siswa tidak melihatnya
                sampai kamu review dan mengubah statusnya jadi published.
            </x-ui.alert>
        </div>

        {{-- Riwayat --}}
        <x-ui.card padding="p-0" class="h-fit lg:col-span-2">
            <div class="border-b border-ink-100 px-6 py-4">
                <h3 class="text-sm font-bold text-ink-900">Riwayat permintaan</h3>
            </div>

            @forelse ($riwayat as $r)
                <div class="border-b border-ink-50 px-5 py-3.5 last:border-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-ink-900">
                                {{ $r->request_json['topik'] ?? '—' }}
                            </div>
                            <div class="truncate text-xs text-ink-500">
                                {{ ucfirst($r->jenis) }} · {{ $r->request_json['jenjang'] ?? '' }}
                                · {{ $r->created_at->diffForHumans() }}
                            </div>
                        </div>

                        @php $warna = ['done' => 'green', 'failed' => 'rose', 'processing' => 'amber'][$r->status] ?? 'slate'; @endphp
                        <x-ui.badge :color="$warna">{{ $r->status }}</x-ui.badge>
                    </div>

                    @if ($r->status === 'done')
                        <p class="mt-1 text-xs text-emerald-700">
                            {{ $r->hasil_json['dibuat'] ?? 0 }} item dibuat sebagai draft.
                            @if (($r->hasil_json['jenis'] ?? '') === 'soal')
                                <a href="{{ route('soal.index', ['status' => 'draft']) }}" class="font-semibold underline">Review soal</a>
                            @elseif (($r->hasil_json['jenis'] ?? '') === 'materi')
                                <a href="{{ route('materi.index', ['status' => 'draft']) }}" class="font-semibold underline">Review materi</a>
                            @else
                                <a href="{{ route('perangkat.index') }}" class="font-semibold underline">Review dokumen</a>
                            @endif
                        </p>
                    @elseif ($r->status === 'failed')
                        <p class="mt-1 text-xs text-rose-600">{{ Str::limit($r->error, 100) }}</p>
                    @elseif (in_array($r->status, ['queued', 'processing']))
                        <p class="mt-1 text-xs text-ink-400">Sedang diproses — muat ulang halaman sebentar lagi.</p>
                    @endif
                </div>
            @empty
                <x-ui.empty title="Belum ada permintaan"
                            icon="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z">
                    Isi form di sebelah untuk membuat draft pertamamu.
                </x-ui.empty>
            @endforelse
        </x-ui.card>
    </div>
</x-layouts.app>
