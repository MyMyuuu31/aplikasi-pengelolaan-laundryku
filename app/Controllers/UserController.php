<?php
/** CRUD Pengguna/User (admin) - password di-hash dengan password_hash */
class UserController
{
    private function log(string $a): void
    {
        q("INSERT INTO tb_log (id_user, aktivitas) VALUES (?,?)", [$_SESSION['user']['id'], $a]);
    }

    public function indexAction()
    {
        $user = $_SESSION['user'];
        $sql = "SELECT u.id, u.nama, u.username, u.role, u.id_outlet, o.nama nama_outlet
                FROM tb_user u LEFT JOIN tb_outlet o ON o.id=u.id_outlet";
        $params = [];
        if ($user['role'] === 'owner') {
            $sql .= " WHERE u.id_outlet=? AND u.role='kasir'";
            $params[] = $user['id_outlet'];
        }
        $data['rows'] = q($sql . " ORDER BY u.nama", $params, false);
        $data['outlets'] = $user['role'] === 'admin'
            ? q("SELECT id, nama FROM tb_outlet ORDER BY nama", [], false)
            : q("SELECT id, nama FROM tb_outlet WHERE id=?", [$user['id_outlet']], false);
        view('user/user', ['judul' => 'Data Pengguna', 'data' => $data]);
    }

    public function saveAction()
    {
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        $nama = $_POST['nama'] ?? '';
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        $current = $_SESSION['user'];
        $isAdmin = $current['role'] === 'admin';
        $previous = $isAdmin && $id ? q("SELECT role, id_outlet FROM tb_user WHERE id=?", [$id]) : null;
        $outlet = (int) ($_POST['id_outlet'] ?? 0) ?: null;
        $role = in_array($_POST['role'] ?? '', ['admin', 'kasir', 'owner'], true) ? $_POST['role'] : 'kasir';

        if (!$isAdmin) {
            $existing = $id ? q("SELECT id, role, id_outlet FROM tb_user WHERE id=?", [$id]) : null;
            if (($id && (!$existing || $existing['role'] !== 'kasir' || (int) $existing['id_outlet'] !== (int) $current['id_outlet']))
                || (int) $current['id_outlet'] < 1) {
                flash('Anda hanya dapat mengelola kasir di outlet sendiri!', 'danger');
                redirect('user');
            }
            $role = 'kasir';
            $outlet = (int) $current['id_outlet'];
        }

        if ($isAdmin && $role === 'owner') {
            if (!$outlet) {
                flash('Akun owner harus terhubung ke outlet!', 'danger');
                redirect('user');
            }
            $existingOwner = q(
                "SELECT id FROM tb_user WHERE role='owner' AND id_outlet=? AND id<>? LIMIT 1",
                [$outlet, $id],
            );
            if ($existingOwner) {
                flash('Setiap outlet hanya boleh memiliki satu akun owner!', 'danger');
                redirect('user');
            }
        }

        if ($isAdmin && $previous && $previous['role'] === 'owner'
            && ($role !== 'owner' || (int) $outlet !== (int) $previous['id_outlet'])) {
            $replacement = q(
                "SELECT id FROM tb_user WHERE role='owner' AND id_outlet=? AND id<>? LIMIT 1",
                [$previous['id_outlet'], $id],
            );
            if (!$replacement) {
                flash('Tambahkan owner pengganti sebelum memindahkan atau mengubah role owner ini!', 'danger');
                redirect('user');
            }
        }

        if ($id) {
            if ($pass !== '') {
                q(
                    "UPDATE tb_user SET nama=?, username=?, password=?, id_outlet=?, role=? WHERE id=?",
                    [$nama, $user, password_hash($pass, PASSWORD_DEFAULT), $outlet, $role, $id]
                );
            } else {
                q(
                    "UPDATE tb_user SET nama=?, username=?, id_outlet=?, role=? WHERE id=?",
                    [$nama, $user, $outlet, $role, $id]
                );
            }
            $this->log("Update pengguna #$id");
            flash('Pengguna berhasil diupdate!');
        } else {
            if ($pass === '') {
                flash('Password wajib diisi untuk pengguna baru!', 'danger');
                redirect('user');
            }
            q(
                "INSERT INTO tb_user (nama, username, password, id_outlet, role) VALUES (?,?,?,?,?)",
                [$nama, $user, password_hash($pass, PASSWORD_DEFAULT), $outlet, $role]
            );
            $this->log('Tambah pengguna baru');
            flash('Pengguna berhasil ditambahkan!');
        }
        redirect('user');
    }

    public function deleteAction()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $current = $_SESSION['user'];
        $target = q("SELECT id, role, id_outlet FROM tb_user WHERE id=?", [$id]);
        if ($id === (int) $current['id']) {
            flash('Tidak bisa menghapus akun sendiri!', 'danger');
        } elseif ($current['role'] === 'admin' && $target && $target['role'] === 'owner'
            && !q("SELECT id FROM tb_user WHERE role='owner' AND id_outlet=? AND id<>? LIMIT 1", [$target['id_outlet'], $id])) {
            flash('Tambahkan owner pengganti sebelum menghapus owner terakhir di outlet ini!', 'danger');
        } elseif (!$target || ($current['role'] === 'owner' && ($target['role'] !== 'kasir' || (int) $target['id_outlet'] !== (int) $current['id_outlet']))) {
            flash('Anda hanya dapat menghapus kasir di outlet sendiri!', 'danger');
        } else {
            q("DELETE FROM tb_user WHERE id=?", [$id]);
            $this->log("Hapus pengguna #$id");
            flash('Pengguna berhasil dihapus!');
        }
        redirect('user');
    }
}
