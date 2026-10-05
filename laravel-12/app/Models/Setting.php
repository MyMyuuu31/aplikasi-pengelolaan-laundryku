<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'tb_setting';

    protected $primaryKey = 'id_setting';

    protected $fillable = ['kunci', 'nilai', 'keterangan'];

    public static function get(string $kunci, mixed $default = null): mixed
    {
        return static::where('kunci', $kunci)->value('nilai') ?? $default;
    }
}