<div x-data="{ open: false }" class="relative shrink-0">
    <button @click="open = !open" @keydown.escape.window="open = false"
            class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-sm hover:bg-ink-100">
        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">
            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
        </span>
        <span class="hidden font-medium text-ink-700 sm:block">{{ Str::limit(auth()->user()->name, 18) }}</span>
        <svg class="h-4 w-4 text-ink-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-cloak x-show="open" @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-ink-200 bg-white py-1 shadow-card">
        <div class="border-b border-ink-100 px-4 py-2.5">
            <div class="truncate text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</div>
            <div class="truncate text-xs text-ink-500">{{ auth()->user()->email }}</div>
        </div>
        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-ink-600 hover:bg-ink-50">Pengaturan Profil</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Keluar</button>
        </form>
    </div>
</div>
