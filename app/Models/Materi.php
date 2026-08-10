<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materi extends Model
{
    use BelongsToSekolah;

    protected $table = 'materis';

    protected $fillable = [
        'sekolah_id', 'mapel_id', 'kelas_id', 'guru_id',
        'judul', 'konten', 'file_path', 'gambar', 'urutan', 'status', 'sumber',
    ];

    protected $casts = ['gambar' => 'array'];

    public function mapel(): BelongsTo { return $this->belongsTo(Mapel::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function guru(): BelongsTo { return $this->belongsTo(User::class, 'guru_id'); }
}
