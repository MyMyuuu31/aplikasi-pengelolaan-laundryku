<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<form class="card border-0 shadow-sm" method="post" action="<?= url('transaksi', 'store') ?>">
  <div class="card-header bg-white d-flex justify-content-between align-items-center"><strong>Transaksi Baru</strong><a class="btn btn-outline-secondary btn-sm" href="<?= url('transaksi') ?>">Kembali</a></div>
  <div class="card-body">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label" for="trxMember">Pelanggan</label><select class="form-select" id="trxMember" name="id_member" required><option value="">Pilih pelanggan</option><?php foreach ($data['members'] as $m): ?><option value="<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?></option><?php endforeach; ?></select></div>
      <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?><div class="col-md-6"><label class="form-label" for="trxOutlet">Outlet</label><select class="form-select" id="trxOutlet" name="id_outlet" required><?php foreach ($data['outlets'] as $o): ?><option value="<?= (int) $o['id'] ?>"><?= htmlspecialchars($o['nama']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
      <div class="col-md-6"><label class="form-label" for="trxBatas">Batas waktu (YYYY-MM-DD HH:MM)</label><input class="form-control" id="trxBatas" name="batas_waktu" value="<?= date('Y-m-d H:i', strtotime('+2 days')) ?>" required></div>
      <div class="col-md-2"><label class="form-label" for="trxTambahan">Biaya tambahan</label><input class="form-control" id="trxTambahan" name="biaya_tambahan" type="number" min="0" value="0"></div>
      <div class="col-md-2"><label class="form-label" for="trxDiskon">Diskon (%)</label><input class="form-control" id="trxDiskon" name="diskon" type="number" min="0" max="100" step="0.01" value="0"></div>
      <div class="col-md-2"><label class="form-label" for="trxPajak">Pajak</label><input class="form-control" id="trxPajak" name="pajak" type="number" min="0" value="0"></div>
    </div>
    <h6 class="mt-4 mb-2">Pilih paket dan jumlah</h6>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead><tr><th>Paket</th><th>Outlet</th><th>Harga</th><th style="width:130px">Jumlah</th><th>Keterangan</th></tr></thead>
        <tbody>
          <?php if (!$data['pakets']): ?><tr><td colspan="5" class="text-muted">Belum ada paket di outlet ini.</td></tr><?php endif; ?>
          <?php foreach ($data['pakets'] as $p): ?>
            <tr><td><?= htmlspecialchars($p['nama_paket']) ?><small class="d-block text-muted"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $p['jenis']))) ?></small></td><td><?= htmlspecialchars($p['nama_outlet']) ?></td><td><?= rupiah($p['harga']) ?></td><td><input class="form-control form-control-sm" name="paket[<?= (int) $p['id'] ?>]" type="number" min="0" step="0.1" value="0" aria-label="Jumlah <?= htmlspecialchars($p['nama_paket']) ?>"></td><td><input class="form-control form-control-sm" name="keterangan[<?= (int) $p['id'] ?>]" aria-label="Keterangan <?= htmlspecialchars($p['nama_paket']) ?>"></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer bg-white text-end"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Transaksi</button></div>
</form>