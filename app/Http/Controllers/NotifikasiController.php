<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifikasi.index', [
            'notifikasi' => $request->user()->notifications()->paginate(20),
        ]);
    }

    public function baca(Request $request, string $id): RedirectResponse
    {
        $notif = $request->user()->notifications()->findOrFail($id);
        $notif->markAsRead();

        return redirect($this->tujuan($notif->data['url'] ?? null));
    }

    /**
     * Notifikasi lama (dibuat di queue saat APP_URL=localhost) menyimpan URL
     * absolut "http://localhost/...". Ambil path+query-nya saja supaya tetap
     * membuka di host yang benar, bukan localhost pengunjung.
     */
    private function tujuan(?string $url): string
    {
        if (blank($url)) {
            return route('notifikasi.index');
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $query = parse_url($url, PHP_URL_QUERY);

            return $path.($query ? '?'.$query : '');
        }

        return $url; // sudah relatif
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Semua notifikasi ditandai terbaca.');
    }
}
