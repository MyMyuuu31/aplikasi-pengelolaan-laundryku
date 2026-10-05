<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Transaksi | londryraa</title>
  <style>
    @page { size: A4 landscape; margin: 14mm; }
    body { color: #332a27; font: 12px Arial, sans-serif; }
    h1 { margin: 0 0 4px; color: #63463e; font-size: 22px; }
    .meta { margin-bottom: 18px; color: #655b57; }
    .summary { display: flex; gap: 28px; margin: 12px 0; }
    .summary strong { display: block; margin-top: 3px; font-size: 15px; }
    table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    th, td { padding: 7px 8px; border: 1px solid #d9cbc4; text-align: left; }
    th { color: #50372f; background: #f5e8e3; }
    tfoot td { font-weight: bold; }
  </style>
</head>
<body>
  <h1>londryraa</h1>
  <div>Laporan transaksi pencucian</div>
  <div class="meta">Periode <?= htmlspecialchars($data['filter']['dari']) ?> sampai <?= htmlspecialchars($data['filter']['sampai']) ?> · Outlet: <?php if ($data['filter']['id_outlet']): ?><?= htmlspecialchars($data['outlets'][0]['nama'] ?? 'Outlet') ?><?php else: ?>Semua outlet<?php endif; ?> · Dicetak <?= date('d-m-Y H:i') ?></div>
  <div class="summary"><div>Total tagihan<strong><?= rupiah($data['total_all']) ?></strong></div><div>Sudah dibayar<strong><?= rupiah($data['total_bayar']) ?></strong></div><div>Jumlah transaksi<strong><?= count($data['rows']) ?></strong></div></div>
  <table>
    <thead><tr><th>Tanggal</th><th>Invoice</th><th>Outlet</th><th>Pelanggan</th><th>Paket / jumlah</th><th>Kasir</th><th>Status cuci</th><th>Pembayaran</th><th>Total</th></tr></thead>
    <tbody>
      <?php if (!$data['rows']): ?><tr><td colspan="9">Tidak ada transaksi pada rentang tanggal ini.</td></tr><?php endif; ?>
      <?php foreach ($data['rows'] as $r): ?><tr><td><?= date('d-m-Y H:i', strtotime($r['tgl'])) ?></td><td><?= htmlspecialchars($r['kode_invoice']) ?></td><td><?= htmlspecialchars($r['nama_outlet']) ?></td><td><?= htmlspecialchars($r['nama_member']) ?></td><td><?= htmlspecialchars($r['detail_paket']) ?></td><td><?= htmlspecialchars($r['nama_kasir']) ?></td><td><?= htmlspecialchars(ucfirst($r['status'])) ?></td><td><?= $r['dibayar'] === 'dibayar' ? 'Lunas' : 'Belum dibayar' ?></td><td><?= rupiah($r['total_tagihan']) ?></td></tr><?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="8">Total tagihan</td><td><?= rupiah($data['total_all']) ?></td></tr></tfoot>
  </table>
</body>
</html>