# Struktur Folder Aplikasi Pengelolaan Laundry

```
Aplikasi Pengolahan Laundry/
└── laravel-12/
    │
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   │   ├── AuthController.php          Login, logout, session
    │   │   │   ├── Controller.php              Helper: log aktivitas, validasi duplikat, outlet aktif
    │   │   │   ├── DashboardController.php     Ringkasan dashboard
    │   │   │   ├── LaporanController.php       Laporan transaksi, pendapatan, paket, member
    │   │   │   ├── LogAktivitasController.php  Log aktivitas + export
    │   │   │   ├── MemberController.php        CRUD member
    │   │   │   ├── OutletController.php        CRUD outlet
    │   │   │   ├── PaketController.php         CRUD paket laundry
    │   │   │   ├── TransaksiController.php     Transaksi, kembalian, status
    │   │   │   └── UserController.php          CRUD user + role
    │   │   └── Middleware/
    │   │       ├── EnsureRole.php              Pembatasan akses per role
    │   │       └── EnsureUserActive.php         Blokir akun nonaktif
    │   ├── Models/
    │   │   ├── DetailTransaksi.php
    │   │   ├── LogAktivitas.php
    │   │   ├── Member.php
    │   │   ├── Outlet.php
    │   │   ├── Paket.php
    │   │   ├── Setting.php
    │   │   ├── Transaksi.php
    │   │   └── User.php
    │   ├── Providers/
    │   │   └── AppServiceProvider.php
    │   └── Services/
    │       └── ActivityLogger.php              Pencatatan log aktivitas
    │
    ├── bootstrap/
    │   ├── app.php                             Konfigurasi middleware & redirect
    │   └── cache/
    │
    ├── config/
    │   ├── app.php                             Timezone Asia/Jakarta
    │   ├── database.php
    │   ├── laundry.php                          Domain: pajak, invoice, status, metode bayar
    │   ├── session.php
    │   └── ... (config bawaan Laravel)
    │
    ├── database/
    │   ├── laundry_ukk.sql                     Struktur 8 tabel PDM (tanpa data awal)
    │   ├── migrations/
    │   │   ├── 2026_01_01_000100_create_tb_outlet_table.php
    │   │   ├── 2026_01_01_000200_create_tb_user_table.php
    │   │   ├── 2026_01_01_000300_create_tb_member_table.php
    │   │   ├── 2026_01_01_000400_create_tb_paket_table.php
    │   │   ├── 2026_01_01_000500_create_tb_transaksi_table.php
    │   │   ├── 2026_01_01_000600_create_tb_detail_transaksi_table.php
    │   │   ├── 2026_01_01_000700_create_tb_log_table.php
    │   │   └── 2026_01_01_000800_create_tb_setting_table.php
    │   ├── factories/
    │   └── seeders/                            Kosong (tanpa seeder)
    │
    ├── public/
    │   ├── index.php
    │   ├── assets/
    │   │   ├── css/
    │   │   │   ├── bootstrap.min.css           Bootstrap 5.3.3 (lokal)
    │   │   │   └── app.css                     Tema sky blue
    │   │   ├── js/
    │   │   │   ├── bootstrap.bundle.min.js    Bootstrap JS (lokal)
    │   │   │   └── chart.umd.min.js           Chart.js 4.4.1 (lokal)
    │   │   └── fonts/                          Bootstrap Icons
    │   ├── .htaccess
    │   └── favicon.ico
    │
    ├── resources/
    │   ├── views/
    │   │   ├── layouts/app.blade.php           Layout utama (sidebar, navbar, footer)
    │   │   ├── partials/
    │   │   │   ├── flash.blade.php             Pesan sukses/error
    │   │   │   ├── filter.blade.php            Komponen filter periode
    │   │   │   └── page-header.blade.php       Judul halaman + tombol aksi
    │   │   ├── auth/login.blade.php
    │   │   ├── dashboard.blade.php
    │   │   ├── member/                         create, edit, index
    │   │   ├── paket/                          create, edit, index
    │   │   ├── outlet/                         create, edit, index
    │   │   ├── user/                           create, edit, index
    │   │   ├── transaksi/                      create, edit, index, show
    │   │   ├── laporan/                        index, transaksi, pendapatan, paket, member, print-*
    │   │   └── log/index.blade.php
    │   ├── css/app.css
    │   └── js/
    │       ├── app.js                          Helper form, konfirmasi, pencarian
    │       └── bootstrap.js
    │
    ├── routes/
    │   ├── web.php                             56 route aplikasi
    │   └── console.php                         Perintah laundry:admin, laundry:info
    │
    ├── storage/                                Cache, log, session, view
    ├── tests/
    │   ├── TestCase.php
    │   ├── Feature/
    │   │   ├── AuthTest.php                   10 test
    │   │   ├── MasterDataTest.php             12 test
    │   │   └── TransaksiTest.php              9 test
    │   └── Unit/
    │
    ├── vendor/                                 Dependency Composer
    │
    ├── .env                                   Konfigurasi lokal
    ├── .env.example                           Contoh konfigurasi
    ├── artisan                                 CLI Laravel
    ├── composer.json
    ├── phpunit.xml                             Konfigurasi testing (SQLite in-memory)
    └── README.md
```

## Penjelasan Singkat

### Alur Request

```
Browser
  → public/index.php
  → bootstrap/app.php (middleware global)
  → routes/web.php
  → Middleware (auth, role, user aktif)
  → Controller
  → Model (Eloquent)
  → MySQL laundry_ukk
  → View Blade + Bootstrap lokal
```

### Lapisan Keamanan

| Lapisan | Mekanisme |
| --- | --- |
| Autentikasi | Session manual + `EnsureUserActive` |
| Otorisasi | `EnsureRole` per route group |
| Input | Validasi Laravel pada setiap Controller |
| Query | Eloquent + binding parameter (aman dari SQL injection) |
| Perhitungan | Harga, subtotal, pajak, total dihitung server |
| Aktivitas | `ActivityLogger` mencatat setiap aksi penting |
