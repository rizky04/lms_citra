<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanSiswa extends Model
{
    use BelongsToSekolah;

    protected $table = 'jawaban_siswas';

    protected $fillable = [
        'sekolah_id', 'kuis_id', 'soal_id', 'user_id',
        'percobaan', 'jawaban', 'benar', 'nilai',
    ];

    protected $casts = [
        'benar' => 'boolean',
        'nilai' => 'decimal:2',
    ];

    public function kuis(): BelongsTo { return $this->belongsTo(Kuis::class); }
    public function soal(): BelongsTo { return $this->belongsTo(Soal::class); }
    public function siswa(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
