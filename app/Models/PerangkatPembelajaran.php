<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerangkatPembelajaran extends Model
{
    use BelongsToSekolah;

    // Jenis dokumen administratif guru (Kurikulum Merdeka).
    public const JENIS = [
        'modul_ajar' => 'Modul Ajar / RPP',
        'prota' => 'Program Tahunan (Prota)',
        'prosem' => 'Program Semester (Prosem)',
        'atp_silabus' => 'ATP / Silabus',
        'kktp' => 'KKTP',
    ];

    protected $table = 'perangkat_pembelajarans';

    protected $fillable = [
        'sekolah_id', 'guru_id', 'mapel_id', 'jenjang_id',
        'jenis', 'judul', 'tahun_ajaran', 'semester',
        'konten', 'file_path', 'status', 'sumber',
    ];

    public function guru(): BelongsTo { return $this->belongsTo(User::class, 'guru_id'); }
    public function mapel(): BelongsTo { return $this->belongsTo(Mapel::class); }
    public function jenjang(): BelongsTo { return $this->belongsTo(Jenjang::class); }
}
