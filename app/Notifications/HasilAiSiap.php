<?php

namespace App\Notifications;

use App\Models\AiGenerationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class HasilAiSiap extends Notification
{
    use Queueable;

    public function __construct(public AiGenerationJob $job) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $berhasil = $this->job->status === 'done';
        $label = ['soal' => 'Soal', 'materi' => 'Materi', 'perangkat' => 'Perangkat pembelajaran'][$this->job->jenis] ?? 'Konten';

        return [
            'tipe' => 'ai',
            'judul' => $berhasil ? "{$label} hasil AI siap direview" : "Generate {$label} gagal",
            'pesan' => $berhasil
                ? ($this->job->hasil_json['dibuat'] ?? 0).' item dibuat sebagai draft. Review sebelum dipublish.'
                : Str::limit($this->job->error ?? 'Terjadi kesalahan.', 120),
            'url' => route('ai.index'),
            'sukses' => $berhasil,
        ];
    }
}
