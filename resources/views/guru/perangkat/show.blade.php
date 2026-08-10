@php
    $jenisLabel = \App\Models\PerangkatPembelajaran::JENIS[$perangkat->jenis] ?? $perangkat->jenis;
    $menit = \App\Support\RichText::menitBaca($perangkat->konten);
@endphp

<x-layouts.app :title="$perangkat->judul" :subtitle="$jenisLabel">
    <x-slot:actions>
        <x-ui.btn size="sm" :href="route('perangkat.pdf', $perangkat)">Unduh PDF</x-ui.btn>
        <x-ui.btn size="sm" variant="secondary" :href="route('perangkat.edit', $perangkat)">Edit</x-ui.btn>
        <x-ui.btn size="sm" variant="ghost" :href="route('perangkat.index')">←</x-ui.btn>
    </x-slot:actions>

    <x-ui.reader :text="$perangkat->konten">

        <x-ui.page-hero
            :title="$perangkat->judul"
            tone="brand"
            icon="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
            :meta="[
                ['label' => $perangkat->guru->name, 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                ['label' => $menit.' menit baca', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['label' => 'diperbarui '.$perangkat->updated_at->diffForHumans()],
            ]">
            <x-slot:action>
                <div class="flex flex-wrap justify-end gap-1.5">
                    <x-ui.badge color="brand">{{ $jenisLabel }}</x-ui.badge>
                    <x-ui.badge>{{ $perangkat->mapel?->nama }}</x-ui.badge>
                    <x-ui.badge>{{ $perangkat->jenjang?->nama }}</x-ui.badge>
                    @if ($perangkat->semester)
                        <x-ui.badge color="amber">{{ ucfirst($perangkat->semester) }} {{ $perangkat->tahun_ajaran }}</x-ui.badge>
                    @endif
                    @if ($perangkat->sumber === 'ai_generated')<x-ui.badge color="green">dibantu AI</x-ui.badge>@endif
                </div>
            </x-slot:action>
        </x-ui.page-hero>

        <x-ui.card class="sm:p-8">
            @if ($perangkat->konten)
                <x-ui.rich-text :text="$perangkat->konten" />
            @else
                <x-ui.empty title="Dokumen masih kosong"
                            icon="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z">
                    Isi dokumennya, atau biarkan AI menyusun draftnya untukmu.
                    <x-slot:action>
                        <div class="flex gap-2">
                            <x-ui.btn size="sm" :href="route('perangkat.edit', $perangkat)">Isi sekarang</x-ui.btn>
                            <x-ui.btn size="sm" variant="secondary" :href="route('ai.index')">Buat dengan AI</x-ui.btn>
                        </div>
                    </x-slot:action>
                </x-ui.empty>
            @endif
        </x-ui.card>
    </x-ui.reader>
</x-layouts.app>
