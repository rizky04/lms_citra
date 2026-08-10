@props(['text'])

@php $daftarIsi = \App\Support\RichText::daftarIsi($text); @endphp

{{-- Shell baca dokumen panjang: progres baca + daftar isi lengket + scrollspy.
     Dipakai halaman materi dan perangkat pembelajaran. --}}
<div x-data="pembacaDokumen()" x-init="pasang()">

    <div class="fixed inset-x-0 top-0 z-50 h-1 bg-transparent">
        <div class="h-full bg-gradient-to-r from-brand-400 to-brand-600 transition-[width] duration-150"
             :style="`width: ${progres}%`"></div>
    </div>

    <div @class([
        'grid gap-8',
        'lg:grid-cols-[minmax(0,1fr)_16rem]' => (bool) $daftarIsi,
    ])>
        <article class="min-w-0 space-y-6">
            {{ $slot }}

            @if ($daftarIsi)
                <x-ui.card class="lg:hidden">
                    <h3 class="text-sm font-bold text-ink-900">Daftar isi</h3>
                    <ol class="mt-3 space-y-1.5">
                        @foreach ($daftarIsi as $i => $j)
                            <li>
                                <a href="#{{ $j['id'] }}" class="flex gap-2 text-sm text-ink-600 hover:text-brand-600">
                                    <span class="text-ink-400">{{ $i + 1 }}.</span>{{ $j['isi'] }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </x-ui.card>
            @endif
        </article>

        @if ($daftarIsi)
            <aside class="hidden lg:block">
                <div class="sticky top-24 space-y-4">
                    <div class="rounded-2xl border border-ink-200/70 bg-white p-5 shadow-card">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-ink-400">Daftar isi</h3>
                            <span class="text-xs font-semibold text-brand-600" x-text="`${Math.round(progres)}%`">0%</span>
                        </div>

                        <nav class="mt-3 max-h-[60vh] space-y-0.5 overflow-y-auto scroll-slim">
                            @foreach ($daftarIsi as $j)
                                <a href="#{{ $j['id'] }}"
                                   @click.prevent="loncat('{{ $j['id'] }}')"
                                   :class="aktif === '{{ $j['id'] }}'
                                       ? 'border-brand-500 bg-brand-50 font-semibold text-brand-700'
                                       : 'border-transparent text-ink-500 hover:border-ink-300 hover:text-ink-800'"
                                   class="block border-l-2 py-1.5 pl-3 text-[13px] leading-snug transition">
                                    {{ $j['isi'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <button @click="keAtas()"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-ink-200
                                   bg-white py-2.5 text-xs font-semibold text-ink-600 shadow-soft hover:bg-ink-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                        </svg>
                        Kembali ke atas
                    </button>
                </div>
            </aside>
        @endif
    </div>
</div>

@once
    @push('skrip')
        <script>
            function pembacaDokumen() {
                return {
                    progres: 0,
                    aktif: '',

                    pasang() {
                        this.hitung();
                        // passive: gulir tetap mulus di perangkat siswa yang lambat
                        window.addEventListener('scroll', () => this.hitung(), { passive: true });
                        window.addEventListener('resize', () => this.hitung(), { passive: true });
                    },

                    hitung() {
                        const bisaGulir = document.documentElement.scrollHeight - window.innerHeight;
                        this.progres = bisaGulir > 0
                            ? Math.min(100, Math.max(0, (window.scrollY / bisaGulir) * 100))
                            : 100;

                        // Judul terakhir yang sudah melewati garis baca = bagian aktif
                        let kini = '';
                        for (const h of document.querySelectorAll('article h2[id]')) {
                            if (h.getBoundingClientRect().top <= 140) kini = h.id;
                        }
                        this.aktif = kini;
                    },

                    loncat(id) {
                        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    },

                    keAtas() {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    },
                };
            }
        </script>
    @endpush
@endonce
