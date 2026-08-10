<x-layouts.app title="Mata Pelajaran">
    <x-ui.page-hero title="Mata Pelajaran" tone="dark"
        subtitle="Rapikan daftar mapel — perbaiki salah ketik, atau hapus yang tidak terpakai. Mapel dipakai bersama oleh soal, materi, dan perangkat ajar."
        icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
        :meta="[['label' => $mapel->count().' mata pelajaran']]" />

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Tambah --}}
        <x-ui.section title="Tambah mapel" class="h-fit lg:col-span-1"
                      icon="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z">
            <form method="POST" action="{{ route('mapel.store') }}" class="space-y-4">
                @csrf
                <x-ui.field label="Nama mapel" name="nama">
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           class="field" placeholder="mis. Informatika">
                </x-ui.field>
                <x-ui.field label="Jenjang" name="jenjang_id">
                    <select name="jenjang_id" class="field">
                        @foreach ($jenjangList as $j)
                            <option value="{{ $j->id }}" @selected(old('jenjang_id') == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.btn class="w-full">Tambah</x-ui.btn>
            </form>
        </x-ui.section>

        {{-- Daftar --}}
        <div class="lg:col-span-2">
            @error('hapus')<x-ui.alert type="error" class="mb-4">{{ $message }}</x-ui.alert>@enderror

            @if ($mapel->isEmpty())
                <x-ui.card>
                    <x-ui.empty title="Belum ada mata pelajaran">
                        Mapel juga otomatis dibuat saat kamu mengetik nama baru di form soal/materi.
                    </x-ui.empty>
                </x-ui.card>
            @else
                <x-ui.card padding="p-0">
                    <div class="divide-y divide-ink-50">
                        @foreach ($mapel as $m)
                            @php $dipakai = $m->soal_count + $m->materi_count + $m->perangkat_count; @endphp
                            <div x-data="{ edit: false }" class="px-5 py-4">
                                {{-- Tampilan --}}
                                <div x-show="!edit" class="flex items-center gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-ink-900">{{ $m->nama }}</span>
                                            <x-ui.badge color="brand">{{ $m->jenjang->nama }}</x-ui.badge>
                                        </div>
                                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-ink-500">
                                            <span>{{ $m->soal_count }} soal</span>
                                            <span>{{ $m->materi_count }} materi</span>
                                            <span>{{ $m->perangkat_count }} perangkat</span>
                                        </div>
                                    </div>
                                    <button type="button" @click="edit = true"
                                            class="rounded-lg p-2 text-ink-400 hover:bg-brand-50 hover:text-brand-600" title="Ubah">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                        </svg>
                                    </button>
                                    @if ($dipakai === 0)
                                        <form action="{{ route('mapel.destroy', $m) }}" method="POST"
                                              onsubmit="return confirm('Hapus mapel {{ $m->nama }}?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg p-2 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="p-2 text-ink-200" title="Masih dipakai, tak bisa dihapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>

                                {{-- Edit inline --}}
                                <form x-cloak x-show="edit" method="POST" action="{{ route('mapel.update', $m) }}"
                                      class="flex flex-wrap items-end gap-2">
                                    @csrf @method('PUT')
                                    <x-ui.field label="Nama" class="min-w-[10rem] flex-1">
                                        <input type="text" name="nama" value="{{ $m->nama }}" required class="field">
                                    </x-ui.field>
                                    <x-ui.field label="Jenjang" class="w-28">
                                        <select name="jenjang_id" class="field">
                                            @foreach ($jenjangList as $j)
                                                <option value="{{ $j->id }}" @selected($m->jenjang_id == $j->id)>{{ $j->nama }}</option>
                                            @endforeach
                                        </select>
                                    </x-ui.field>
                                    <x-ui.btn size="sm">Simpan</x-ui.btn>
                                    <x-ui.btn size="sm" variant="ghost" type="button" @click="edit = false">Batal</x-ui.btn>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.app>
