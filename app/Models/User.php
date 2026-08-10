<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Role;
use Spatie\Permission\Traits\HasRoles;

// User TIDAK pakai BelongsToSekolah global scope: user adalah boundary auth
// (login by email lintas sekolah, super admin tanpa sekolah). Scope listing
// user dilakukan manual lewat ->where('sekolah_id', ...) di controller.
#[Fillable(['sekolah_id', 'name', 'email', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    // Kelas yang diikuti (siswa)
    public function kelasDiikuti(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa')->withTimestamps();
    }

    // --- Role helpers (tipis di atas spatie, dipakai di views) ---
    public function isSuperAdmin(): bool { return $this->hasRole(Role::SUPER_ADMIN); }
    public function isAdminSekolah(): bool { return $this->hasRole(Role::ADMIN_SEKOLAH); }
    public function isGuru(): bool { return $this->hasRole(Role::GURU); }
    public function isSiswa(): bool { return $this->hasRole(Role::SISWA); }
    public function isPengajar(): bool { return $this->hasAnyRole([Role::GURU, Role::ADMIN_SEKOLAH]); }
    public function isActive(): bool { return $this->status === 'active'; }

    // Label role untuk ditampilkan.
    public function labelRole(): string
    {
        return str($this->getRoleNames()->first() ?? '-')->replace('_', ' ')->title()->value();
    }
}
