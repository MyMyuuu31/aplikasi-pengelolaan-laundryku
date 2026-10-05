<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $table = 'tb_member';

    protected $primaryKey = 'id_member';

    protected $fillable = [
        'id_outlet',
        'kode_member',
        'nama',
        'no_hp',
        'email',
        'alamat',
        'poin',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'poin' => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'id_outlet', 'id_outlet');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_member', 'id_member');
    }

    public function totalTransaksi(): int
    {
        return $this->transaksis()->count();
    }

    public function totalBelanja(): float
    {
        return (float) $this->transaksis()->sum('total_bayar');
    }
}