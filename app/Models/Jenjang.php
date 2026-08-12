<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jenjang extends Model
{
    // Master global — tanpa BelongsToSekolah.
    protected $fillable = ['nama'];

    public function mapel(): HasMany { return $this->hasMany(Mapel::class); }
    public function kelas(): HasMany { return $this->hasMany(Kelas::class); }
    public function soal(): HasMany { return $this->hasMany(Soal::class); }

    // FK ke jenjang cascade-delete lintas sekolah, jadi jenjang yang masih
    // dipakai TIDAK boleh dihapus.
    public function sedangDipakai(): bool
    {
        return $this->mapel()->exists() || $this->kelas()->exists() || $this->soal()->exists();
    }
}
