<x-layouts.app title="Pengaturan Sekolah">
    <x-ui.page-hero :title="$sekolah->nama" tone="dark"
        subtitle="Atur identitas sekolah dan kunci API Gemini yang dipakai untuk fitur AI."
        icon="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
        :meta="[['label' => 'Status: '.ucfirst($sekolah->status)]]" />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Identitas --}}
        <x-ui.section title="Identitas sekolah"
                      icon="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21M3 3h12m-.75 4.5H21a.75.75 0 0 1 .75.75V21">
            <form method="POST" action="{{ route('admin.sekolah.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <x-ui.field label="Nama sekolah" name="nama">
                    <input type="text" name="nama" value="{{ old('nama', $sekolah->nama) }}" required class="field">
                </x-ui.field>
                <x-ui.btn>Simpan</x-ui.btn>
            </form>
        </x-ui.section>

        {{-- API Key --}}
        <x-ui.section title="API Key Gemini (opsional)"
                      desc="Isi kalau sekolah ingin memakai kuota AI sendiri. Kosong = memakai key platform."
                      icon="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z">
            <div class="mb-4 rounded-xl px-3 py-2.5 text-xs {{ $sekolah->gemini_api_key ? 'bg-emerald-50 text-emerald-700' : 'bg-ink-50 text-ink-500' }}">
                @if ($sekolah->gemini_api_key)
                    ✓ Sekolah ini memakai API key sendiri.
                @elseif ($keyPlatformAda)
                    Saat ini memakai key platform (disediakan sistem).
                @else
                    Belum ada key platform maupun key sekolah — fitur AI belum aktif.
                @endif
            </div>

            <form method="POST" action="{{ route('admin.sekolah.apikey') }}" class="space-y-4">
                @csrf @method('PUT')
                <x-ui.field label="API Key" name="gemini_api_key"
                            hint="Ambil di aistudio.google.com/apikey. Disimpan aman, tidak ditampilkan lagi.">
                    <input type="password" name="gemini_api_key" class="field"
                           placeholder="{{ $sekolah->gemini_api_key ? '•••••••• (terisi)' : 'Tempel key di sini' }}">
                </x-ui.field>
                <div class="flex flex-wrap gap-2">
                    <x-ui.btn>Simpan key</x-ui.btn>
                    @if ($sekolah->gemini_api_key)
                        <x-ui.btn variant="secondary" name="hapus_key" value="1"
                                  onclick="return confirm('Hapus key sekolah dan kembali ke key platform?')">
                            Hapus key
                        </x-ui.btn>
                    @endif
                </div>
            </form>
        </x-ui.section>
    </div>
</x-layouts.app>
