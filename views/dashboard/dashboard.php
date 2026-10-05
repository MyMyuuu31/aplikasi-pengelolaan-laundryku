<?php require_once __DIR__ . '/../../config/koneksi.php';
$g = $data['grafik']; ?>
<div class="row g-3 mb-4">
  <?php
  $kartu = [
    ['Pendapatan', rupiah($data['pendapatan']), 'fa-money-bill-wave', 'success', 'transaksi'],
    ['Transaksi Baru', $data['transaksi_baru'], 'fa-inbox', 'primary', 'transaksi'],
    ['Pelanggan', $data['member_total'], 'fa-users', 'info', 'member'],
    ['Outlet', $data['outlet_total'], 'fa-shop', 'warning', 'outlet'],
  ];
  foreach ($kartu as $k): ?>
    <div class="col-md-6 col-xl-3">
      <div class="card stat-card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center">
          <div class="stat-icon bg-<?= $k[3] ?>"><i class="fa-solid <?= $k[2] ?>"></i></div>
          <div>
            <div class="text-muted small"><?= $k[0] ?></div>
            <div class="fs-5 fw-bold"><?= $k[1] ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-chart-column text-primary me-2"></i>Pendapatan
        7 Hari Terakhir</div>
      <div class="card-body">
        <canvas id="grafikPendapatan" height="180"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold"><i
          class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Transaksi Terbaru</div>
      <div class="list-group list-group-flush">
        <?php if (!$data['terbaru']): ?>
          <div class="list-group-item text-muted">Belum ada transaksi.</div>
        <?php endif; ?>
        <?php foreach ($data['terbaru'] as $t): ?>
          <a class="list-group-item list-group-item-action" href="<?= url('transaksi', 'detail', ['id' => $t['id']]) ?>">
            <div class="d-flex justify-content-between">
              <span class="fw-semibold"><?= htmlspecialchars($t['kode_invoice']) ?></span>
              <span class="badge bg-secondary text-capitalize"><?= $t['status'] ?></span>
            </div>
            <small class="text-muted"><?= htmlspecialchars($t['nama_member']) ?> &bull;
              <?= date('d M Y H:i', strtotime($t['tgl'])) ?></small>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  new Chart(document.getElementById('grafikPendapatan'), {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($g, 'tgl')) ?>,
      datasets: [{
        label: 'Pendapatan (Rp)',
        data: <?= json_encode(array_column($g, 'total')) ?>,
        fill: true, tension: .35,
        borderColor: '#d88da1',
        backgroundColor: 'rgba(216,141,161,.18)'
      }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
</script>