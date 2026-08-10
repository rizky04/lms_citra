<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    use BelongsToSekolah;

    protected $table = 'tugas';

    protected $fillable = [
        'sekolah_id', 'kelas_id', 'guru_id',
        'judul', 'instruksi', 'file_path', 'deadline',
    ];

    protected $casts = ['deadline' => 'datetime'];

    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function guru(): BelongsTo { return $this->belongsTo(User::class, 'guru_id'); }
    public function submisi(): HasMany { return $this->hasMany(SubmisiTugas::class, 'tugas_id'); }
}
