<x-layouts.app title="Pengguna" subtitle="Kelola guru dan siswa di sekolahmu">
    <x-ui.page-hero title="Pengguna" :tone="$pending->isNotEmpty() ? 'amber' : 'dark'"
        :subtitle="$pending->isNotEmpty()
            ? 'Ada '.$pending->count().' guru menunggu persetujuanmu.'
            : 'Kelola guru dan siswa di sekolahmu.'"
        icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"
        :meta="[
            ['label' => $guru->count().' guru & admin'],
            ['label' => $siswa->total().' siswa'],
            ['label' => auth()->user()->sekolah?->nama ?? '—'],
        ]"
        class="print:hidden" />

    {{-- Kartu login hasil pembuatan akun massal (hanya tampil sekali) --}}
    @if (session('kartuLogin'))
        <x-ui.card class="border-amber-300 bg-amber-50/60 print:border-0 print:bg-white">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-amber-900">Kartu login siswa</h3>
                    <p class="mt-1 text-sm text-amber-800">
                        Sandi hanya ditampilkan sekali. Cetak atau catat sekarang sebelum meninggalkan halaman ini.
                    </p>
                </div>
                <x-ui.btn size="sm" variant="secondary" type="button" onclick="window.print()" class="print:hidden">Cetak</x-ui.btn>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (session('kartuLogin') as $k)
                    <div class="rounded-xl border border-amber-200 bg-white p-3">
                        <div class="text-sm font-bold text-ink-900">{{ $k['nama'] }}</div>
                        <dl class="mt-1.5 space-y-0.5 text-xs">
                            <div class="flex gap-1"><dt class="text-ink-400">User:</dt>
                                <dd class="font-mono text-ink-700">{{ $k['email'] }}</dd></div>
                            <div class="flex gap-1"><dt class="text-ink-400">Sandi:</dt>
                                <dd class="font-mono font-bold text-brand-700">{{ $k['sandi'] }}</dd></div>
                        </dl>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    {{-- Guru menunggu persetujuan --}}
    @if ($pending->isNotEmpty())
        <x-ui.card padding="p-0" class="border-amber-300">
            <div class="flex items-center gap-2 border-b border-amber-200 bg-amber-50 px-6 py-4">
                <h3 class="text-sm font-bold text-amber-900">Menunggu persetujuan</h3>
                <x-ui.badge color="amber">{{ $pending->count() }}</x-ui.badge>
            </div>

            @foreach ($pending as $u)
                <div class="flex flex-wrap items-center gap-3 border-b border-ink-50 px-6 py-4 last:border-0">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">
                        {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-ink-900">{{ $u->name }}</div>
                        <div class="truncate text-xs text-ink-500">{{ $u->email }} · daftar {{ $u->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.user.approve', $u) }}">
                            @csrf
                            <x-ui.btn size="sm">Setujui</x-ui.btn>
                        </form>
                        <form method="POST" action="{{ route('admin.user.tolak', $u) }}"
                              onsubmit="return confirm('Tolak dan hapus pendaftaran {{ $u->name }}?')">
                            @csrf
                            <x-ui.btn size="sm" variant="secondary">Tolak</x-ui.btn>
                        </form>
                    </div>
                </div>
            @endforeach
        </x-ui.card>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Buat akun siswa massal --}}
        <x-ui.card class="h-fit print:hidden">
            <h3 class="text-sm font-bold text-ink-900">Buatkan akun siswa</h3>
            <p class="mt-1 text-sm text-ink-500">
                Untuk siswa yang belum punya email sendiri. Username & sandi dibuat otomatis.
            </p>

            <form method="POST" action="{{ route('admin.user.siswa.store') }}" class="mt-4 space-y-4">
                @csrf
                <x-ui.field label="Masukkan ke kelas" name="kelas_id">
                    <select name="kelas_id" class="field" required>
                        <option value="">— pilih kelas —</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Daftar nama" name="daftar_nama" hint="Satu nama per baris, maks 200.">
                    <textarea name="daftar_nama" rows="8" required class="field"
                              placeholder="Budi Santoso&#10;Siti Aminah&#10;Andi Wijaya">{{ old('daftar_nama') }}</textarea>
                </x-ui.field>

                <x-ui.btn class="w-full">Buat akun</x-ui.btn>
            </form>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            {{-- Guru --}}
            <x-ui.card padding="p-0">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-ink-900">Guru & admin</h3>
                    <x-ui.badge>{{ $guru->count() }}</x-ui.badge>
                </div>

                @forelse ($guru as $u)
                    <div class="flex items-center gap-3 border-b border-ink-50 px-6 py-3.5 last:border-0">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">
                            {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-ink-900">{{ $u->name }}</div>
                            <div class="truncate text-xs text-ink-500">{{ $u->email }}</div>
                        </div>
                        <x-ui.badge :color="$u->isAdminSekolah() ? 'brand' : 'slate'">{{ $u->labelRole() }}</x-ui.badge>

                        @if ($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.user.destroy', $u) }}"
                                  onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-1.5 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <x-ui.empty title="Belum ada guru lain" />
                @endforelse
            </x-ui.card>

            {{-- Siswa --}}
            <x-ui.card padding="p-0">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-ink-900">Siswa</h3>
                    <x-ui.badge>{{ $siswa->total() }}</x-ui.badge>
                </div>

                @forelse ($siswa as $u)
                    <div class="flex items-center gap-3 border-b border-ink-50 px-6 py-3.5 last:border-0">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-ink-100 text-xs font-bold text-ink-600">
                            {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-ink-900">{{ $u->name }}</div>
                            <div class="truncate text-xs text-ink-500">{{ $u->email }}</div>
                        </div>
                        <x-ui.badge>{{ $u->kelas_diikuti_count }} kelas</x-ui.badge>

                        <form method="POST" action="{{ route('admin.user.destroy', $u) }}"
                              onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg p-1.5 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <x-ui.empty title="Belum ada siswa">
                        Siswa bisa daftar sendiri pakai kode kelas, atau buatkan akunnya di panel sebelah.
                    </x-ui.empty>
                @endforelse

                @if ($siswa->hasPages())
                    <div class="border-t border-ink-100 p-4">{{ $siswa->links() }}</div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
