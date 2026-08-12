<?php

namespace App\Models;

use App\Support\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sekolah extends Model
{
    protected $fillable = ['nama', 'status', 'gemini_api_key'];

    protected $hidden = ['gemini_api_key'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Admin sekolah aktif pertama — target "masuk sebagai" oleh super admin.
    public function adminUtama(): HasOne
    {
        return $this->hasOne(User::class)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', Role::ADMIN_SEKOLAH))
            ->oldest();
    }

    // Key Gemini efektif: pakai milik sekolah kalau ada, kalau tidak fallback ke platform.
    public function geminiApiKey(): ?string
    {
        return $this->gemini_api_key ?: config('services.gemini.key');
    }
}
