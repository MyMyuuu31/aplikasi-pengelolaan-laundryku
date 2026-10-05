<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    /**
     * Catat aktivitas dengan identitas user yang sedang login.
     */
    protected function logAktivitas(string $aksi, string $deskripsi, string $modul = 'umum'): void
    {
        ActivityLogger::log($aksi, $deskripsi, $modul);
    }

    /**
     * Validasi unik sederhana lintas tabel PDM.
     */
    protected function validasiDuplikat(string $table, string $column, mixed $value, mixed $ignoreId = null, string $pk = 'id'): void
    {
        $query = DB::table($table)->where($column, $value);

        if ($ignoreId !== null) {
            $query->where($pk, '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                $column => 'Nilai tersebut sudah digunakan, silakan pilih yang lain.',
            ]);
        }
    }

    protected function outletAktif(): mixed
    {
        return Auth::check() ? Auth::user()->id_outlet : null;
    }
}