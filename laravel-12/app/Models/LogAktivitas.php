<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 'tb_log';

    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_user',
        'nama_user',
        'role',
        'modul',
        'aksi',
        'deskripsi',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}