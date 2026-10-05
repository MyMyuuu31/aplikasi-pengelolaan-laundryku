<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
    <strong><i class="fa-solid fa-users me-2"></i>Data Pelanggan</strong>
    <div class="d-flex flex-wrap gap-2">
      <form method="get" action="<?= BASE_URL ?>/index.php" class="d-flex gap-2">
        <input type="hidden" name="page" value="member">
        <input class="form-control form-control-sm" name="cari" value="<?= htmlspecialchars($_GET['cari'] ?? '') ?>" placeholder="Cari nama/telepon">
        <button class="btn btn-outline-primary btn-sm" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMember" onclick="isiMemberForm()">
        <i class="fa-solid fa-plus me-1"></i>Tambah Pelanggan
      </button>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>No</th><th>Nama</th><th>Jenis Kelamin</th><th>Alamat</th><th>Telepon</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php if (!$data['rows']): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pelanggan.</td></tr>
        <?php endif; ?>
        <?php foreach ($data['rows'] as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama']) ?></td>
            <td><?= $r['jenis_kelamin'] === 'P' ? 'Perempuan' : 'Laki-laki' ?></td>
            <td><?= htmlspecialchars($r['alamat'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['tlp'] ?? '') ?></td>
            <td class="text-nowrap">
              <button class="btn btn-outline-primary btn-sm" aria-label="Ubah pelanggan" data-bs-toggle="modal" data-bs-target="#modalMember" onclick='isiMemberForm(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>)'><i class="fa-solid fa-pen"></i></button>
              <a class="btn btn-outline-danger btn-sm" href="<?= url('member', 'delete', ['id' => $r['id']]) ?>" onclick="return confirm('Hapus pelanggan ini?')" aria-label="Hapus pelanggan"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalMember" tabindex="-1" aria-labelledby="judulMember" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= url('member', 'save') ?>">
      <div class="modal-header"><h5 class="modal-title" id="judulMember">Tambah Pelanggan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <div class="mb-3"><label class="form-label" for="memberNama">Nama</label><input class="form-control" id="memberNama" name="nama" required></div>
        <div class="mb-3"><label class="form-label" for="memberJk">Jenis kelamin</label><select class="form-select" id="memberJk" name="jenis_kelamin"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
        <div class="mb-3"><label class="form-label" for="memberAlamat">Alamat</label><textarea class="form-control" id="memberAlamat" name="alamat" rows="2"></textarea></div>
        <div><label class="form-label" for="memberTlp">Telepon</label><input class="form-control" id="memberTlp" name="tlp" inputmode="tel"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan</button></div>
    </form>
  </div>
</div>

<script>
  function isiMemberForm(row = {}) {
    const form = document.querySelector('#modalMember form');
    form.reset();
    form.elements.id.value = row.id ?? '';
    form.elements.nama.value = row.nama ?? '';
    form.elements.jenis_kelamin.value = row.jenis_kelamin ?? 'L';
    form.elements.alamat.value = row.alamat ?? '';
    form.elements.tlp.value = row.tlp ?? '';
    document.getElementById('judulMember').textContent = row.id ? 'Ubah Pelanggan' : 'Tambah Pelanggan';
  }
</script>