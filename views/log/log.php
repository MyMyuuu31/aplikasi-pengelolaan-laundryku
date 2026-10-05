<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white"><strong><i class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat Aktivitas</strong><span class="text-muted small ms-2">Maksimal 200 aktivitas terbaru</span></div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Username</th><th>Aktivitas</th></tr></thead><tbody>
    <?php if (!$data['rows']): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas tercatat.</td></tr><?php endif; ?>
    <?php foreach ($data['rows'] as $r): ?><tr><td><?= date('d M Y H:i:s', strtotime($r['waktu'])) ?></td><td><?= htmlspecialchars($r['nama_user'] ?? 'Pengguna dihapus') ?></td><td><?= htmlspecialchars($r['username'] ?? '-') ?></td><td><?= htmlspecialchars($r['aktivitas']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
</div>