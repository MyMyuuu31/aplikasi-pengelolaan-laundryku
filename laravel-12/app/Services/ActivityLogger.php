<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Catat aktivitas pengguna ke tb_log.
     */
    public static function log(string $aksi, string $deskripsi, string $modul = 'umum'): void
    {
        $user = Auth::user();

        try {
            LogAktivitas::create([
                'id_user' => $user?->id_user,
                'nama_user' => $user?->nama ?? 'Tanpa Login',
                'role' => $user?->role,
                'modul' => $modul,
                'aksi' => $aksi,
                'deskripsi' => mb_substr($deskripsi, 0, 255),
                'ip_address' => request()->ip(),
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Logging tidak boleh menghentikan alur aplikasi.
            report($e);
        }
    }

    public static function berhasil(string $aksi, string $deskripsi, string $modul = 'umum'): void
    {
        self::log($aksi, $deskripsi, $modul);
    }

    public static function gagal(string $aksi, string $deskripsi, string $modul = 'umum'): void
    {
        self::log($aksi, $deskripsi, $modul);
    }
}