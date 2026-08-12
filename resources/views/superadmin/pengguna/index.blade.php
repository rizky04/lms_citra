@php
    $warnaRole = [
        'super_admin' => 'rose',
        'admin_sekolah' => 'brand',
        'guru' => 'green',
        'siswa' => 'slate',
    ];
@endphp

<x-layouts.app title="Manajemen Peran" subtitle="Kelola peran & akses semua pengguna lintas sekolah">
    <x-ui.page-hero title="Manajemen Peran" tone="dark"
        subtitle="Ubah peran, aktif/suspend, hapus, atau masuk sebagai user mana pun di seluruh platform."
        icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"
        :meta="[['label' => $users->total().' pengguna']]" />

    {{-- Filter --}}
    <x-ui.card padding="p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <x-ui.field label="Cari nama/email" class="min-w-[12rem] flex-1">
                <input type="text" name="q" value="{{ request('q') }}" class="field" placeholder="mis. budi">
            </x-ui.field>
            <x-ui.field label="Sekolah" class="w-48">
                <select name="sekolah_id" class="field">
                    <option value="">Semua sekolah</option>
                    @foreach ($sekolahList as $s)
                        <option value="{{ $s->id }}" @selected(request('sekolah_id') == $s->id)>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Peran" class="w-40">
                <select name="role" class="field">
                    <option value="">Semua peran</option>
                    @foreach ($roleList as $r)
                        <option value="{{ $r }}" @selected(request('role') === $r)>{{ str_replace('_', ' ', ucfirst($r)) }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Status" class="w-36">
                <select name="status" class="field">
                    <option value="">Semua</option>
                    @foreach (['active' => 'Aktif', 'pending' => 'Pending', 'suspended' => 'Suspend'] as $k => $l)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.btn variant="secondary">Filter</x-ui.btn>
            @if (request()->hasAny(['q', 'sekolah_id', 'role', 'status']))
                <x-ui.btn variant="ghost" :href="route('superadmin.pengguna.index')">Reset</x-ui.btn>
            @endif
        </form>
    </x-ui.card>

    {{-- Tabel pengguna --}}
    <x-ui.card padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-ink-100 bg-ink-50/60 text-left text-xs uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Pengguna</th>
                        <th class="px-3 py-3 font-semibold">Sekolah</th>
                        <th class="px-3 py-3 font-semibold">Peran</th>
                        <th class="px-3 py-3 font-semibold">Status</th>
                        <th class="px-3 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @forelse ($users as $u)
                        <tr class="hover:bg-ink-50/40" x-data="{ ubah: false }">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ink-100 text-xs font-bold text-ink-600">
                                        {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="truncate font-semibold text-ink-900">{{ $u->name }}</div>
                                        <div class="truncate text-xs text-ink-500">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-ink-600">{{ $u->sekolah?->nama ?? '—' }}</td>
                            <td class="px-3 py-3">
                                <x-ui.badge :color="$warnaRole[$u->getRoleNames()->first()] ?? 'slate'">{{ $u->labelRole() }}</x-ui.badge>
                            </td>
                            <td class="px-3 py-3">
                                <x-ui.badge :color="['active' => 'green', 'pending' => 'amber', 'suspended' => 'rose'][$u->status] ?? 'slate'">
                                    {{ $u->status }}
                                </x-ui.badge>
                            </td>
                            <td class="px-3 py-3">
                                @if ($u->id === auth()->id())
                                    <span class="block text-right text-xs text-ink-400">akun kamu</span>
                                @else
                                    <div class="flex items-center justify-end gap-1">
                                        {{-- Masuk sebagai --}}
                                        @unless ($u->isSuperAdmin())
                                            <form method="POST" action="{{ route('superadmin.masuk-sebagai', $u) }}"
                                                  onsubmit="return {{ $u->isActive() ? 'confirm(\'Masuk sebagai '.e($u->name).'?\')' : 'false' }}">
                                                @csrf
                                                <button @disabled(! $u->isActive())
                                                        class="rounded-lg p-2 text-ink-400 enabled:hover:bg-brand-50 enabled:hover:text-brand-600 disabled:opacity-30"
                                                        title="{{ $u->isActive() ? 'Masuk sebagai user ini' : 'Akun tidak aktif' }}">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endunless

                                        {{-- Ubah peran --}}
                                        <button type="button" @click="ubah = !ubah"
                                                class="rounded-lg p-2 text-ink-400 hover:bg-ink-100 hover:text-ink-700" title="Ubah peran">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
                                            </svg>
                                        </button>

                                        {{-- Toggle status --}}
                                        <form method="POST" action="{{ route('superadmin.pengguna.toggle', $u) }}">
                                            @csrf
                                            <button class="rounded-lg p-2 text-ink-400 hover:bg-amber-50 hover:text-amber-600"
                                                    title="{{ $u->status === 'active' ? 'Suspend' : 'Aktifkan' }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $u->status === 'active' ? 'M15.75 5.25v13.5m-7.5-13.5v13.5' : 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z' }}" />
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Hapus --}}
                                        <form method="POST" action="{{ route('superadmin.pengguna.destroy', $u) }}"
                                              onsubmit="return confirm('Hapus akun {{ $u->name }} secara permanen?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg p-2 text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.2v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Baris ubah peran (inline) --}}
                                    <div x-cloak x-show="ubah" class="mt-2 flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('superadmin.pengguna.peran', $u) }}" class="flex items-center gap-2">
                                            @csrf @method('PUT')
                                            <select name="role" class="field !w-40 !py-1.5 text-xs">
                                                @foreach ($roleList as $r)
                                                    <option value="{{ $r }}" @selected($u->hasRole($r))>{{ str_replace('_', ' ', ucfirst($r)) }}</option>
                                                @endforeach
                                            </select>
                                            <x-ui.btn size="sm">Simpan peran</x-ui.btn>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-ui.empty title="Tidak ada pengguna cocok filter" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-ink-100 p-4">{{ $users->links() }}</div>
        @endif
    </x-ui.card>

    {{-- Matriks hak akses --}}
    <x-ui.section title="Matriks hak akses per peran"
                  desc="Ringkasan apa yang bisa diakses tiap peran. Mencerminkan pembatasan route aplikasi."
                  icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-ink-100 bg-ink-50/60 text-left text-xs uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Kemampuan</th>
                        @foreach ($roleList as $r)
                            <th class="px-3 py-3 text-center font-semibold">{{ str_replace('_', ' ', ucfirst($r)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-50">
                    @foreach ($matriks as $kemampuan => $peranBisa)
                        <tr>
                            <td class="px-5 py-2.5 text-ink-700">{{ $kemampuan }}</td>
                            @foreach ($roleList as $r)
                                <td class="px-3 py-2.5 text-center">
                                    @if (in_array($r, $peranBisa, true))
                                        <svg class="mx-auto h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <span class="text-ink-200">·</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.section>
</x-layouts.app>
