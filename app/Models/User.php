<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable;

    protected $table = 'auth_user';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'is_superuser',
        'is_staff',
        'is_active',
        'date_joined',
    ];

    protected $hidden = [
        'password',
    ];

    protected $attributes = [
        'is_superuser' => true,
        'is_staff' => true,
        'is_active' => true,
        'first_name' => '',
        'last_name' => '',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_superuser' => 'boolean',
            'is_staff' => 'boolean',
            'is_active' => 'boolean',
            'date_joined' => 'datetime',
            'last_login' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (! $user->username) {
                $user->username = strstr($user->email, '@', true) ?: $user->email;
            }

            if (! $user->date_joined) {
                $user->date_joined = now();
            }
        });
    }

    public function getFilamentName(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->username;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->is_staff;
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'auth_user_groups',
            'user_id',
            'group_id'
        );
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'auth_user_user_permissions',
            'user_id',
            'permission_id'
        );
    }

    public function hasPermission(string $permission): bool
    {
        // Superuser tiene todo
        if ($this->is_superuser) {
            return true;
        }

        // Permiso directo
        if ($this->permissions()
            ->where('codename', $permission)
            ->exists()) {
            return true;
        }

        // Permiso vía grupo
        return $this->groups()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('codename', $permission);
            })
            ->exists();
    }
}
