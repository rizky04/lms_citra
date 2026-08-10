<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationJob extends Model
{
    use BelongsToSekolah;

    protected $table = 'ai_generation_jobs';

    protected $fillable = [
        'sekolah_id', 'guru_id', 'jenis',
        'request_json', 'status', 'hasil_json', 'error',
    ];

    protected $casts = [
        'request_json' => 'array',
        'hasil_json' => 'array',
    ];

    public function guru(): BelongsTo { return $this->belongsTo(User::class, 'guru_id'); }
}
