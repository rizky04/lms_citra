<x-layouts.app :title="$kuis->judul" :subtitle="$soal->count().' soal · '.$kuis->kelas->nama">
    <form method="POST" action="{{ route('kerjakan.submit', $kuis) }}"
          x-data="pengerjaanKuis({{ $sisaDetik ?? 'null' }})"
          x-init="pasang()"
          @change="hitungTerisi($el)"
          @submit="jikaManual($event)"
          class="mx-auto w-full max-w-3xl space-y-5">
        @csrf

        {{-- Progress + timer --}}
        <div class="sticky top-16 z-10 space-y-2 rounded-2xl border border-ink-200/70 bg-white/95 px-5 py-3.5 shadow-card backdrop-blur">
            <div class="flex items-center justify-between text-sm">
                <span class="font-semibold text-ink-800">Progres</span>
                <span class="text-ink-500"><span x-text="terisi">0</span> / {{ $soal->count() }} terjawab</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-ink-100">
                <div class="h-full rounded-full bg-brand-600 transition-all duration-300"
                     :style="`width: ${total ? (terisi / total * 100) : 0}%`"></div>
            </div>

            @if ($sisaDetik !== null)
                <div x-cloak x-show="true" class="flex items-center justify-between border-t border-ink-100 pt-2 text-sm">
                    <span class="flex items-center gap-1.5" :class="sisa <= 60 ? 'text-rose-600 font-semibold' : 'text-ink-500'">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Sisa waktu
                    </span>
                    <span class="font-mono text-base font-bold" :class="sisa <= 60 ? 'text-rose-600' : 'text-ink-800'" x-text="format(sisa)">--:--</span>
                </div>
            @endif
        </div>

        @foreach ($soal as $i => $s)
            <x-ui.card>
                <div class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-xs font-bold text-white">
                        {{ $i + 1 }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium leading-relaxed text-ink-900">{{ $s->pertanyaan }}</p>

                        @if ($s->tipe === 'pg')
                            <div class="mt-4 space-y-2">
                                @foreach ($s->opsi_json ?? [] as $huruf => $teks)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-ink-200 px-4 py-3 transition
                                                  hover:border-brand-300 hover:bg-brand-50/40
                                                  has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                                        <input type="radio" name="jawaban[{{ $s->id }}]" value="{{ $huruf }}" required
                                               class="h-4 w-4 border-ink-300 text-brand-600 focus:ring-brand-500">
                                        <span class="text-sm font-bold text-ink-500">{{ $huruf }}</span>
                                        <span class="text-sm text-ink-800">{{ $teks }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4">
                                <textarea name="jawaban[{{ $s->id }}]" rows="5" class="field"
                                          placeholder="Tulis jawaban{{ $s->tipe === 'praktik' ? ' atau link hasil praktik' : '' }} di sini…"></textarea>
                                <p class="mt-1.5 text-xs text-ink-400">Dinilai manual oleh guru.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        @endforeach

        <x-ui.card padding="p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-ink-500">Pastikan semua soal sudah dijawab sebelum mengirim.</p>
                <x-ui.btn size="lg">Kirim jawaban</x-ui.btn>
            </div>
        </x-ui.card>
    </form>

    @push('skrip')
    <script>
        function pengerjaanKuis(sisaAwal) {
            return {
                sisa: sisaAwal,       // detik, null = tanpa batas waktu
                terisi: 0,
                total: {{ $soal->count() }},
                otomatis: false,
                timer: null,

                pasang() {
                    if (this.sisa === null) return;
                    // Server adalah sumber kebenaran waktu; ini cuma tampilan berjalan mundur.
                    this.timer = setInterval(() => {
                        this.sisa = Math.max(0, this.sisa - 1);
                        if (this.sisa === 0) {
                            clearInterval(this.timer);
                            this.kirimOtomatis();
                        }
                    }, 1000);
                },

                hitungTerisi(el) {
                    this.terisi = el.querySelectorAll('input[type=radio]:checked').length
                        + [...el.querySelectorAll('textarea')].filter(t => t.value.trim()).length;
                },

                jikaManual(e) {
                    if (this.otomatis) return; // waktu habis: kirim langsung, tanpa konfirmasi
                    if (! confirm('Kirim jawaban sekarang? Jawaban tidak bisa diubah setelah dikirim.')) {
                        e.preventDefault();
                    }
                },

                kirimOtomatis() {
                    this.otomatis = true;
                    this.$el.submit();
                },

                format(detik) {
                    const m = Math.floor(detik / 60).toString().padStart(2, '0');
                    const s = Math.floor(detik % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },
            };
        }
    </script>
    @endpush
</x-layouts.app>
