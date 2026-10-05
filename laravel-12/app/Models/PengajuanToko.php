<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanToko extends Model
{
    protected $table = 'tb_pengajuan_toko';

    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'nama_owner',
        'username',
        'email',
        'password',
        'nama_outlet',
        'alamat',
        'telepon',
        'deskripsi',
        'status',
        'id_owner',
        'id_admin',
    ];

    protected $hidden = ['password'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_owner', 'id_user');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin', 'id_user');
    }
}