<x-layouts.app title="Kelas" subtitle="Kelola kelas dan kode undangan siswa">
    <x-ui.page-hero title="Kelas" tone="dark"
        subtitle="Buat kelas, bagikan kode undangannya, dan siswa langsung bergabung sendiri."
        icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"
        :meta="[
            ['label' => $kelas->count().' kelas'],
            ['label' => $kelas->sum('siswa_count').' siswa terdaftar'],
        ]" />

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Form buat kelas --}}
        <x-ui.section title="Buat kelas baru" desc="Kode undangan dibuat otomatis." class="h-fit lg:col-span-1"
                      icon="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z">
            <form method="POST" action="{{ route('kelas.store') }}" class="space-y-4">
                @csrf
                <x-ui.field label="Nama kelas" name="nama">
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           class="field" placeholder="mis. 7A / XI RPL 2">
                </x-ui.field>

                <x-ui.field label="Jenjang" name="jenjang_id">
                    <select name="jenjang_id" class="field">
                        @foreach ($jenjangList as $j)
                            <option value="{{ $j->id }}" @selected(old('jenjang_id') == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.btn class="w-full">Buat kelas</x-ui.btn>
            </form>
        </x-ui.section>

        {{-- Daftar kelas --}}
        <div class="lg:col-span-2">
            @if ($kelas->isEmpty())
                <x-ui.card>
                    <x-ui.empty title="Belum ada kelas"
                                icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z">
                        Buat kelas pertamamu, lalu bagikan kode undangannya ke siswa.
                    </x-ui.empty>
                </x-ui.card>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($kelas as $k)
                        @php
                            // Warna aksen per jenjang biar kelas mudah dibedakan sekilas.
                            $aksen = [
                                'SD' => ['from-emerald-400 to-emerald-600', 'bg-emerald-50 text-emerald-700'],
                                'SMP' => ['from-sky-400 to-sky-600', 'bg-sky-50 text-sky-700'],
                                'SMA' => ['from-violet-400 to-violet-600', 'bg-violet-50 text-violet-700'],
                                'SMK' => ['from-amber-400 to-orange-600', 'bg-amber-50 text-amber-700'],
                            ][$k->jenjang->nama] ?? ['from-brand-400 to-brand-600', 'bg-brand-50 text-brand-700'];
                        @endphp

                        <div class="group relative overflow-hidden rounded-2xl border border-ink-200/70 bg-white shadow-card
                                    transition hover:-translate-y-0.5 hover:shadow-lift">
                            <div class="h-1.5 bg-gradient-to-r {{ $aksen[0] }}"></div>

                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold {{ $aksen[1] }}">
                                            {{ Str::upper(Str::substr($k->jenjang->nama, 0, 2)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <a href="{{ route('kelas.show', $k) }}"
                                               class="block truncate text-base font-bold text-ink-900 hover:text-brand-600">{{ $k->nama }}</a>
                                            <p class="text-xs text-ink-500">{{ $k->jenjang->nama }} · {{ $k->siswa_count }} siswa</p>
                                        </div>
                                    </div>

                                    <form action="{{ route('kelas.destroy', $k) }}" method="POST"
                                          onsubmit="return confirm('Hapus kelas {{ $k->nama }}? Semua kuis di kelas ini ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-1.5 text-ink-300 opacity-0 transition hover:bg-rose-50
                                                       hover:text-rose-600 focus:opacity-100 group-hover:opacity-100" title="Hapus kelas">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                {{-- Kode undangan, bisa disalin sekali klik --}}
                                <div x-data="{ tersalin: false }" class="mt-4">
                                    <div class="text-[11px] font-medium uppercase tracking-wide text-ink-400">Kode undangan siswa</div>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ $k->kode_undangan }}');
                                                    tersalin = true; setTimeout(() => tersalin = false, 1800)"
                                            class="mt-1 flex w-full items-center justify-between gap-3 rounded-xl border border-dashed
                                                   border-ink-300 bg-ink-50 px-3 py-2.5 transition hover:border-brand-400 hover:bg-brand-50">
                                        <span class="font-mono text-lg font-bold tracking-widest text-brand-700">{{ $k->kode_undangan }}</span>
                                        <span class="flex items-center gap-1 text-xs font-semibold"
                                              :class="tersalin ? 'text-emerald-600' : 'text-ink-400'">
                                            <svg x-show="!tersalin" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                            </svg>
                                            <svg x-cloak x-show="tersalin" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            <span x-text="tersalin ? 'tersalin' : 'salin'">salin</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
