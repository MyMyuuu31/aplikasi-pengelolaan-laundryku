<?php require_once __DIR__ . '/../../config/koneksi.php'; $isAdmin = $_SESSION['user']['role'] === 'admin'; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <strong><i class="fa-solid fa-user-gear me-2"></i><?= $isAdmin ? 'Pengguna' : 'Kasir Outlet' ?></strong>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUser" onclick="isiUserForm()"><i class="fa-solid fa-plus me-1"></i>Tambah Pengguna</button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th><th>Outlet</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php if (!$data['rows']): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengguna.</td></tr><?php endif; ?>
        <?php foreach ($data['rows'] as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td><td class="fw-semibold"><?= htmlspecialchars($r['nama']) ?></td>
            <td><?= htmlspecialchars($r['username']) ?></td><td><span class="badge text-bg-light text-capitalize"><?= htmlspecialchars($r['role']) ?></span></td>
            <td><?= htmlspecialchars($r['nama_outlet'] ?? 'Belum dipilih') ?></td>
            <td class="text-nowrap">
              <button class="btn btn-outline-primary btn-sm" aria-label="Ubah pengguna" data-bs-toggle="modal" data-bs-target="#modalUser" onclick='isiUserForm(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>)'><i class="fa-solid fa-pen"></i></button>
              <?php if ((int) $r['id'] !== (int) $_SESSION['user']['id']): ?><a class="btn btn-outline-danger btn-sm" href="<?= url('user', 'delete', ['id' => $r['id']]) ?>" onclick="return confirm('Hapus pengguna ini?')" aria-label="Hapus pengguna"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="judulUser" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= url('user', 'save') ?>">
      <div class="modal-header"><h5 class="modal-title" id="judulUser">Tambah Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
      <div class="modal-body">
        <?= csrf_field() ?><input type="hidden" name="id">
        <div class="mb-3"><label class="form-label" for="userNama">Nama</label><input class="form-control" id="userNama" name="nama" required></div>
        <div class="mb-3"><label class="form-label" for="userUsername">Username</label><input class="form-control" id="userUsername" name="username" required></div>
        <div class="mb-3"><label class="form-label" for="userPassword">Password <span id="passwordHint" class="text-muted small"></span></label><input class="form-control" id="userPassword" name="password" type="password" autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label" for="userRole">Role</label><select class="form-select" id="userRole" name="role" <?= $isAdmin ? '' : 'disabled' ?>><?php if ($isAdmin): ?><option value="admin">Admin</option><?php endif; ?><option value="kasir">Kasir</option><?php if ($isAdmin): ?><option value="owner">Owner</option><?php endif; ?></select><?php if (!$isAdmin): ?><input type="hidden" name="role" value="kasir"><?php endif; ?></div>
        <div><label class="form-label" for="userOutlet">Outlet</label><?php if ($isAdmin): ?><select class="form-select" id="userOutlet" name="id_outlet"><option value="">Belum dipilih</option><?php foreach ($data['outlets'] as $o): ?><option value="<?= (int) $o['id'] ?>"><?= htmlspecialchars($o['nama']) ?></option><?php endforeach; ?></select><?php else: ?><input type="hidden" id="userOutlet" name="id_outlet" value="<?= (int) $_SESSION['user']['id_outlet'] ?>"><input class="form-control" value="<?= htmlspecialchars($data['outlets'][0]['nama'] ?? 'Outlet belum ditentukan') ?>" disabled><?php endif; ?></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan</button></div>
    </form>
  </div>
</div>

<script>
  function isiUserForm(row = {}) {
    const form = document.querySelector('#modalUser form');
    form.reset();
    const defaultOutlet = form.elements.id_outlet.value;
    form.elements.id.value = row.id ?? '';
    form.elements.nama.value = row.nama ?? '';
    form.elements.username.value = row.username ?? '';
    form.elements.password.value = '';
    form.elements.password.required = !row.id;
    form.elements.role.value = row.role ?? 'kasir';
    form.elements.id_outlet.value = row.id_outlet ?? defaultOutlet;
    document.getElementById('passwordHint').textContent = row.id ? '(kosongkan jika tidak diubah)' : '(wajib diisi)';
    document.getElementById('judulUser').textContent = row.id ? 'Ubah Pengguna' : 'Tambah Pengguna';
  }
</script>