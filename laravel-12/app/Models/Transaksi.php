<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    protected $table = 'tb_transaksi';

    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'no_invoice',
        'id_outlet',
        'id_member',
        'id_user',
        'total_harga',
        'diskon',
        'diskon_persen',
        'pajak_persen',
        'pajak',
        'total_bayar',
        'uang_diterima',
        'total_kembali',
        'metode_bayar',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'total_harga' => 'decimal:2',
            'diskon' => 'decimal:2',
            'diskon_persen' => 'decimal:2',
            'pajak_persen' => 'decimal:2',
            'pajak' => 'decimal:2',
            'total_bayar' => 'decimal:2',
            'uang_diterima' => 'decimal:2',
            'total_kembali' => 'decimal:2',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'id_outlet', 'id_outlet');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'id_member', 'id_member');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function statusLabel(): string
    {
        return config('laundry.transaksi.status.'.$this->status.'.label') ?? ucfirst((string) $this->status);
    }

    public function statusColor(): string
    {
        return config('laundry.transaksi.status.'.$this->status.'.color') ?? 'secondary';
    }

    public function namaPelanggan(): string
    {
        return $this->member?->nama ?? 'Pelanggan Umum';
    }
}