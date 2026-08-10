@php $menit = \App\Support\RichText::menitBaca($materi->konten); @endphp

{{-- Halaman baca materi, dipakai guru & siswa --}}
<x-ui.reader :text="$materi->konten">

    <x-ui.page-hero :title="$materi->judul" :meta="[
        ['label' => $materi->guru->name, 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
        ['label' => $menit.' menit baca', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['label' => 'diperbarui '.$materi->updated_at->diffForHumans()],
    ]">
        <x-slot:action>
            <div class="flex flex-wrap gap-1.5">
                <x-ui.badge color="brand">{{ $materi->mapel->nama }}</x-ui.badge>
                <x-ui.badge>{{ $materi->mapel->jenjang->nama }}</x-ui.badge>
                @if ($materi->kelas)<x-ui.badge color="amber">{{ $materi->kelas->nama }}</x-ui.badge>@endif
                @if ($materi->sumber === 'ai_generated')<x-ui.badge color="green">dibantu AI</x-ui.badge>@endif
            </div>
        </x-slot:action>
    </x-ui.page-hero>

    {{-- Lampiran --}}
    @if ($materi->file_path)
        <a href="{{ Storage::url($materi->file_path) }}" target="_blank"
           class="group flex items-center gap-4 rounded-2xl border border-brand-200 bg-brand-50/70 p-4 transition hover:bg-brand-100">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 shadow-soft">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-bold text-brand-900">{{ basename($materi->file_path) }}</span>
                <span class="block text-xs text-brand-600">Lampiran materi — klik untuk membuka</span>
            </span>
            <svg class="h-5 w-5 shrink-0 text-brand-400 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>
    @endif

    <x-ui.card class="sm:p-8">
        <x-ui.rich-text :text="$materi->konten"
                        :gambar="$materi->gambar ?? []"
                        :boleh-unggah="auth()->user()->isPengajar()"
                        :materi="$materi" />
    </x-ui.card>
</x-ui.reader>
