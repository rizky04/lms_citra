<x-layouts.app title="Pengaturan Profil">
    <x-ui.page-hero :title="$user->name" tone="dark"
        :subtitle="$user->email"
        icon="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
        :meta="[
            ['label' => $user->labelRole()],
            ['label' => $user->sekolah?->nama ?? 'Platform'],
            ['label' => 'bergabung '.$user->created_at->translatedFormat('F Y')],
        ]" />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Info akun --}}
            <x-ui.card>
                <h3 class="text-sm font-bold text-ink-900">Informasi Akun</h3>
                <p class="mt-1 text-sm text-ink-500">Perbarui nama dan email yang dipakai untuk masuk.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                    @csrf @method('PATCH')

                    <x-ui.field label="Nama" name="name">
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="field">
                    </x-ui.field>

                    <x-ui.field label="Email" name="email">
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="field">
                    </x-ui.field>

                    <x-ui.btn>Simpan perubahan</x-ui.btn>
                </form>
            </x-ui.card>

            {{-- Ganti sandi --}}
            <x-ui.card>
                <h3 class="text-sm font-bold text-ink-900">Ganti Kata Sandi</h3>
                <p class="mt-1 text-sm text-ink-500">Gunakan sandi yang panjang dan unik.</p>

                <form method="POST" action="{{ route('profile.password') }}" class="mt-5 space-y-4">
                    @csrf @method('PUT')

                    <x-ui.field label="Sandi saat ini" name="current_password">
                        <input type="password" name="current_password" required autocomplete="current-password" class="field">
                    </x-ui.field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Sandi baru" name="password">
                            <input type="password" name="password" required autocomplete="new-password" class="field">
                        </x-ui.field>
                        <x-ui.field label="Ulangi sandi baru" name="password_confirmation">
                            <input type="password" name="password_confirmation" required autocomplete="new-password" class="field">
                        </x-ui.field>
                    </div>

                    <x-ui.btn>Perbarui sandi</x-ui.btn>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">
                        {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-bold text-ink-900">{{ $user->name }}</div>
                        <div class="truncate text-xs text-ink-500">{{ $user->email }}</div>
                    </div>
                </div>
                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Peran</dt>
                        <dd><x-ui.badge color="brand">{{ $user->labelRole() }}</x-ui.badge></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Sekolah</dt>
                        <dd class="font-medium text-ink-800">{{ $user->sekolah?->nama ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Status</dt>
                        <dd><x-ui.badge :color="$user->isActive() ? 'green' : 'amber'">{{ $user->status }}</x-ui.badge></dd>
                    </div>
                </dl>
            </x-ui.card>

            {{-- Hapus akun --}}
            <x-ui.card x-data="{ konfirmasi: false }" class="border-rose-200">
                <h3 class="text-sm font-bold text-rose-700">Hapus Akun</h3>
                <p class="mt-1 text-sm text-ink-500">Semua data akunmu dihapus permanen dan tidak bisa dikembalikan.</p>

                <div x-show="! konfirmasi" class="mt-4">
                    <x-ui.btn variant="danger" size="sm" type="button" @click="konfirmasi = true">Hapus akun saya</x-ui.btn>
                </div>

                <form x-cloak x-show="konfirmasi" method="POST" action="{{ route('profile.destroy') }}" class="mt-4 space-y-3">
                    @csrf @method('DELETE')
                    <x-ui.field label="Konfirmasi dengan kata sandi" name="password">
                        <input type="password" name="password" required class="field">
                    </x-ui.field>
                    <div class="flex gap-2">
                        <x-ui.btn variant="danger" size="sm">Ya, hapus permanen</x-ui.btn>
                        <x-ui.btn variant="ghost" size="sm" type="button" @click="konfirmasi = false">Batal</x-ui.btn>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
