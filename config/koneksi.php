<?php
/**
 * Konfigurasi & koneksi database (PDO)
 * Aplikasi Pengelolaan Laundry - UKK RPL
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_laundry_ukk');
define('DB_USER', 'root');
define('DB_PASS', '');          // sesuaikan dengan MySQL di komputer Anda
define('BASE_URL', '/Aplikasi Pengolahan Laundry'); // sesuaikan nama folder project

date_default_timezone_set('Asia/Jakarta');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

/** Ambil satu baris / scalar dari query berparameter (aman SQL Injection) */
function q(string $sql, array $params = [], bool $single = true)
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $single ? $st->fetch() : $st->fetchAll();
}

/** Redirect */
function redirect(string $page): void
{
    header('Location: ' . BASE_URL . '/index.php?page=' . $page);
    exit;
}

/** URL helper */
function url(string $page, string $action = '', array $params = []): string
{
    $u = BASE_URL . '/index.php?page=' . $page . ($action ? '&action=' . $action : '');
    foreach ($params as $k => $v)
        $u .= '&' . $k . '=' . urlencode($v);
    return $u;
}

/** CSRF token sederhana */
function csrf_token(): string
{
    if (empty($_SESSION['csrf']))
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_check(): void
{
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        die('Token keamanan tidak valid. Silakan kembali dan coba lagi.');
    }
}

/** Flash message */
function flash(string $msg = null, string $type = 'success')
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/** Format Rupiah */
function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}
