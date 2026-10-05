<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <strong><i class="fa-solid fa-box me-2"></i>Daftar Paket Cuci</strong>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPaket" onclick="isiPaketForm()"><i class="fa-solid fa-plus me-1"></i>Tambah Paket</button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>No</th><th>Outlet</th><th>Nama Paket</th><th>Jenis</th><th>Harga</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php if (!$data['rows']): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada paket cuci.</td></tr><?php endif; ?>
        <?php foreach ($data['rows'] as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td><td><?= htmlspecialchars($r['nama_outlet']) ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama_paket']) ?></td>
            <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $r['jenis']))) ?></td>
            <td><?= rupiah($r['harga']) ?></td>
            <td class="text-nowrap">
              <button class="btn btn-outline-primary btn-sm" aria-label="Ubah paket" data-bs-toggle="modal" data-bs-target="#modalPaket" onclick='isiPaketForm(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>)'><i class="fa-solid fa-pen"></i></button>
              <a class="btn btn-outline-danger btn-sm" href="<?= url('paket', 'delete', ['id' => $r['id']]) ?>" onclick="return confirm('Hapus paket ini?')" aria-label="Hapus paket"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalPaket" tabindex="-1" aria-labelledby="judulPaket" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= url('paket', 'save') ?>">
      <div class="modal-header"><h5 class="modal-title" id="judulPaket">Tambah Paket</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
      <div class="modal-body">
        <?= csrf_field() ?><input type="hidden" name="id">
        <div class="mb-3"><label class="form-label" for="paketOutlet">Outlet</label><select class="form-select" id="paketOutlet" name="id_outlet" required><?php foreach ($data['outlets'] as $o): ?><option value="<?= (int) $o['id'] ?>"><?= htmlspecialchars($o['nama']) ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="paketNama">Nama paket</label><input class="form-control" id="paketNama" name="nama_paket" required></div>
        <div class="mb-3"><label class="form-label" for="paketJenis">Jenis</label><select class="form-select" id="paketJenis" name="jenis"><option value="kiloan">Kiloan</option><option value="selimut">Selimut</option><option value="bed_cover">Bed cover</option><option value="kaos">Kaos</option><option value="lain">Lainnya</option></select></div>
        <div><label class="form-label" for="paketHarga">Harga per satuan (Rp)</label><input class="form-control" id="paketHarga" name="harga" type="number" min="0" required></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan</button></div>
    </form>
  </div>
</div>

<script>
  function isiPaketForm(row = {}) {
    const form = document.querySelector('#modalPaket form');
    form.reset();
    form.elements.id.value = row.id ?? '';
    form.elements.id_outlet.value = row.id_outlet ?? form.elements.id_outlet.options[0]?.value ?? '';
    form.elements.nama_paket.value = row.nama_paket ?? '';
    form.elements.jenis.value = row.jenis ?? 'kiloan';
    form.elements.harga.value = row.harga ?? '';
    document.getElementById('judulPaket').textContent = row.id ? 'Ubah Paket' : 'Tambah Paket';
  }
</script>