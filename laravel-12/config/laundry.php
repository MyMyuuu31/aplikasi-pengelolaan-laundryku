<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Aplikasi Laundry
    |--------------------------------------------------------------------------
    |
    | Konfigurasi khusus domain laundry: nama outlet aktif, pajak default,
    | format nomor invoice, dan nilai default tampilan dashboard.
    |
    */

    'app' => [
        'nama_aplikasi' => env('APP_LAB_NAME', 'LaundryKu'),
        'tagline' => 'Aplikasi Pengelolaan Laundry',
        'kota' => env('APP_CITY', 'Jakarta'),
    ],

    'pajak' => [
        'default' => (float) env('PAJAK_DEFAULT', 11),
        'nama' => env('PAJAK_NAMA', 'PPN'),
    ],

    'invoice' => [
        'prefix' => env('INVOKE_PREFIX', 'INV'),
        'remarks' => (int) env('INVOKE_REMARKS', 5),
    ],

    'transaksi' => [
        'diskon_max' => 100,
        'metode_bayar' => ['tunai', 'transfer', 'qris'],
        'status' => [
            'proses' => ['label' => 'Sedang Diproses', 'color' => 'warning'],
            'selesai' => ['label' => 'Selesai (Siap Diambil)', 'color' => 'info'],
            'diambil' => ['label' => 'Sudah Diambil', 'color' => 'success'],
            'dibatalkan' => ['label' => 'Dibatalkan', 'color' => 'danger'],
        ],
    ],

    'seeder' => [
        'nama_outlet' => env('LAUNDRY_DEFAULT_OUTLET', 'LaundryKu Pusat'),
        'users' => [
            'admin' => [
                'nama' => 'Administrator',
                'username' => env('LAUNDRY_ADMIN_USERNAME', 'admin'),
                'password' => env('LAUNDRY_ADMIN_PASSWORD'),
            ],
            'kasir' => [
                'nama' => 'Kasir Laundry',
                'username' => env('LAUNDRY_KASIR_USERNAME', 'kasir'),
                'password' => env('LAUNDRY_KASIR_PASSWORD'),
            ],
            'owner' => [
                'nama' => 'Pemilik Laundry',
                'username' => env('LAUNDRY_OWNER_USERNAME', 'owner'),
                'password' => env('LAUNDRY_OWNER_PASSWORD'),
            ],
        ],
    ],

];