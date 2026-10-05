<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div><h4 class="h5 mb-1">Detail Transaksi</h4><div class="text-muted"><?= htmlspecialchars($t['kode_invoice']) ?></div></div>
  <div class="d-flex gap-2"><a class="btn btn-outline-secondary btn-sm" href="<?= url('transaksi') ?>">Kembali</a><button class="btn btn-outline-primary btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Cetak</button></div>
</div>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6"><div class="text-muted small">Pelanggan</div><strong><?= htmlspecialchars($t['nama_member']) ?></strong><div><?= htmlspecialchars($t['alamat_member'] ?? '') ?></div><div><?= htmlspecialchars($t['tlp_member'] ?? '') ?></div></div>
      <div class="col-md-6"><div class="text-muted small">Outlet</div><strong><?= htmlspecialchars($t['nama_outlet']) ?></strong><div><?= htmlspecialchars($t['alamat_outlet'] ?? '') ?></div><div class="mt-2">Kasir: <?= htmlspecialchars($t['nama_kasir']) ?></div></div>
      <div class="col-md-6"><div class="text-muted small">Tanggal masuk</div><?= date('d M Y H:i', strtotime($t['tgl'])) ?></div>
      <div class="col-md-6"><div class="text-muted small">Batas waktu</div><?= $t['batas_waktu'] ? date('d M Y H:i', strtotime($t['batas_waktu'])) : '-' ?></div>
    </div>
  </div>
</div>
<div class="card border-0 shadow-sm mb-3">
  <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Paket</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th><th>Keterangan</th></tr></thead><tbody>
    <?php foreach ($detail as $d): ?><tr><td><?= htmlspecialchars($d['nama_paket']) ?><small class="d-block text-muted"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $d['jenis']))) ?></small></td><td><?= number_format((float) $d['qty'], 1, ',', '.') ?></td><td><?= rupiah($d['harga']) ?></td><td><?= rupiah($d['subtotal']) ?></td><td><?= htmlspecialchars($d['keterangan'] ?? '') ?></td></tr><?php endforeach; ?>
    <?php if (!$detail): ?><tr><td colspan="5" class="text-center text-muted">Belum ada detail paket.</td></tr><?php endif; ?>
  </tbody><tfoot><tr><th colspan="3" class="text-end">Total tagihan</th><th><?= rupiah($total) ?></th><th></th></tr></tfoot></table></div>
</div>
<form class="card border-0 shadow-sm" method="post" action="<?= url('transaksi', 'updateStatus') ?>">
  <div class="card-body"><h6>Perbarui status</h6><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
    <div class="row g-3 align-items-end"><div class="col-md-4"><label class="form-label" for="trxStatus">Status</label><select class="form-select" id="trxStatus" name="status"><?php foreach (['baru', 'proses', 'selesai', 'diambil'] as $status): ?><option value="<?= $status ?>" <?= $t['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label" for="trxDibayar">Pembayaran</label><select class="form-select" id="trxDibayar" name="dibayar"><option value="belum_dibayar" <?= $t['dibayar'] === 'belum_dibayar' ? 'selected' : '' ?>>Belum dibayar</option><option value="dibayar" <?= $t['dibayar'] === 'dibayar' ? 'selected' : '' ?>>Dibayar</option></select></div>
    <div class="col-md-4"><button class="btn btn-primary">Simpan Status</button></div></div>
  </div>
</form>
<style>@media print { .sidebar, .topbar, .btn, form { display: none !important; } .content > .p-4 { padding: 0 !important; } }</style>