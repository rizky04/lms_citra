<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kuis extends Model
{
    use BelongsToSekolah;

    protected $table = 'kuis';

    protected $fillable = [
        'sekolah_id', 'kelas_id', 'guru_id', 'judul',
        'durasi_menit', 'acak_soal', 'max_percobaan',
        'mulai_at', 'selesai_at', 'status',
    ];

    protected $casts = [
        'acak_soal' => 'boolean',
        'mulai_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function guru(): BelongsTo { return $this->belongsTo(User::class, 'guru_id'); }

    public function soal(): BelongsToMany
    {
        return $this->belongsToMany(Soal::class, 'kuis_soals', 'kuis_id', 'soal_id')
            ->withPivot('urutan')->orderBy('urutan');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class);
    }

    // Esai/praktik yang belum dikoreksi guru: benar=null (bukan PG) & nilai belum diisi.
    public function jawabanBelumDinilai(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class)->whereNull('benar')->whereNull('nilai');
    }
}
