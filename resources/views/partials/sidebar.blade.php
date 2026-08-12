@php
    $user = auth()->user();

    $ikon = [
        'grid' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        'users' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        'bank' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        'quiz' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z',
        'play' => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z',
        'task' => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z',
        'check' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'spark' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z',
        'doc' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'tag' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3ZM6 6h.008v.008H6V6Z',
        'gear' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        'sekolah' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    ];

    $menu = [];
    if ($user->isPengajar()) {
        $menu = [
            ['route' => 'dashboard', 'aktif' => 'dashboard', 'label' => 'Dashboard', 'ikon' => $ikon['grid']],
            ['route' => 'kelas.index', 'aktif' => 'kelas.*', 'label' => 'Kelas', 'ikon' => $ikon['users']],
            ['route' => 'materi.index', 'aktif' => 'materi.*', 'label' => 'Materi', 'ikon' => $ikon['bank']],
            ['route' => 'soal.index', 'aktif' => 'soal.*', 'label' => 'Bank Soal', 'ikon' => $ikon['bank']],
            ['route' => 'mapel.index', 'aktif' => 'mapel.*', 'label' => 'Mata Pelajaran', 'ikon' => $ikon['tag']],
            ['route' => 'kuis.index', 'aktif' => 'kuis.*', 'label' => 'Kuis', 'ikon' => $ikon['quiz']],
            ['route' => 'tugas.index', 'aktif' => 'tugas.*', 'label' => 'Tugas', 'ikon' => $ikon['task']],
            ['route' => 'koreksi.index', 'aktif' => 'koreksi.*', 'label' => 'Koreksi', 'ikon' => $ikon['check']],
            ['route' => 'perangkat.index', 'aktif' => 'perangkat.*', 'label' => 'Perangkat Ajar', 'ikon' => $ikon['doc']],
            ['route' => 'laporan.index', 'aktif' => 'laporan.*', 'label' => 'Laporan', 'ikon' => $ikon['chart']],
            ['route' => 'ai.index', 'aktif' => 'ai.*', 'label' => 'Asisten AI', 'ikon' => $ikon['spark']],
        ];
        if ($user->isAdminSekolah()) {
            $menu[] = ['route' => 'admin.user.index', 'aktif' => 'admin.user.*', 'label' => 'Pengguna', 'ikon' => $ikon['users']];
            $menu[] = ['route' => 'admin.sekolah.edit', 'aktif' => 'admin.sekolah.*', 'label' => 'Pengaturan Sekolah', 'ikon' => $ikon['gear']];
        }
    } elseif ($user->isSiswa()) {
        $menu = [
            ['route' => 'dashboard', 'aktif' => 'dashboard', 'label' => 'Dashboard', 'ikon' => $ikon['grid']],
            ['route' => 'materi.baca.index', 'aktif' => 'materi.baca.*', 'label' => 'Materi', 'ikon' => $ikon['bank']],
            ['route' => 'kerjakan.index', 'aktif' => 'kerjakan.*', 'label' => 'Kuis Saya', 'ikon' => $ikon['play']],
            ['route' => 'tugas.saya.index', 'aktif' => 'tugas.saya.*', 'label' => 'Tugas', 'ikon' => $ikon['task']],
            ['route' => 'rapor.index', 'aktif' => 'rapor.*', 'label' => 'Rapor Saya', 'ikon' => $ikon['chart']],
        ];
    } else {
        // Super admin platform
        $menu = [
            ['route' => 'dashboard', 'aktif' => 'dashboard', 'label' => 'Dashboard', 'ikon' => $ikon['grid']],
            ['route' => 'superadmin.sekolah.index', 'aktif' => 'superadmin.sekolah.*', 'label' => 'Kelola Sekolah', 'ikon' => $ikon['sekolah']],
            ['route' => 'superadmin.pengguna.index', 'aktif' => 'superadmin.pengguna.*', 'label' => 'Manajemen Peran', 'ikon' => $ikon['users']],
            ['route' => 'superadmin.master.index', 'aktif' => 'superadmin.master.*', 'label' => 'Master Data', 'ikon' => $ikon['gear']],
        ];
    }
@endphp

{{-- Brand --}}
<div class="flex h-16 shrink-0 items-center gap-3 px-5">
    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 shadow-lift">
        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
        </svg>
    </div>
    <div class="min-w-0">
        <div class="truncate text-sm font-extrabold tracking-tight text-white">LMS Citra</div>
        <div class="truncate text-[11px] text-ink-400">{{ $user->sekolah?->nama ?? 'Platform' }}</div>
    </div>
</div>

{{-- Navigasi --}}
<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
    @foreach ($menu as $item)
        @php $aktif = request()->routeIs($item['aktif']); @endphp
        <a href="{{ route($item['route']) }}"
           @class([
               'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
               'bg-brand-600 text-white shadow-lift' => $aktif,
               'text-ink-300 hover:bg-white/5 hover:text-white' => ! $aktif,
           ])>
            <svg class="h-5 w-5 shrink-0 {{ $aktif ? 'text-white' : 'text-ink-400 group-hover:text-brand-300' }}"
                 fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['ikon'] }}" />
            </svg>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>

{{-- Kartu identitas bawah --}}
<div class="border-t border-white/10 p-3">
    <div class="flex items-center gap-3 rounded-xl bg-white/5 px-3 py-2.5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-500/20 text-sm font-bold text-brand-200">
            {{ Str::upper(Str::substr($user->name, 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="truncate text-sm font-semibold text-white">{{ $user->name }}</div>
            <div class="truncate text-[11px] text-ink-400">{{ $user->labelRole() }}</div>
        </div>
    </div>
</div>
