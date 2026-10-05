<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tb_user';

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'id_outlet',
        'nama',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'id_outlet', 'id_outlet');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_user', 'id_user');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'id_user', 'id_user');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->nama)) ?: [];
        $first = mb_substr($parts[0] ?? '?', 0, 1);
        $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($first.$last);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'kasir' => 'Kasir',
            'owner' => 'Owner',
            default => ucfirst((string) $this->role),
        };
    }
}