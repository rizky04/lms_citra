@php $terkunci = $submisi && $submisi->nilai !== null; @endphp

<x-layouts.app :title="$tugas->judul" :subtitle="$tugas->kelas->nama">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('tugas.saya.index')">← Semua tugas</x-ui.btn>
    </x-slot:actions>

    <div class="mx-auto w-full max-w-3xl space-y-6">
        {{-- Instruksi --}}
        <x-ui.page-hero :title="$tugas->judul" :tone="$terkunci ? 'emerald' : 'brand'"
            icon="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18"
            :meta="array_values(array_filter([
                ['label' => $tugas->kelas->nama],
                $tugas->deadline ? ['label' => 'Tenggat '.$tugas->deadline->translatedFormat('d M Y, H:i'),
                    'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'] : null,
            ]))">
            <x-slot:action>
                @if ($terkunci)
                    <x-ui.badge color="green">sudah dinilai</x-ui.badge>
                @elseif ($submisi)
                    <x-ui.badge color="brand">terkumpul</x-ui.badge>
                @elseif ($tugas->deadline?->isPast())
                    <x-ui.badge color="rose">lewat tenggat</x-ui.badge>
                @else
                    <x-ui.badge color="amber">belum dikerjakan</x-ui.badge>
                @endif
            </x-slot:action>
        </x-ui.page-hero>

        @if ($tugas->instruksi || $tugas->file_path)
            <x-ui.section title="Instruksi"
                          icon="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z">
                @if ($tugas->instruksi)
                    <x-ui.rich-text :text="$tugas->instruksi" />
                @endif

                @if ($tugas->file_path)
                    <a href="{{ Storage::url($tugas->file_path) }}" target="_blank"
                       class="mt-4 flex items-center gap-3 rounded-xl border border-brand-200 bg-brand-50/70 p-3 hover:bg-brand-100">
                        <svg class="h-5 w-5 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                        <span class="truncate text-sm font-semibold text-brand-800">{{ basename($tugas->file_path) }}</span>
                    </a>
                @endif
            </x-ui.section>
        @endif

        {{-- Nilai & feedback --}}
        @if ($terkunci)
            <x-ui.card class="border-emerald-200 bg-emerald-50/50">
                <div class="flex items-center gap-4">
                    <div class="text-4xl font-extrabold tracking-tight text-emerald-700">
                        {{ rtrim(rtrim($submisi->nilai, '0'), '.') }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-emerald-900">Sudah dinilai guru</div>
                        @if ($submisi->feedback)
                            <p class="mt-0.5 text-sm text-emerald-800">{{ $submisi->feedback }}</p>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        @endif

        {{-- Form pengumpulan --}}
        <x-ui.card>
            <h3 class="text-sm font-bold text-ink-900">
                {{ $submisi ? 'Pekerjaanmu' : 'Kumpulkan pekerjaan' }}
            </h3>

            @if ($terkunci)
                <p class="mt-1 text-sm text-ink-500">Sudah dinilai — tidak bisa diubah lagi.</p>

                @if ($submisi->isi)
                    <div class="mt-4 whitespace-pre-line rounded-xl bg-ink-50 px-4 py-3 text-sm text-ink-700">{{ $submisi->isi }}</div>
                @endif
                @if ($submisi->file_path)
                    <a href="{{ Storage::url($submisi->file_path) }}" target="_blank"
                       class="mt-3 inline-block text-sm font-medium text-brand-700 hover:underline">
                        📎 {{ basename($submisi->file_path) }}
                    </a>
                @endif
            @else
                <p class="mt-1 text-sm text-ink-500">
                    Tulis jawaban, unggah berkas, atau keduanya. Bisa diperbarui selama belum dinilai.
                </p>

                <form method="POST" action="{{ route('tugas.saya.submit', $tugas) }}"
                      enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf

                    <x-ui.field label="Jawaban" name="isi">
                        <textarea name="isi" rows="8" class="field"
                                  placeholder="Tulis jawabanmu di sini…">{{ old('isi', $submisi->isi ?? '') }}</textarea>
                    </x-ui.field>

                    <x-ui.field label="Berkas" name="berkas" hint="PDF, Word, gambar, ZIP. Maks 10 MB.">
                        @if ($submisi?->file_path)
                            <a href="{{ Storage::url($submisi->file_path) }}" target="_blank"
                               class="mb-2 block truncate text-sm font-medium text-brand-700 hover:underline">
                                {{ basename($submisi->file_path) }} (unggah baru untuk mengganti)
                            </a>
                        @endif
                        <input type="file" name="berkas"
                               class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0
                                      file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold
                                      file:text-brand-700 hover:file:bg-brand-100">
                    </x-ui.field>

                    <x-ui.btn>{{ $submisi ? 'Perbarui pekerjaan' : 'Kumpulkan' }}</x-ui.btn>
                </form>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
