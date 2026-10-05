<?php
/** CRUD Member / Pelanggan */
class MemberController
{
    private function log(string $a): void
    {
        q("INSERT INTO tb_log (id_user, aktivitas) VALUES (?,?)", [$_SESSION['user']['id'], $a]);
    }

    public function indexAction()
    {
        $cari = trim($_GET['cari'] ?? '');
        $sql = "SELECT * FROM tb_member";
        $p = [];
        if ($cari !== '') {
            $sql .= " WHERE nama LIKE ? OR tlp LIKE ?";
            $p = ["%$cari%", "%$cari%"];
        }
        $sql .= " ORDER BY nama";
        $data['rows'] = q($sql, $p, false);
        view('member/member', ['judul' => 'Data Pelanggan', 'data' => $data]);
    }

    public function saveAction()
    {
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        $jk = ($_POST['jenis_kelamin'] ?? 'L') === 'P' ? 'P' : 'L';
        $p = [$_POST['nama'] ?? '', $_POST['alamat'] ?? '', $jk, $_POST['tlp'] ?? ''];
        if ($id) {
            q("UPDATE tb_member SET nama=?, alamat=?, jenis_kelamin=?, tlp=? WHERE id=?", [...$p, $id]);
            $this->log("Update member #$id");
            flash('Pelanggan berhasil diupdate!');
        } else {
            q("INSERT INTO tb_member (nama, alamat, jenis_kelamin, tlp) VALUES (?,?,?,?)", $p);
            $this->log('Tambah member baru');
            flash('Pelanggan berhasil ditambahkan!');
        }
        redirect('member');
    }

    public function deleteAction()
    {
        $id = (int) ($_GET['id'] ?? 0);
        q("DELETE FROM tb_member WHERE id=?", [$id]);
        $this->log("Hapus member #$id");
        flash('Pelanggan berhasil dihapus!');
        redirect('member');
    }
}
