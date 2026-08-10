<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    protected $fillable = ['nama', 'status', 'gemini_api_key'];

    protected $hidden = ['gemini_api_key'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Key Gemini efektif: pakai milik sekolah kalau ada, kalau tidak fallback ke platform.
    public function geminiApiKey(): ?string
    {
        return $this->gemini_api_key ?: config('services.gemini.key');
    }
}
