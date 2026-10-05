<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Outlet extends Model
{
    protected $table = 'tb_outlet';

    protected $primaryKey = 'id_outlet';

    protected $fillable = [
        'nama_outlet',
        'alamat',
        'deskripsi',
        'telepon',
        'email',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_outlet', 'id_outlet');
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class, 'id_outlet', 'id_outlet')->where('role', 'owner');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'id_outlet', 'id_outlet');
    }

    public function pakets(): HasMany
    {
        return $this->hasMany(Paket::class, 'id_outlet', 'id_outlet');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_outlet', 'id_outlet');
    }
}