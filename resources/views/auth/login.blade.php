<x-layouts.auth title="Masuk" heading="Selamat datang kembali" sub="Masuk untuk melanjutkan mengajar atau belajar.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-ui.field label="Email" name="email">
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" class="field" placeholder="nama@sekolah.sch.id">
        </x-ui.field>

        <x-ui.field label="Kata Sandi" name="password">
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="field" placeholder="••••••••">
        </x-ui.field>

        <label class="flex items-center gap-2 text-sm text-ink-600">
            <input type="checkbox" name="remember" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500">
            Ingat saya
        </label>

        <x-ui.btn size="lg" class="w-full">Masuk</x-ui.btn>
    </form>

    <p class="mt-6 text-center text-sm text-ink-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Daftar sekarang</a>
    </p>
</x-layouts.auth>
