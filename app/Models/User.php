<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use RuntimeException;

class User extends Authenticatable
{
<<<<<<< HEAD
=======
    use HasFactory;
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
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
<<<<<<< HEAD
=======
        'last_login_at',
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
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
<<<<<<< HEAD
=======
            'last_login_at' => 'datetime',
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
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