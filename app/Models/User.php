<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'desa_id', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'role' => 'string',
        ];
    }

    /** @return BelongsTo<Desa, $this> */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return true;
        }

        $role = $this->getAttribute('role');

        return is_string($role)
            && in_array($role, ['techade', 'admin_desa', 'editor'], true)
            && $this->hasVerifiedEmail();
    }

    public function isAdminDesa(): bool
    {
        return $this->role === 'admin_desa';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isTechade(): bool
    {
        return $this->role === 'techade';
    }

    public function canAccessAllDesa(): bool
    {
        return $this->isTechade();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdminDesa() || $this->isTechade();
    }

    public function canPublish(): bool
    {
        return $this->isAdminDesa() || $this->isTechade();
    }
}
