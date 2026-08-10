<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuisPercobaan extends Model
{
    use BelongsToSekolah;

    protected $table = 'kuis_percobaans';

    protected $fillable = ['sekolah_id', 'kuis_id', 'user_id', 'percobaan', 'mulai_at'];

    protected $casts = ['mulai_at' => 'datetime'];

    public function kuis(): BelongsTo
    {
        return $this->belongsTo(Kuis::class);
    }

    // Batas waktu absolut percobaan ini (null = tanpa batas waktu).
    public function batasWaktu(): ?\Illuminate\Support\Carbon
    {
        return $this->kuis->durasi_menit
            ? $this->mulai_at->copy()->addMinutes($this->kuis->durasi_menit)
            : null;
    }

    // Toleransi keterlambatan submit akibat jeda jaringan, bukan celah untuk curang.
    public const TOLERANSI_DETIK = 20;

    public function sudahKadaluarsa(): bool
    {
        $batas = $this->batasWaktu();

        return $batas && now()->gt($batas->copy()->addSeconds(self::TOLERANSI_DETIK));
    }

    public function sisaDetik(): ?int
    {
        $batas = $this->batasWaktu();

        return $batas ? max(0, now()->diffInSeconds($batas, false)) : null;
    }
}
