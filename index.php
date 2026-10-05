<?php
/**
 * Front Controller / Router sederhana (?page=... &action=...)
 * Aplikasi Pengelolaan Laundry - UKK RPL
 */
session_start();
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/app/helper.php';

$page = $_GET['page'] ?? 'auth';
$action = $_GET['action'] ?? 'index';

// ---- Middleware proteksi: semua kecuali login butuh sesi aktif ----
if ($page !== 'auth') {
    if (empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/index.php?page=auth');
        exit;
    }
}

// ---- Middleware hak akses berdasarkan role (RBAC) ----
$akses = [
    'auth'       => ['*'],
    'dashboard'  => ['admin', 'kasir', 'owner'],
    'outlet'     => ['admin'],
    'user'       => ['admin', 'owner'],
    'member'     => ['admin', 'kasir'],
    'paket'      => ['admin', 'kasir'],
    'transaksi'  => ['admin', 'kasir'],
    'laporan'    => ['admin', 'owner', 'kasir'],
    'log'        => ['admin'],
    'logout'     => ['*'],
];
if (!in_array('*', $akses[$page] ?? [])) {
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $akses[$page] ?? [])) {
        flash('Anda tidak memiliki hak akses ke menu tersebut!', 'danger');
        redirect('dashboard');
    }
}

// ---- Pemetaan page -> controller ----
$map = [
    'auth'      => 'AuthController',
    'dashboard' => 'DashboardController',
    'outlet'    => 'OutletController',
    'user'      => 'UserController',
    'member'    => 'MemberController',
    'paket'     => 'PaketController',
    'transaksi' => 'TransaksiController',
    'laporan'   => 'LaporanController',
    'log'       => 'LogController',
    'logout'    => 'AuthController',
];

$controller = $map[$page] ?? null;
if (!$controller) die('Halaman tidak ditemukan.');

require_once __DIR__ . '/app/Controllers/' . $controller . '.php';

// logout = action "logout" pada AuthController
$method = $action . 'Action';
$obj = new $controller();
if (!method_exists($obj, $method)) die('Aksi tidak ditemukan: ' . $method);
$obj->$method();
