<x-layouts.auth title="Daftar" heading="Buat akun" sub="Pilih peranmu, sisanya kami sesuaikan otomatis.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5"
          x-data="{ peran: '{{ old('peran', 'guru') }}', modeSekolah: '{{ old('mode_sekolah', 'buat') }}' }">
        @csrf

        {{-- Pilih peran --}}
        <div>
            <span class="label">Daftar sebagai</span>
            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['guru', 'Guru', 'Mengajar & buat soal', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
                    ['siswa', 'Siswa', 'Belajar & kerjakan kuis', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                ] as [$val, $judul, $ket, $path])
                    <label class="cursor-pointer">
                        <input type="radio" name="peran" value="{{ $val }}" x-model="peran" class="peer sr-only">
                        <div class="rounded-xl border border-ink-200 bg-white p-3 text-center transition
                                    peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-500
                                    hover:border-ink-300">
                            <svg class="mx-auto h-6 w-6 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                            </svg>
                            <div class="mt-1.5 text-sm font-semibold text-ink-900">{{ $judul }}</div>
                            <div class="text-[11px] text-ink-500">{{ $ket }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            <x-ui.field name="peran" />
        </div>

        <x-ui.field label="Nama Lengkap" name="name">
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="field" placeholder="Nama lengkap">
        </x-ui.field>

        <x-ui.field label="Email" name="email">
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   autocomplete="username" class="field" placeholder="nama@sekolah.sch.id">
        </x-ui.field>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.field label="Kata Sandi" name="password">
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="field" placeholder="Min. 8 karakter">
            </x-ui.field>
            <x-ui.field label="Ulangi Sandi" name="password_confirmation">
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password" class="field" placeholder="Ulangi sandi">
            </x-ui.field>
        </div>

        {{-- GURU --}}
        <div x-cloak x-show="peran === 'guru'" class="space-y-3 rounded-xl bg-ink-50 p-4">
            <span class="label mb-2">Sekolah</span>
            <div class="grid grid-cols-2 gap-2">
                @foreach ([['buat', 'Buat baru'], ['gabung', 'Gabung']] as [$val, $judul])
                    <label class="cursor-pointer">
                        <input type="radio" name="mode_sekolah" value="{{ $val }}" x-model="modeSekolah" class="peer sr-only">
                        <div class="rounded-lg border border-ink-200 bg-white px-3 py-2 text-center text-sm font-medium text-ink-600 transition
                                    peer-checked:border-brand-500 peer-checked:bg-brand-600 peer-checked:text-white">
                            {{ $judul }}
                        </div>
                    </label>
                @endforeach
            </div>

            <div x-show="modeSekolah === 'buat'">
                <x-ui.field name="nama_sekolah" hint="Kamu otomatis jadi admin sekolah ini.">
                    <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah') }}"
                           class="field" placeholder="mis. SMKN 1 Jakarta">
                </x-ui.field>
            </div>

            <div x-cloak x-show="modeSekolah === 'gabung'">
                <x-ui.field name="sekolah_id" hint="Akun menunggu persetujuan admin sekolah.">
                    <select name="sekolah_id" class="field">
                        <option value="">-- pilih sekolah --</option>
                        @foreach ($sekolahList as $s)
                            <option value="{{ $s->id }}" @selected(old('sekolah_id') == $s->id)>{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
            </div>
        </div>

        {{-- SISWA --}}
        <div x-cloak x-show="peran === 'siswa'" class="rounded-xl bg-ink-50 p-4">
            <x-ui.field label="Kode Kelas" name="kode_kelas" hint="Minta kode ini ke gurumu.">
                <input type="text" name="kode_kelas" value="{{ old('kode_kelas') }}"
                       class="field font-mono uppercase tracking-wider" placeholder="X9K2ABCD">
            </x-ui.field>
        </div>

        <x-ui.btn size="lg" class="w-full">Buat Akun</x-ui.btn>
    </form>

    <p class="mt-6 text-center text-sm text-ink-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Masuk</a>
    </p>
</x-layouts.auth>
