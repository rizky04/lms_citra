<x-layouts.app title="Master Data" subtitle="Jenjang & setelan platform">
    <x-ui.page-hero title="Master Data" tone="dark"
        subtitle="Kelola jenjang pendidikan dan lihat konfigurasi platform."
        icon="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Tambah jenjang --}}
        <x-ui.section title="Tambah jenjang" desc="Mis. TK, MA, atau jenjang khusus." class="h-fit"
                      icon="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z">
            <form method="POST" action="{{ route('superadmin.master.jenjang.store') }}" class="space-y-4">
                @csrf
                <x-ui.field label="Nama jenjang" name="nama">
                    <input type="text" name="nama" value="{{ old('nama') }}" required class="field" placeholder="mis. MA">
                </x-ui.field>
                <x-ui.btn class="w-full">Tambah</x-ui.btn>
            </form>
            @error('jenjang')
                <p class="mt-3 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </x-ui.section>

        {{-- Daftar jenjang --}}
        <x-ui.section title="Jenjang pendidikan" class="lg:col-span-2" padding="p-0"
                      icon="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5">
            <div class="divide-y divide-ink-50">
                @foreach ($jenjangList as $j)
                    <div class="flex flex-wrap items-center gap-3 px-5 py-3.5" x-data="{ edit: false }">
                        <div class="flex-1" x-show="!edit">
                            <span class="font-semibold text-ink-900">{{ $j->nama }}</span>
                            <span class="ml-2 text-xs text-ink-400">
                                {{ $j->mapel_count }} mapel · {{ $j->kelas_count }} kelas · {{ $j->soal_count }} soal
                            </span>
                        </div>

                        {{-- Form edit inline --}}
                        <form x-cloak x-show="edit" method="POST" action="{{ route('superadmin.master.jenjang.update', $j) }}"
                              class="flex flex-1 items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="nama" value="{{ $j->nama }}" required class="field !w-40 !py-1.5 text-sm">
                            <x-ui.btn size="sm">Simpan</x-ui.btn>
                            <x-ui.btn size="sm" variant="ghost" type="button" @click="edit = false">Batal</x-ui.btn>
                        </form>

                        <div class="flex items-center gap-1" x-show="!edit">
                            <button type="button" @click="edit = true"
                                    class="rounded-lg p-2 text-ink-400 hover:bg-brand-50 hover:text-brand-600" title="Ubah nama">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('superadmin.master.jenjang.destroy', $j) }}"
                                  onsubmit="return confirm('Hapus jenjang {{ $j->nama }}?')">
                                @csrf @method('DELETE')
                                <button @disabled($j->sedangDipakai())
                                        class="rounded-lg p-2 text-ink-400 enabled:hover:bg-rose-50 enabled:hover:text-rose-600 disabled:opacity-30"
                                        title="{{ $j->sedangDipakai() ? 'Masih dipakai, tidak bisa dihapus' : 'Hapus' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.section>
    </div>

    {{-- Setelan platform (read-only, dari .env) --}}
    <x-ui.section title="Setelan platform (Gemini AI)"
                  desc="Dikonfigurasi lewat file .env di server — ditampilkan di sini untuk referensi."
                  icon="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z">
        <dl class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-ink-50 p-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Model</dt>
                <dd class="mt-1 font-mono text-sm font-semibold text-ink-900">{{ $setelan['model'] }}</dd>
            </div>
            <div class="rounded-xl bg-ink-50 p-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Limit AI / sekolah / hari</dt>
                <dd class="mt-1 text-sm font-semibold text-ink-900">{{ $setelan['limit_harian'] }}</dd>
            </div>
            <div class="rounded-xl bg-ink-50 p-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">API key platform</dt>
                <dd class="mt-1">
                    <x-ui.badge :color="$setelan['api_terisi'] ? 'green' : 'rose'">
                        {{ $setelan['api_terisi'] ? 'terpasang' : 'belum diisi' }}
                    </x-ui.badge>
                </dd>
            </div>
        </dl>
    </x-ui.section>
</x-layouts.app>
