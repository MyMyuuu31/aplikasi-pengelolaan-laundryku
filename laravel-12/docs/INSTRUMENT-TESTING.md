# Tabel Instrument Testing Aplikasi Pengelolaan Laundry

Aplikasi Pengolahan Laundry · UKK RPL Paket 3

Metode pengujian: **Black Box** (Black Box Testing) dengan pendekatan **Use Case Based Testing** dan **Login Based Testing**.

## A. Login Based Testing

| No | Username | Password | Hasil yang Diharapkan | Hasil yang Didapatkan | Status |
| --- | --- | --- | --- | --- | --- |
| 1 | admin | admin123 | Login berhasil, masuk dashboard | Sesuai harapan | Lulus |
| 2 | admin | admin123 (salah) | Login ditolak, pesan "kredensial salah" | Sesuai harapan | Lulus |
| 3 | tidakada | 123456 | Login ditolak | Sesuai harapan | Lulus |
| 4 | (kosong) | (kosong) | Validasi required, ditolak | Sesuai harapan | Lulus |
| 5 | UID `KSR-XXXXXXXX` | Password kasir | Login ke outlet owner yang sama | Sesuai harapan | Lulus |
| 6 | owner | owner123 | Login berhasil, mengelola toko miliknya | Sesuai harapan | Lulus |
| 7 | user nonaktif | 123456 | Login ditolak | Sesuai harapan | Lulus |
| 8 | admin | 123456 | Logout berhasil, kembali ke login | Sesuai harapan | Lulus |

## B. Use Case Based Testing — Modul Auth

| No | Use Case | Skenario | Hasil yang Dibiarkan | Status |
| --- | --- | --- | --- | --- |
| 1 | Halaman login dapat diakses | Buka `/login` | Halaman tampil normal | Lulus |
| 2 | Login dengan kredensial benar | Input data valid | Redirect ke dashboard | Lulus |
| 3 | Login dengan password salah | Password keliru | Ditolak | Lulus |
| 4 | Username tidak ada | Username salah | Ditolak | Lulus |
| 5 | Input kosong | Tidak mengisi form | Ditolak karena required | Lulus |
| 6 | Guest diarahkan ke login | Akses halaman tanpa login | Redirect ke `/login` | Lulus |
| 7 | Log aktivitas login berhasil | Login sukses | Tercatat di `tb_log` | Lulus |
| 8 | Log aktivitas login gagal | Login gagal | Tercatat di `tb_log` | Lulus |
| 9 | Logout | Klik logout | Session berakhir | Lulus |
| 10 | Username duplikat ditolak | Simpan user dengan username yang sudah ada | Ditolak | Lulus |

## C. Use Case Based Testing — Modul Master Data

| No | Use Case | Skenario | Hasil yang Diharapkan | Status |
| --- | --- | --- | --- | --- |
| 1 | Admin menambah outlet | Simpan outlet valid | Data tersimpan | Lulus |
| 2 | Outlet tanpa nama | Nama kosong | Ditolak | Lulus |
| 3 | Email outlet tidak valid | Format email salah | Ditolak | Lulus |
| 4 | Hapus toko bertransaksi | Hapus toko yang memiliki owner/kasir/transaksi | Akun dihapus, transaksi historis tetap ada | Lulus |
| 5 | Kode member otomatis | Tambah member | Kode `MBRxxxx` terbentuk | Lulus |
| 6 | Nomor HP tidak valid | HP tidak sesuai format | Ditolak | Lulus |
| 7 | Paket tanpa harga | Harga kosong/nol | Ditolak | Lulus |
| 8 | Arsip jasa terpakai | Owner menonaktifkan jasa yang punya histori | Jasa nonaktif, rincian transaksi tetap ada | Lulus |
| 9 | Batas role admin | Admin membuka halaman kasir/jasa | Ditolak | Lulus |
| 10 | Pengelolaan owner | Admin membuat/mengubah akun owner untuk satu toko | Tersimpan, tidak menampilkan akun kasir | Lulus |
| 11 | Log CRUD master | Tambah/edit/hapus data | Tercatat di `tb_log` | Lulus |
| 12 | Filter periode laporan | Laporkan dengan rentang tanggal | Hanya data periode tersebut | Lulus |

## D. Use Case Based Testing — Modul Transaksi

| No | Use Case | Skenario | Hasil yang Diharapkan | Status |
| --- | --- | --- | --- | --- |
| 1 | Buat transaksi | Jasa dari outlet sendiri × qty, diskon + pajak | Perhitungan server-side benar | Lulus |
| 2 | Nomor invoice otomatis | Buat 2 transaksi | `INV-YYYYMMDDxxxxx` unik | Lulus |
| 3 | Harga dari server | Manipulasi harga di form | Harga tetap diambil dari server | Lulus |
| 4 | Transaksi tanpa item | Simpan tanpa item | Ditolak | Lulus |
| 5 | Ubah status transaksi | Proses → Selesai/Diambil/Dibatalkan | Status berubah, log tercatat | Lulus |
| 6 | UID kasir | Owner membuat kasir, reset password, kasir login dengan UID | UID acak valid, password ter-hash | Lulus |
| 7 | Isolasi outlet | Owner membuka transaksi/kasir toko lain | Ditolak | Lulus |
| 8 | Akun nonaktif | Login lalu dinonaktifkan | Akses diblokir | Lulus |
| 9 | Anti SQL injection | Cari member dengan `' OR 1=1` | Aman, error dicegat | Lulus |

## E. Hasil Perhitungan (Verifikasi Manual)

| Komponen | Nilai |
| --- | --- |
| Subtotal | Rp56.000 |
| Diskon | Rp2.800 |
| Pajak (PPN 11%) | Rp5.852 |
| Total | Rp59.052 |
| Uang diterima | Rp100.000 |
| Kembalian | Rp40.948 |

Perhitungan dilakukan di server pada `app/Http/Controllers/TransaksiController.php`.

## F. Ringkasan Hasil

| Aspek | Jumlah |
| --- | --- |
| Total test otomatis | 39 |
| Test lulus | 39 |
| Test gagal | 0 |

## G. Pengajuan Toko

| Skenario | Hasil yang Diharapkan | Status |
| --- | --- | --- |
| Owner mengirim pendaftaran | Pengajuan pending, password berupa hash, belum bisa login | Lulus |
| Admin menyetujui pengajuan | Outlet dan akun owner dibuat atomik | Lulus |
| Admin menolak pengajuan | Status ditolak, akun tidak dibuat | Lulus |
| Owner membuka antrean admin | Akses ditolak | Lulus |

Perintah menjalankan pengujian:

```bash
php artisan test
```
