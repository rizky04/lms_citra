@forelse ($riwayat as $r)
    <div class="border-b border-ink-50 px-5 py-3.5 last:border-0">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-ink-900">
                    {{ $r->request_json['topik'] ?? '—' }}
                </div>
                <div class="truncate text-xs text-ink-500">
                    {{ ucfirst($r->jenis) }} · {{ $r->request_json['jenjang'] ?? '' }}
                    · {{ $r->created_at->diffForHumans() }}
                </div>
            </div>

            @php $warna = ['done' => 'green', 'failed' => 'rose', 'processing' => 'amber'][$r->status] ?? 'slate'; @endphp
            <x-ui.badge :color="$warna">{{ $r->status }}</x-ui.badge>
        </div>

        @if ($r->status === 'done')
            <p class="mt-1 text-xs text-emerald-700">
                {{ $r->hasil_json['dibuat'] ?? 0 }} item dibuat sebagai draft.
                @if (($r->hasil_json['jenis'] ?? '') === 'soal')
                    <a href="{{ route('soal.index', ['status' => 'draft']) }}" class="font-semibold underline">Review soal</a>
                @elseif (($r->hasil_json['jenis'] ?? '') === 'materi')
                    <a href="{{ route('materi.index', ['status' => 'draft']) }}" class="font-semibold underline">Review materi</a>
                @else
                    <a href="{{ route('perangkat.index') }}" class="font-semibold underline">Review dokumen</a>
                @endif
            </p>
        @elseif ($r->status === 'failed')
            <p class="mt-1 text-xs text-rose-600">{{ Str::limit($r->error, 100) }}</p>
        @elseif (in_array($r->status, ['queued', 'processing']))
            <p class="mt-1 flex items-center gap-1.5 text-xs text-amber-600">
                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path>
                </svg>
                Sedang diproses — akan diperbarui otomatis.
            </p>
        @endif
    </div>
@empty
    <x-ui.empty title="Belum ada permintaan"
                icon="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z">
        Isi form di sebelah untuk membuat draft pertamamu.
    </x-ui.empty>
@endforelse
