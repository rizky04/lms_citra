<?php

namespace App\Notifications;

use App\Models\Tugas;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TugasBaru extends Notification
{
    use Queueable;

    public function __construct(public Tugas $tugas) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipe' => 'tugas',
            'judul' => 'Tugas baru: '.$this->tugas->judul,
            'pesan' => $this->tugas->deadline
                ? 'Tenggat '.$this->tugas->deadline->translatedFormat('d M Y, H:i')
                : 'Tanpa batas waktu.',
            'url' => route('tugas.saya.show', $this->tugas),
            'sukses' => true,
        ];
    }
}
