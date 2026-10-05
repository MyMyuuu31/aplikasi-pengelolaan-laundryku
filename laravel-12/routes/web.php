<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\OwnerOutletController;
use App\Http\Controllers\PengajuanTokoController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/daftar-toko', [PengajuanTokoController::class, 'create'])->name('daftar-toko.create');
    Route::post('/daftar-toko', [PengajuanTokoController::class, 'store'])->middleware('throttle:5,1')->name('daftar-toko.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'auth.active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:owner,kasir')->group(function () {
        Route::resource('transaksi', TransaksiController::class)->except(['destroy']);
        Route::get('/transaksi/{transaksi}/kembalian', [TransaksiController::class, 'kembalian'])
            ->name('transaksi.kembalian');
        Route::patch('/transaksi/{transaksi}/status', [TransaksiController::class, 'updateStatus'])
            ->name('transaksi.status');
    });

    // Admin mengelola toko dan akun owner, bukan kasir atau daftar jasa.
    Route::middleware('role:admin')->group(function () {
        Route::resource('outlet', OutletController::class);
        Route::resource('user', UserController::class);
        Route::get('/pengajuan-toko', [PengajuanTokoController::class, 'index'])->name('pengajuan-toko.index');
        Route::post('/pengajuan-toko/{pengajuan}/setujui', [PengajuanTokoController::class, 'approve'])->name('pengajuan-toko.approve');
        Route::post('/pengajuan-toko/{pengajuan}/tolak', [PengajuanTokoController::class, 'reject'])->name('pengajuan-toko.reject');
    });

    Route::middleware('role:owner')->group(function () {
        Route::get('/toko/edit', [OwnerOutletController::class, 'edit'])->name('toko.edit');
        Route::put('/toko', [OwnerOutletController::class, 'update'])->name('toko.update');
        Route::resource('paket', PaketController::class);
        Route::resource('kasir', KasirController::class)->except(['show']);
    });

    Route::resource('member', MemberController::class)->middleware('role:owner,kasir');

    // Master bisa diakses semua role untuk lookup (read), mutasi hanya admin
    Route::get('/api/paket-aktif', [PaketController::class, 'apiPaketAktif'])->middleware('role:owner,kasir')->name('api.paket-aktif');
    Route::get('/api/member-cari', [MemberController::class, 'apiCari'])->middleware('role:owner,kasir')->name('api.member-cari');

    // Laporan - Kasir & Owner (Owner hanya laporan)
    Route::middleware('role:kasir,owner')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/transaksi', [LaporanController::class, 'transaksi'])->name('laporan.transaksi');
        Route::get('/laporan/transaksi/cetak', [LaporanController::class, 'cetakTransaksi'])->name('laporan.transaksi.cetak');
        Route::get('/laporan/transaksi/export', [LaporanController::class, 'exportTransaksi'])->name('laporan.transaksi.export');
        Route::get('/laporan/pendapatan', [LaporanController::class, 'pendapatan'])->name('laporan.pendapatan');
        Route::get('/laporan/pendapatan/cetak', [LaporanController::class, 'cetakPendapatan'])->name('laporan.pendapatan.cetak');
        Route::get('/laporan/pendapatan/export', [LaporanController::class, 'exportPendapatan'])->name('laporan.pendapatan.export');
        Route::get('/laporan/paket', [LaporanController::class, 'paket'])->name('laporan.paket');
        Route::get('/laporan/paket/export', [LaporanController::class, 'exportPaket'])->name('laporan.paket.export');
        Route::get('/laporan/member', [LaporanController::class, 'member'])->name('laporan.member');
        Route::get('/laporan/member/export', [LaporanController::class, 'exportMember'])->name('laporan.member.export');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/log', [LogAktivitasController::class, 'index'])->name('log.index');
        Route::get('/log/export', [LogAktivitasController::class, 'export'])->name('log.export');
    });
});