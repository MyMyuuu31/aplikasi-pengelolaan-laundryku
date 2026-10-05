<?php
/** CRUD Paket Cucian */
class PaketController
{
    private function log(string $a): void
    {
        q("INSERT INTO tb_log (id_user, aktivitas) VALUES (?,?)", [$_SESSION['user']['id'], $a]);
    }

    public function indexAction()
    {
        $o = $_SESSION['user'];
        $sql = "SELECT p.*, o.nama nama_outlet FROM tb_paket p
                JOIN tb_outlet o ON o.id=p.id_outlet";
        $p = [];
        if ($o['role'] !== 'admin') {
            $sql .= " WHERE p.id_outlet = ?";
            $p = [$o['id_outlet']];
        }
        $sql .= " ORDER BY o.nama, p.nama_paket";
        $data['rows'] = q($sql, $p, false);
        $data['outlets'] = q("SELECT id, nama FROM tb_outlet ORDER BY nama", [], false);
        view('paket/paket', ['judul' => 'Data Paket Cucian', 'data' => $data]);
    }

    public function saveAction()
    {
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        $user = $_SESSION['user'];
        $existing = $id ? q("SELECT id_outlet FROM tb_paket WHERE id=?", [$id]) : null;
        if ($user['role'] !== 'admin' && ($id && (!$existing || (int) $existing['id_outlet'] !== (int) $user['id_outlet']))) {
            flash('Paket ini bukan milik outlet Anda!', 'danger');
            redirect('paket');
        }
        $jenis = in_array($_POST['jenis'] ?? '', ['kiloan', 'selimut', 'bed_cover', 'kaos', 'lain']) ? $_POST['jenis'] : 'kiloan';
        $outlet = $user['role'] === 'admin' ? (int) ($_POST['id_outlet'] ?? 0) : (int) $user['id_outlet'];
        $p = [$outlet, $jenis, $_POST['nama_paket'] ?? '', (int) ($_POST['harga'] ?? 0)];
        if ($id) {
            q("UPDATE tb_paket SET id_outlet=?, jenis=?, nama_paket=?, harga=? WHERE id=?", [...$p, $id]);
            $this->log("Update paket #$id");
            flash('Paket berhasil diupdate!');
        } else {
            q("INSERT INTO tb_paket (id_outlet, jenis, nama_paket, harga) VALUES (?,?,?,?)", $p);
            $this->log('Tambah paket baru');
            flash('Paket berhasil ditambahkan!');
        }
        redirect('paket');
    }

    public function deleteAction()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = $_SESSION['user'];
        $paket = q("SELECT id_outlet FROM tb_paket WHERE id=?", [$id]);
        if (!$paket || ($user['role'] !== 'admin' && (int) $paket['id_outlet'] !== (int) $user['id_outlet'])) {
            flash('Paket ini bukan milik outlet Anda!', 'danger');
        } else {
            q("DELETE FROM tb_paket WHERE id=?", [$id]);
            $this->log("Hapus paket #$id");
            flash('Paket berhasil dihapus!');
        }
        redirect('paket');
    }
}
