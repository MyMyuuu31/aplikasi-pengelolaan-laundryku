<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    protected $table = 'tb_paket';

    protected $primaryKey = 'id_paket';

    protected $fillable = [
        'id_outlet',
        'nama_paket',
        'jenis',
        'satuan',
        'harga',
        'estimasi_hari',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'estimasi_hari' => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'id_outlet', 'id_outlet');
    }

    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_paket', 'id_paket');
    }

    public function label(): string
    {
        return $this->nama_paket.' / '.$this->satuan;
    }
}