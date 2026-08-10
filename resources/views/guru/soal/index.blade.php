<x-layouts.app title="Bank Soal" subtitle="Kumpulan soal yang bisa dipakai ulang di banyak kuis">
    <x-slot:actions>
        <x-ui.btn size="sm" variant="secondary" :href="route('soal.io')">Import / Export</x-ui.btn>
        <x-ui.btn size="sm" :href="route('soal.create')">+ Soal baru</x-ui.btn>
    </x-slot:actions>

    <x-ui.page-hero title="Bank Soal" tone="dark"
        subtitle="Sekali dibuat, soal bisa dipakai ulang di berapa pun kuis dan kelas."
        icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
        :meta="[
            ['label' => $ringkas['total'].' soal total'],
            ['label' => $ringkas['pg'].' pilihan ganda'],
            ['label' => $ringkas['draft'].' masih draft'],
        ]">
        <x-slot:action>
            <x-ui.btn variant="secondary" size="sm" :href="route('ai.index')">Buat dengan AI</x-ui.btn>
        </x-slot:action>
    </x-ui.page-hero>

    {{-- Filter --}}
    <x-ui.card padding="p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.field label="Tipe" class="w-40">
                <select name="tipe" class="field">
                    <option value="">Semua tipe</option>
                    @foreach (['pg' => 'Pilihan Ganda', 'esai' => 'Esai', 'praktik' => 'Praktik'] as $k => $l)
                        <option value="{{ $k }}" @selected(request('tipe') === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <x-ui.field label="Status" class="w-40">
                <select name="status" class="field">
                    <option value="">Semua status</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                </select>
            </x-ui.field>

            <x-ui.field label="Tag / bab" class="min-w-[12rem] flex-1">
                <input type="text" name="tag" value="{{ request('tag') }}" class="field" placeholder="mis. Algoritma">
            </x-ui.field>

            <x-ui.btn variant="secondary">Filter</x-ui.btn>
            @if (request()->hasAny(['tipe', 'tag', 'status']))
                <x-ui.btn variant="ghost" :href="route('soal.index')">Reset</x-ui.btn>
            @endif
        </form>
    </x-ui.card>

    {{-- Daftar --}}
    @if ($soal->isEmpty())
        <x-ui.card>
            <x-ui.empty title="Belum ada soal"
                        icon="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25">
                {{ request()->hasAny(['tipe', 'tag', 'status']) ? 'Tidak ada soal yang cocok dengan filter.' : 'Mulai bangun bank soalmu — sekali buat, bisa dipakai di banyak kuis.' }}
                <x-slot:action>
                    <x-ui.btn :href="route('soal.create')" size="sm">+ Soal baru</x-ui.btn>
                </x-slot:action>
            </x-ui.empty>
        </x-ui.card>
    @else
        <x-ui.card padding="p-0">
            <div class="divide-y divide-ink-50">
                @foreach ($soal as $s)
                    <div x-data="{ buka: false }" class="px-5 py-4 transition hover:bg-ink-50/50">
                      <div class="flex items-start gap-4">
                        <span @class([
                            'mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-[10px] font-extrabold uppercase',
                            'bg-brand-50 text-brand-700' => $s->tipe === 'pg',
                            'bg-amber-50 text-amber-700' => $s->tipe === 'esai',
                            'bg-emerald-50 text-emerald-700' => $s->tipe === 'praktik',
                        ])>{{ Str::substr($s->tipe, 0, 2) }}</span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium leading-snug text-ink-900">
                                {{ Str::limit(strip_tags($s->pertanyaan), 110) }}
                            </p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                <x-ui.badge>{{ $s->mapel?->nama }}</x-ui.badge>
                                <x-ui.badge color="brand">{{ $s->jenjang?->nama }}</x-ui.badge>
                                @if ($s->tag)<x-ui.badge>#{{ $s->tag }}</x-ui.badge>@endif
                                @if ($s->tingkat)<x-ui.badge color="amber">{{ $s->tingkat }}</x-ui.badge>@endif
                                <x-ui.badge :color="$s->status === 'published' ? 'green' : 'slate'">{{ $s->status }}</x-ui.badge>
                                @if ($s->sumber === 'ai_generated')<x-ui.badge color="green">AI</x-ui.badge>@endif
                                @if ($s->sumber === 'import')<x-ui.badge>import</x-ui.badge>@endif

                                @if ($s->tipe === 'pg' && $s->opsi_json)
                                    <button type="button" @click="buka = !buka"
                                            class="text-xs font-semibold text-brand-600 hover:underline"
                                            x-text="buka ? 'sembunyikan opsi' : 'lihat opsi'">lihat opsi</button>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <a href="{{ route('soal.edit', $s) }}"
                               class="rounded-lg p-2 text-ink-400 hover:bg-brand-50 hover:text-brand-600" title="Edit">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                </svg>
                            </a>
                            <form action="{{ route('soal.destroy', $s) }}" method="POST"
                                  onsubmit="return confirm('Hapus soal ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-2 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.2v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                      </div>

                      {{-- Pratinjau opsi PG + kunci jawaban --}}
                      @if ($s->tipe === 'pg' && $s->opsi_json)
                          <div x-cloak x-show="buka" x-transition.opacity
                               class="mt-3 grid gap-1.5 sm:ms-[52px] sm:grid-cols-2">
                              @foreach ($s->opsi_json as $huruf => $teks)
                                  <div @class([
                                      'flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs',
                                      'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-200' => $huruf === $s->jawaban_benar,
                                      'bg-ink-50 text-ink-600' => $huruf !== $s->jawaban_benar,
                                  ])>
                                      <span class="font-bold">{{ $huruf }}</span>
                                      <span class="min-w-0 flex-1 truncate">{{ $teks }}</span>
                                      @if ($huruf === $s->jawaban_benar)
                                          <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                          </svg>
                                      @endif
                                  </div>
                              @endforeach
                          </div>
                      @endif
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <div>{{ $soal->links() }}</div>
    @endif
</x-layouts.app>
