# Aplikasi Pengelolaan Laundry

Aplikasi web pengelolaan operasional laundry untuk UKK RPL Paket 3.

Dibangun dengan **Laravel 12**, **MySQL (XAMPP)**, **Bootstrap 5.3.3**, **Bootstrap Icons 1.11.3**, dan **Chart.js 4.4.1** yang seluruhnya disimpan lokal di `public/assets` (tanpa Vite/Tailwind).

## Fitur

| Modul | Keterangan |
| --- | --- |
| Auth | Login session, UID kasir, approval pengajuan toko, pencatatan aktivitas |
| Admin platform | Kelola toko, akun owner, antrean pengajuan; tidak melihat akun kasir atau transaksi toko |
| Owner | Kelola profil/deskripsi toko, harga jasa, akun kasir, pelanggan, transaksi, dan laporan outlet sendiri |
| Kasir | Login dengan UID acak, entri/edit transaksi, ubah status, cetak struk, lihat transaksi outlet sendiri |
| Transaksi | Harga dari jasa outlet, kalkulasi server-side, invoice, pembayaran, status termasuk dibatalkan |
| Laporan | Laporan per outlet, filter periode, cetak/Simpan PDF dari browser, dan export CSV |
| Log | Log aktivitas platform dan operasional |
| Log | Log aktivitas lengkap dengan filter dan export CSV |

## Requirement

- PHP 8.2+
- Composer 2
- XAMPP (Apache + MySQL/MariaDB)
- Database `laundry_ukk` (dibuat otomatis oleh file SQL)

## Instalasi

```bash
composer install
copy .env.example .env          # Windows
php artisan key:generate
```

Nyalakan MySQL di XAMPP, buat database kosong `laundry_ukk` lewat phpMyAdmin, lalu
atur koneksi database dan password seed di `.env`. Untuk demo lokal, akun admin
adalah `admin` / `admin123` dan owner `owner` / `owner123`. UID akun kasir dibuat
acak saat seeding dan dapat dilihat owner pada menu **Akun Kasir**; sandinya
`kasir123` untuk seed demo. Ganti semua sandi demo sebelum aplikasi dipublikasikan.

```bash
php artisan migrate --seed
php artisan serve
```

## Database

Migration Laravel membuat 9 tabel aplikasi, termasuk antrean pendaftaran toko:

`tb_outlet`, `tb_user`, `tb_member`, `tb_paket`, `tb_transaksi`, `tb_detail_transaksi`, `tb_log`, `tb_setting`, `tb_pengajuan_toko`

Gunakan migration untuk instalasi baru. `database/laundry_ukk.sql` adalah dump skema lama dan belum memuat alur multi-toko terbaru. Seeder membuat outlet demo dan akun admin, kasir UID, serta owner secara idempoten; password di-hash dan tidak diubah saat seeder dijalankan ulang.

## Role

| Role | Akses |
| --- | --- |
| admin | Daftar toko, akun owner, persetujuan/penolakan pengajuan; tidak melihat kasir atau data transaksi |
| owner | Satu toko; edit profil/deskripsi, CRUD jasa, CRUD kasir, transaksi, pelanggan, laporan toko |
| kasir | Transaksi toko sendiri, pelanggan, status transaksi, invoice/struk; login memakai UID |

Calon owner mengajukan toko lewat `/daftar-toko`. Admin menyetujui pengajuan dari menu **Pengajuan Toko**; approval membuat toko dan akun owner sekaligus.

## Perhitungan

Seluruh perhitungan dilakukan di server pada `TransaksiController`:

- subtotal = jumlah (harga paket dari server × qty)
- diskon transaksi (persen)
- pajak (default PPN 11%)
- total = subtotal − diskon + pajak
- kembalian = uang diterima − total

Halaman cetak laporan dan struk memakai print stylesheet browser. Pilih **Cetak / Simpan PDF** lalu gunakan tujuan **Save as PDF** pada dialog cetak.

Nomor invoice otomatis dengan format `INV-YYYYMMDDxxxxx`.

## Testing

```bash
php artisan test
```

Hasil saat ini: **39 tes lulus** pada `tests/Feature`:

- `AuthTest.php` — login, logout, validasi, log aktivitas
- `MasterDataTest.php` — outlet cascade, owner, jasa, role dan filter laporan
- `TransaksiTest.php` — perhitungan, UID kasir, isolasi outlet, status transaksi
- `PengajuanTokoTest.php` — pendaftaran publik dan persetujuan admin

## Perintah Artisan

| Perintah | Fungsi |
| --- | --- |
| `php artisan laundry:info` | Menampilkan informasi instalasi |

## Struktur Folder

Lihat `docs/STRUKTUR-FOLDER.md`.

## Dokumentasi

- `docs/STRUKTUR-FOLDER.md` — struktur direktori proyek
- `docs/INSTRUMENT-TESTING.md` — tabel instrument testing dan hasil uji
