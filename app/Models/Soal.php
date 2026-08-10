<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Soal extends Model
{
    use BelongsToSekolah;

    protected $table = 'soals';

    protected $fillable = [
        'sekolah_id', 'mapel_id', 'jenjang_id', 'guru_id',
        'tipe', 'pertanyaan', 'opsi_json', 'jawaban_benar',
        'bobot', 'tingkat', 'tag', 'status', 'sumber',
    ];

    protected $casts = [
        'opsi_json' => 'array',
    ];

    public function mapel(): BelongsTo { return $this->belongsTo(Mapel::class); }
    public function jenjang(): BelongsTo { return $this->belongsTo(Jenjang::class); }
    public function guru(): BelongsTo { return $this->belongsTo(User::class, 'guru_id'); }
}
