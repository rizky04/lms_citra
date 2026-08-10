<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Kelas extends Model
{
    use BelongsToSekolah;

    protected $table = 'kelas';

    protected $fillable = ['sekolah_id', 'jenjang_id', 'wali_guru_id', 'nama', 'kode_undangan'];

    protected static function booted(): void
    {
        static::creating(function (Kelas $kelas) {
            if (empty($kelas->kode_undangan)) {
                $kelas->kode_undangan = strtoupper(Str::random(8));
            }
        });
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(Jenjang::class);
    }

    public function waliGuru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_guru_id');
    }

    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kelas_siswa', 'kelas_id', 'user_id')->withTimestamps();
    }
}
