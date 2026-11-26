<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use RuntimeException;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_PETUGAS = 'petugas';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_DIREKSI = 'direksi';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_PETUGAS => 'petugas.dashboard',
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_DIREKSI => 'direksi.dashboard',
            default => throw new RuntimeException('Role tidak dikenali: '.$this->role),
        };
    }
}