<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-white"><strong><i class="fa-solid fa-chart-line me-2"></i>Filter Laporan</strong></div>
  <div class="card-body"><form method="get" action="<?= BASE_URL ?>/index.php" class="row g-3 align-items-end">
    <input type="hidden" name="page" value="laporan">
    <div class="col-sm-6 col-lg-2"><label class="form-label" for="lapDari">Dari</label><input class="form-control" id="lapDari" type="date" name="dari" value="<?= htmlspecialchars($data['filter']['dari']) ?>" required></div>
    <div class="col-sm-6 col-lg-2"><label class="form-label" for="lapSampai">Sampai</label><input class="form-control" id="lapSampai" type="date" name="sampai" value="<?= htmlspecialchars($data['filter']['sampai']) ?>" required></div>
    <?php if ($_SESSION['user']['role'] === 'admin'): ?><div class="col-sm-6 col-lg-3"><label class="form-label" for="lapOutlet">Outlet</label><select class="form-select" id="lapOutlet" name="id_outlet"><option value="">Semua outlet</option><?php foreach ($data['outlets'] as $o): ?><option value="<?= (int) $o['id'] ?>" <?= (int) $data['filter']['id_outlet'] === (int) $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nama']) ?></option><?php endforeach; ?></select></div><?php else: ?><div class="col-sm-6 col-lg-3"><label class="form-label" for="lapOutlet">Outlet</label><input type="hidden" name="id_outlet" value="<?= (int) $data['filter']['id_outlet'] ?>"><input class="form-control" id="lapOutlet" value="<?= htmlspecialchars($data['outlets'][0]['nama'] ?? 'Outlet belum ditentukan') ?>" disabled></div><?php endif; ?>
    <div class="col-sm-6 col-lg-3"><label class="form-label" for="lapStatus">Status</label><select class="form-select" id="lapStatus" name="status"><option value="">Semua status</option><?php foreach (['baru', 'proses', 'selesai', 'diambil'] as $status): ?><option value="<?= $status ?>" <?= $data['filter']['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-2"><button class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i>Terapkan</button></div>
  </form></div>
</div>
<div class="row g-3 mb-3"><div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total tagihan</div><strong class="fs-5"><?= rupiah($data['total_all']) ?></strong></div></div></div><div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Sudah dibayar</div><strong class="fs-5"><?= rupiah($data['total_bayar']) ?></strong></div></div></div></div>
<div class="d-flex justify-content-end mb-3">
  <form method="post" action="<?= url('laporan', 'generate') ?>" class="d-inline">
    <?= csrf_field() ?>
    <?php foreach ($data['filter'] as $name => $value): ?><input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars((string) $value) ?>"><?php endforeach; ?>
    <button class="btn btn-primary"><i class="fa-solid fa-file-circle-plus me-1"></i>Simpan PDF</button>
  </form>
</div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Tanggal</th><th>Invoice</th><th>Outlet</th><th>Pelanggan</th><th>Paket / jumlah</th><th>Kasir</th><th>Status</th><th>Pembayaran</th><th>Total</th></tr></thead><tbody>
  <?php if (!$data['rows']): ?><tr><td colspan="9" class="text-center text-muted py-4">Tidak ada transaksi pada rentang tanggal ini.</td></tr><?php endif; ?>
  <?php foreach ($data['rows'] as $r): ?><tr><td><?= date('d M Y', strtotime($r['tgl'])) ?></td><td><a href="<?= url('transaksi', 'detail', ['id' => $r['id']]) ?>"><?= htmlspecialchars($r['kode_invoice']) ?></a></td><td><?= htmlspecialchars($r['nama_outlet']) ?></td><td><?= htmlspecialchars($r['nama_member']) ?></td><td><?= htmlspecialchars($r['detail_paket']) ?></td><td><?= htmlspecialchars($r['nama_kasir']) ?></td><td class="text-capitalize"><?= htmlspecialchars($r['status']) ?></td><td><?= $r['dibayar'] === 'dibayar' ? 'Lunas' : 'Belum dibayar' ?></td><td><?= rupiah($r['total_tagihan']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="card border-0 shadow-sm mt-3">
  <div class="card-header bg-white"><strong><i class="fa-solid fa-folder-open me-2"></i>PDF Tersimpan</strong></div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Disimpan</th><th>Periode</th><th>Outlet</th><th>Pembuat</th><th>Aksi</th></tr></thead><tbody>
    <?php if (!$data['saved']): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada PDF tersimpan.</td></tr><?php endif; ?>
    <?php foreach ($data['saved'] as $report): ?><tr><td><?= date('d M Y H:i', strtotime($report['created_at'])) ?></td><td><?= htmlspecialchars($report['dari']) ?> – <?= htmlspecialchars($report['sampai']) ?></td><td><?= htmlspecialchars($report['nama_outlet'] ?? 'Semua outlet') ?></td><td><?= htmlspecialchars($report['nama_user']) ?></td><td class="text-nowrap"><a class="btn btn-outline-primary btn-sm" href="<?= url('laporan', 'downloadPdf', ['id' => $report['id']]) ?>"><i class="fa-solid fa-download me-1"></i>Unduh PDF</a> <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener" href="<?= url('laporan', 'printPdf', ['id' => $report['id']]) ?>"><i class="fa-solid fa-print me-1"></i>Cetak PDF</a></td></tr><?php endforeach; ?>
  </tbody></table></div>
</div>