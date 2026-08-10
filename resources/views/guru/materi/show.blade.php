<x-layouts.app :title="$materi->judul" :subtitle="$materi->status === 'published' ? 'Published' : 'Draft — belum terlihat siswa'">
    <x-slot:actions>
        <x-ui.btn size="sm" :href="route('materi.pdf', $materi)">Unduh PDF</x-ui.btn>
        <x-ui.btn variant="secondary" size="sm" :href="route('materi.edit', $materi)">Edit</x-ui.btn>
        <x-ui.btn variant="ghost" size="sm" :href="route('materi.index')">←</x-ui.btn>
    </x-slot:actions>

    @include('partials.materi-isi')
</x-layouts.app>
