<x-layouts.app :title="$materi->judul" :subtitle="$materi->mapel->nama">
    <x-slot:actions>
        <x-ui.btn variant="secondary" size="sm" :href="route('materi.baca.index')">← Semua materi</x-ui.btn>
    </x-slot:actions>

    @include('partials.materi-isi')
</x-layouts.app>
