@if (session('impersonator_id'))
    <div class="flex flex-wrap items-center justify-between gap-3 bg-amber-500 px-4 py-2.5 text-sm text-white sm:px-6 lg:px-8">
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            </svg>
            <span>
                Mode <strong>masuk sebagai</strong> {{ auth()->user()->name }}
                @if (auth()->user()->sekolah) · {{ auth()->user()->sekolah->nama }} @endif
            </span>
        </div>
        <form method="POST" action="{{ route('impersonasi.keluar') }}">
            @csrf
            <button class="rounded-lg bg-white/20 px-3 py-1 text-xs font-semibold hover:bg-white/30">
                Keluar & kembali ke super admin
            </button>
        </form>
    </div>
@endif
