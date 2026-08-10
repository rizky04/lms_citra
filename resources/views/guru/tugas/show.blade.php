<x-layouts.app :title="$tugas->judul" :subtitle="$tugas->kelas->nama">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('tugas.edit', $tugas)">Edit</x-ui.btn>
        <x-ui.btn variant="ghost" size="sm" :href="route('tugas.index')">← Semua tugas</x-ui.btn>
    </x-slot:actions>

    @php
        $sudah = $submisi->count();
        $total = $tugas->kelas->siswa->count();
        $dinilai = $submisi->filter(fn ($s) => $s->nilai !== null)->count();
    @endphp

    <x-ui.page-hero :title="$tugas->judul" tone="dark"
        icon="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18"
        :meta="[
            ['label' => $tugas->kelas->nama],
            ['label' => $sudah.' dari '.$total.' siswa mengumpulkan'],
            ['label' => $dinilai.' sudah dinilai'],
        ]">
        <x-slot:action>
            @if ($tugas->deadline)
                <x-ui.badge :color="$tugas->deadline->isPast() ? 'rose' : 'amber'">
                    Tenggat {{ $tugas->deadline->translatedFormat('d M Y, H:i') }}
                </x-ui.badge>
            @endif
        </x-slot:action>
    </x-ui.page-hero>

    {{-- Progres pengumpulan --}}
    <x-ui.card padding="p-5">
        <div class="flex items-center justify-between text-sm">
            <span class="font-semibold text-ink-800">Progres pengumpulan</span>
            <span class="text-ink-500">{{ $sudah }}/{{ $total }}</span>
        </div>
        <div class="mt-2 flex h-2 overflow-hidden rounded-full bg-ink-100">
            <div class="bg-emerald-500 transition-all" style="width: {{ $total ? $dinilai / $total * 100 : 0 }}%"></div>
            <div class="bg-amber-400 transition-all" style="width: {{ $total ? ($sudah - $dinilai) / $total * 100 : 0 }}%"></div>
        </div>
        <div class="mt-2 flex flex-wrap gap-4 text-xs text-ink-500">
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $dinilai }} dinilai</span>
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span>{{ $sudah - $dinilai }} menunggu dinilai</span>
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-ink-200"></span>{{ $total - $sudah }} belum mengumpulkan</span>
        </div>
    </x-ui.card>

    @if ($tugas->instruksi || $tugas->file_path)
        <x-ui.section title="Instruksi tugas"
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

    <x-ui.section title="Pekerjaan siswa" padding="p-0"
                  icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z">

        @forelse ($tugas->kelas->siswa as $siswa)
            @php $s = $submisi->get($siswa->id); @endphp

            <div class="border-b border-ink-50 px-6 py-4 last:border-0" x-data="{ buka: {{ $s && $s->nilai === null ? 'true' : 'false' }} }">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ink-100 text-xs font-bold text-ink-600">
                        {{ Str::upper(Str::substr($siswa->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-ink-900">{{ $siswa->name }}</div>
                        <div class="text-xs text-ink-500">
                            @if ($s)
                                Dikumpulkan {{ $s->submitted_at?->translatedFormat('d M, H:i') }}
                                @if ($tugas->deadline && $s->submitted_at?->gt($tugas->deadline))
                                    <span class="font-medium text-rose-600">· terlambat</span>
                                @endif
                            @else
                                Belum mengumpulkan
                            @endif
                        </div>
                    </div>

                    @if (! $s)
                        <x-ui.badge>belum</x-ui.badge>
                    @elseif ($s->nilai !== null)
                        <x-ui.badge color="green">nilai {{ rtrim(rtrim($s->nilai, '0'), '.') }}</x-ui.badge>
                        <button type="button" @click="buka = !buka" class="text-xs font-semibold text-brand-600 hover:underline">ubah</button>
                    @else
                        <x-ui.badge color="amber">perlu dinilai</x-ui.badge>
                        <button type="button" @click="buka = !buka" class="text-xs font-semibold text-brand-600 hover:underline">nilai</button>
                    @endif
                </div>

                @if ($s)
                    <div x-cloak x-show="buka" class="ms-12 mt-3 space-y-3">
                        @if ($s->isi)
                            <div class="rounded-xl bg-ink-50 px-4 py-3 text-sm leading-relaxed text-ink-700 whitespace-pre-line">{{ $s->isi }}</div>
                        @endif
                        @if ($s->file_path)
                            <a href="{{ Storage::url($s->file_path) }}" target="_blank"
                               class="inline-block text-sm font-medium text-brand-700 hover:underline">
                                📎 {{ basename($s->file_path) }}
                            </a>
                        @endif

                        <form method="POST" action="{{ route('tugas.nilai', [$tugas, $s]) }}"
                              class="flex flex-wrap items-end gap-3 rounded-xl border border-ink-200 p-3">
                            @csrf
                            <x-ui.field label="Nilai (0–100)" class="w-32">
                                <input type="number" name="nilai" min="0" max="100" step="0.5" required class="field"
                                       value="{{ $s->nilai !== null ? rtrim(rtrim($s->nilai, '0'), '.') : '' }}">
                            </x-ui.field>
                            <x-ui.field label="Feedback" class="min-w-[12rem] flex-1">
                                <input type="text" name="feedback" class="field"
                                       value="{{ $s->feedback }}" placeholder="Catatan untuk siswa…">
                            </x-ui.field>
                            <x-ui.btn size="sm">Simpan nilai</x-ui.btn>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <x-ui.empty title="Belum ada siswa di kelas ini">
                Bagikan kode undangan kelas supaya siswa bisa gabung.
            </x-ui.empty>
        @endforelse
    </x-ui.section>
</x-layouts.app>
