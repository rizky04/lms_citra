<x-layouts.app title="Kuis Saya" subtitle="Kuis yang dipublish di kelasmu">
    <x-ui.page-hero title="Kuis Saya" tone="brand"
        subtitle="Kerjakan kuis yang dipublish gurumu. Nilai pilihan ganda langsung keluar setelah dikirim."
        icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25"
        :meta="[['label' => $kuis->count().' kuis tersedia']]" />

    @if ($kuis->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada kuis"
                        icon="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z">
                Kuis akan muncul di sini setelah gurumu mempublikasikannya.
                Pastikan kamu sudah gabung kelas pakai kode dari guru.
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($kuis as $k)
                <x-ui.card class="flex flex-col">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75h-5.25" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-bold leading-snug text-ink-900">{{ $k->judul }}</h3>
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                <x-ui.badge color="brand">{{ $k->kelas->nama }}</x-ui.badge>
                                <x-ui.badge>{{ $k->soal_count }} soal</x-ui.badge>
                                @if ($k->durasi_menit)<x-ui.badge color="amber">{{ $k->durasi_menit }} menit</x-ui.badge>@endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex gap-2 border-t border-ink-100 pt-4">
                        <x-ui.btn :href="route('kerjakan.show', $k)" class="flex-1">Kerjakan</x-ui.btn>
                        <x-ui.btn variant="secondary" :href="route('kerjakan.hasil', $k)">Hasil</x-ui.btn>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
