<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
    <strong><i class="fa-solid fa-cart-shopping me-2"></i>Daftar Transaksi</strong>
    <div class="d-flex gap-2">
      <form method="get" action="<?= BASE_URL ?>/index.php" class="d-flex gap-2">
        <input type="hidden" name="page" value="transaksi">
        <select class="form-select form-select-sm" name="status" aria-label="Filter status"><option value="">Semua status</option><?php foreach (['baru', 'proses', 'selesai', 'diambil'] as $status): ?><option value="<?= $status ?>" <?= ($_GET['status'] ?? '') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select>
        <button class="btn btn-outline-primary btn-sm">Filter</button>
      </form>
      <a class="btn btn-primary btn-sm" href="<?= url('transaksi', 'create') ?>"><i class="fa-solid fa-plus me-1"></i>Transaksi Baru</a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>No</th><th>Invoice</th><th>Tanggal</th><th>Pelanggan</th><th>Outlet</th><th>Status</th><th>Pembayaran</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php if (!$data['rows']): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada transaksi untuk filter ini.</td></tr><?php endif; ?>
        <?php foreach ($data['rows'] as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td><td class="fw-semibold"><?= htmlspecialchars($r['kode_invoice']) ?></td>
            <td><?= date('d M Y H:i', strtotime($r['tgl'])) ?></td><td><?= htmlspecialchars($r['nama_member']) ?></td>
            <td><?= htmlspecialchars($r['nama_outlet']) ?></td><td class="text-capitalize"><?= htmlspecialchars($r['status']) ?></td>
            <td><?= $r['dibayar'] === 'dibayar' ? 'Lunas' : 'Belum dibayar' ?></td>
            <td><a class="btn btn-outline-primary btn-sm" href="<?= url('transaksi', 'detail', ['id' => $r['id']]) ?>">Detail</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>