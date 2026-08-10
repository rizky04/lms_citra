<?php

namespace App\Models\Concerns;

use App\Models\Scopes\SekolahScope;
use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pasang di setiap model yang datanya milik satu sekolah. Otomatis:
 *  - filter semua query ke sekolah user yang login (SekolahScope)
 *  - isi sekolah_id saat create dari user yang login
 */
trait BelongsToSekolah
{
    protected static function bootBelongsToSekolah(): void
    {
        static::addGlobalScope(new SekolahScope);

        static::creating(function (Model $model) {
            if (empty($model->sekolah_id) && auth()->check() && auth()->user()->sekolah_id) {
                $model->sekolah_id = auth()->user()->sekolah_id;
            }
        });
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
