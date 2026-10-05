<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span class="fw-semibold"><i class="fa-solid fa-shop text-primary me-2"></i>Data Outlet</span>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm"
            onclick="isiForm({}, 'outlet', 'save')">
      <i class="fa-solid fa-plus me-1"></i>Tambah Outlet
    </button>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th style="width:60px">No</th><th>Nama</th><th>Alamat</th><th>Telepon</th><th style="width:150px">Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($data['rows'] as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-semibold"><?= htmlspecialchars($r['nama']) ?></td>
          <td><?= htmlspecialchars($r['alamat']) ?></td>
          <td><?= htmlspecialchars($r['tlp']) ?></td>
          <td>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalForm"
                    onclick='isiForm(<?= json_encode($r) ?>, "outlet", "save")'>
              <i class="fa-solid fa-pen"></i></button>
            <a class="btn btn-danger btn-sm" href="<?= url('outlet', 'delete', ['id' => $r['id']]) ?>"
               onclick="return confirm('Yakin hapus outlet ini?')"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal form generik -->
<div class="modal fade" id="modalForm" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formModul" method="post" action="<?= url('outlet', 'save') ?>">
      <div class="modal-header"><h5 class="modal-title" id="judulModal">Form Outlet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="isiModal"><?= csrf_field() ?></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>
