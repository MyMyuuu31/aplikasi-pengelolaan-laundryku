<?php
/**
 * Layout utama (template) - Aplikasi Pengelolaan Laundry
 */
require_once __DIR__ . '/../../config/koneksi.php';
$u = $_SESSION['user'];
$f = flash();

// Menu sidebar sesuai role (RBAC)
$menu = [
  ['dashboard', 'Dashboard', 'fa-house'],
  ['transaksi', 'Transaksi', 'fa-cart-shopping', ['admin', 'kasir']],
  ['member', 'Pelanggan', 'fa-users', ['admin', 'kasir']],
  ['paket', 'Paket Cuci', 'fa-box', ['admin', 'kasir']],
  ['outlet', 'Outlet', 'fa-shop', ['admin']],
  ['user', $u['role'] === 'admin' ? 'Pengguna' : 'Kasir Outlet', 'fa-user-gear', ['admin', 'owner']],
  ['laporan', 'Laporan', 'fa-chart-line', ['admin', 'owner', 'kasir']],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($judulHalaman) ?> | londryraa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>
  <div class="app d-flex">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-soap"></i>
        <div><strong>londryraa</strong><br><small>Laundry & perawatan</small></div>
      </div>
      <nav class="nav flex-column mt-3 px-2">
        <?php foreach ($menu as $m):
          if (isset($m[3]) && !in_array($u['role'], $m[3]))
            continue;
          $aktif = ($_GET['page'] ?? '') === $m[0]; ?>
          <a class="nav-link <?= $aktif ? 'active' : '' ?>" href="<?= url($m[0]) ?>">
            <i class="fa-solid <?= $m[2] ?> me-2"></i> <?= $m[1] ?>
          </a>
        <?php endforeach; ?>
        <hr class="text-white-50">
        <a class="nav-link text-danger" href="<?= url('logout', 'logout') ?>">
          <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
        </a>
      </nav>
    </aside>

    <!-- Main -->
    <main class="content flex-grow-1">
      <header class="topbar d-flex justify-content-between align-items-center px-4 py-2 bg-white shadow-sm">
        <h5 class="mb-0"><i class="fa-solid fa-circle-info text-primary me-2"></i><?= htmlspecialchars($judulHalaman) ?>
        </h5>
        <div class="d-flex align-items-center gap-3">
          <span class="badge bg-primary text-capitalize"><i
              class="fa-solid fa-user me-1"></i><?= htmlspecialchars($u['role']) ?></span>
          <div class="text-end">
            <div class="fw-semibold"><?= htmlspecialchars($u['nama']) ?></div>
            <small class="text-muted">@<?= htmlspecialchars($u['username']) ?></small>
          </div>
        </div>
      </header>
      <div class="p-4">
        <?php if ($f): ?>
          <div class="alert alert-<?= $f['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($f['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <?php require __DIR__ . '/../' . $file . '.php'; ?>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>

</html>