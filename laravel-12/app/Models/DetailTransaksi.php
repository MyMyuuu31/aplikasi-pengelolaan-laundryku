<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    protected $table = 'tb_detail_transaksi';

    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_transaksi',
        'id_paket',
        'nama_paket',
        'satuan',
        'harga_satuan',
        'qty',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'qty' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket', 'id_paket');
    }
}