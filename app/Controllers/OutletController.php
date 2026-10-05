<?php
/** CRUD Outlet (admin) */
class OutletController
{
    private function log(string $a): void
    {
        q("INSERT INTO tb_log (id_user, aktivitas) VALUES (?,?)", [$_SESSION['user']['id'], $a]);
    }

    public function indexAction()
    {
        $data['rows'] = q("SELECT * FROM tb_outlet ORDER BY nama", [], false);
        view('outlet/outlet', ['judul' => 'Data Outlet', 'data' => $data]);
    }

    public function saveAction()
    {
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        $p = [$_POST['nama'] ?? '', $_POST['alamat'] ?? '', $_POST['tlp'] ?? ''];
        if ($id) {
            q("UPDATE tb_outlet SET nama=?, alamat=?, tlp=? WHERE id=?", [...$p, $id]);
            $this->log("Update outlet #$id");
            flash('Outlet berhasil diupdate!');
        } else {
            q("INSERT INTO tb_outlet (nama, alamat, tlp) VALUES (?,?,?)", $p);
            $this->log('Tambah outlet baru');
            flash('Outlet berhasil ditambahkan!');
        }
        redirect('outlet');
    }

    public function deleteAction()
    {
        $id = (int) ($_GET['id'] ?? 0);
        q("DELETE FROM tb_outlet WHERE id=?", [$id]);
        $this->log("Hapus outlet #$id");
        flash('Outlet berhasil dihapus!');
        redirect('outlet');
    }
}
