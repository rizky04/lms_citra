<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmisiTugas extends Model
{
    use BelongsToSekolah;

    protected $table = 'submisi_tugas';

    protected $fillable = [
        'sekolah_id', 'tugas_id', 'user_id',
        'isi', 'file_path', 'nilai', 'feedback', 'submitted_at',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function tugas(): BelongsTo { return $this->belongsTo(Tugas::class); }
    public function siswa(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
