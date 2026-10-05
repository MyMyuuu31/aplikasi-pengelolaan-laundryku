<?php

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

Artisan::command('laundry:admin', function () {
    $this->comment('');
    $this->info('=== Pembuatan Akun Admin Pertama Aplikasi Laundry ===');
    $this->comment('Data akan disimpan secara manual. Isi sesuai kebutuhan Anda.');
    $this->comment('');

    $nama = trim((string) $this->ask('Nama lengkap', 'Administrator'));
    $username = trim((string) $this->ask('Username', 'admin'));
    $password = (string) $this->secret('Password (minimal 6 karakter)');
    $konfirmasi = (string) $this->secret('Ulangi password');

    if (strlen($password) < 6) {
        $this->error('Password minimal 6 karakter.');

        return 1;
    }

    if ($password !== $konfirmasi) {
        $this->error('Konfirmasi password tidak cocok.');

        return 1;
    }

    if (DB::table('tb_user')->where('username', $username)->exists()) {
        $this->error("Username '{$username}' sudah digunakan.");

        return 1;
    }

    $user = User::create([
        'nama' => $nama,
        'username' => $username,
        'password' => Hash::make($password),
        'role' => 'admin',
        'is_active' => true,
    ]);

    LogAktivitas::create([
        'id_user' => $user->id_user,
        'nama_user' => $user->nama,
        'role' => 'admin',
        'modul' => 'auth',
        'aksi' => 'TAMBAH',
        'deskripsi' => "Pembuatan akun admin pertama: {$username}",
        'ip_address' => 'CLI',
        'user_agent' => 'artisan laundry:admin',
    ]);

    $this->comment('');
    $this->info("Akun admin berhasil dibuat untuk username '{$username}'.");
    $this->comment('Silakan login melalui browser dan segera buat akun kasir serta owner.');
    $this->comment('');

    return 0;
})->purpose('Membuat akun admin pertama tanpa seed data');

Artisan::command('laundry:info', function () {
    $this->comment('');
    $this->info('=== Informasi Instalasi Aplikasi Laundry ===');
    $this->table(
        ['Komponen', 'Nilai'],
        [
            ['Versi Laravel', app()->version()],
            ['PHP', PHP_VERSION],
            ['Environment', config('app.env')],
            ['Database', config('database.default').' ('.config('database.connections.'.config('database.default').'.database').')'],
            ['Timezone', config('app.timezone')],
            ['Pajak default', config('laundry.pajak.nama').' '.config('laundry.pajak.default').'%'],
            ['Prefix invoice', config('laundry.invoice.prefix')],
            ['URL Aplikasi', config('app.url')],
        ]
    );

    $jumlah = [
        'Outlet' => DB::table('tb_outlet')->count(),
        'User' => DB::table('tb_user')->count(),
        'Member' => DB::table('tb_member')->count(),
        'Paket' => DB::table('tb_paket')->count(),
        'Transaksi' => DB::table('tb_transaksi')->count(),
        'Log' => DB::table('tb_log')->count(),
    ];

    $this->table(['Tabel', 'Jumlah Data'], array_map(null, array_keys($jumlah), array_values($jumlah)));
    $this->comment('');
})->purpose('Menampilkan informasi instalasi dan jumlah data');