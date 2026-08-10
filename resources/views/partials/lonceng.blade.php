@php $belumDibaca = auth()->user()->unreadNotifications()->count(); @endphp

<a href="{{ route('notifikasi.index') }}"
   class="relative shrink-0 rounded-xl p-2 text-ink-500 hover:bg-ink-100 hover:text-ink-800"
   title="Notifikasi">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
    </svg>

    @if ($belumDibaca > 0)
        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
            {{ $belumDibaca > 9 ? '9+' : $belumDibaca }}
        </span>
    @endif
</a>
