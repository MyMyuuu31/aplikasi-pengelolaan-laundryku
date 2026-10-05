<?php
/**
 * TransaksiController - transaksi laundry & pembayaran
 * Menggunakan STORED PROCEDURE sp_hitung_total & TRIGGER otomatis di tb_log
 */
class TransaksiController
{
    private function log(string $a): void
    {
        q("INSERT INTO tb_log (id_user, aktivitas) VALUES (?,?)", [$_SESSION['user']['id'], $a]);
    }

    /** Kode invoice: LND-YYYYMMDD-XXX (fallback bila function SQL tak tersedia) */
    private function buatKodeInvoice(): string
    {
        try {
            return q("SELECT fn_kode_invoice() k")['k'];
        } catch (Throwable $e) {
            $p = 'LND-' . date('Ymd') . '-';
            $c = (int) q("SELECT COUNT(*) c FROM tb_transaksi WHERE kode_invoice LIKE ?", [$p . '%'])['c'];
            return $p . str_pad((string) ($c + 1), 3, '0', STR_PAD_LEFT);
        }
    }

    public function indexAction()
    {
        $u = $_SESSION['user'];
        $sql = "SELECT t.*, m.nama nama_member, o.nama nama_outlet, us.nama nama_kasir
                FROM tb_transaksi t
                JOIN tb_member m ON m.id=t.id_member
                JOIN tb_outlet o ON o.id=t.id_outlet
                JOIN tb_user us ON us.id=t.id_user";
        $p = [];
        if ($u['role'] !== 'admin') {
            $sql .= " WHERE t.id_outlet = ?";
            $p[] = $u['id_outlet'];
        }
        if (($_GET['status'] ?? '') !== '') {
            $sql .= ($p ? " AND" : " WHERE") . " t.status = ?";
            $p[] = $_GET['status'];
        }
        $sql .= " ORDER BY t.tgl DESC";
        $data['rows'] = q($sql, $p, false);
        view('transaksi/transaksi', ['judul' => 'Data Transaksi', 'data' => $data]);
    }

    /** Form transaksi baru (pilih member + multiple paket) */
    public function createAction()
    {
        $u = $_SESSION['user'];
        $sql = "SELECT * FROM tb_member ORDER BY nama";
        $data['members'] = q($sql, [], false);
        $sql = "SELECT p.*, o.nama nama_outlet FROM tb_paket p JOIN tb_outlet o ON o.id=p.id_outlet";
        $p = [];
        if ($u['role'] !== 'admin') {
            $sql .= " WHERE p.id_outlet = ?";
            $p[] = $u['id_outlet'];
        }
        $sql .= " ORDER BY o.nama, p.nama_paket";
        $data['pakets'] = q($sql, $p, false);
        $data['outlets'] = q("SELECT id, nama FROM tb_outlet ORDER BY nama", [], false);
        view('transaksi/form', ['judul' => 'Transaksi Baru', 'data' => $data]);
    }

    /** Simpan transaksi (transaksi DB + stored procedure hitung total) */
    public function storeAction()
    {
        csrf_check();
        $u = $_SESSION['user'];
        $member = (int) ($_POST['id_member'] ?? 0);
        $outlet = $u['role'] === 'admin' ? (int) ($_POST['id_outlet'] ?? 0) : (int) $u['id_outlet'];
        $batas = $_POST['batas_waktu'] ?? date('Y-m-d H:i');
        $tamb = (int) ($_POST['biaya_tambahan'] ?? 0);
        $diskon = (float) ($_POST['diskon'] ?? 0);
        $pajak = (int) ($_POST['pajak'] ?? 0);
        $paket = $_POST['paket'] ?? [];      // [id_paket => qty]
        $ket = $_POST['keterangan'] ?? [];

        $paket = array_filter($paket, fn($q) => (float) $q > 0);
        if (!$member || !$paket) {
            flash('Pelanggan dan minimal 1 paket wajib diisi!', 'danger');
            redirect('transaksi', 'create');
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();
            $kode = $this->buatKodeInvoice();
            $st = $pdo->prepare("INSERT INTO tb_transaksi
                (id_outlet, kode_invoice, id_member, tgl, batas_waktu, biaya_tambahan, diskon, pajak, status, dibayar, id_user)
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, 'baru', 'belum_dibayar', ?)");
            $st->execute([$outlet, $kode, $member, $batas, $tamb, $diskon, $pajak, $u['id']]);
            $idTrx = (int) $pdo->lastInsertId();

            $stD = $pdo->prepare("INSERT INTO tb_detail_transaksi (id_transaksi, id_paket, qty, keterangan) VALUES (?,?,?,?)");
            foreach ($paket as $idP => $qty) {
                $stD->execute([$idTrx, (int) $idP, (float) $qty, $ket[$idP] ?? null]);
            }
            $pdo->commit();
            flash("Transaksi $kode berhasil dibuat!");
            redirect('transaksi', 'detail', ['id' => $idTrx]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            die('Gagal menyimpan transaksi: ' . htmlspecialchars($e->getMessage()));
        }
    }

    /** Detail + struk (bisa dicetak) */
    public function detailAction()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $t = q("SELECT t.*, m.nama nama_member, m.alamat alamat_member, m.tlp tlp_member,
                       o.nama nama_outlet, o.alamat alamat_outlet, us.nama nama_kasir
                FROM tb_transaksi t
                JOIN tb_member m ON m.id=t.id_member
                JOIN tb_outlet o ON o.id=t.id_outlet
                JOIN tb_user us ON us.id=t.id_user WHERE t.id=?", [$id]);
        if (!$t) {
            flash('Transaksi tidak ditemukan!', 'danger');
            redirect('transaksi');
        }
        if ($_SESSION['user']['role'] !== 'admin' && (int) $t['id_outlet'] !== (int) $_SESSION['user']['id_outlet']) {
            flash('Transaksi ini bukan milik outlet Anda!', 'danger');
            redirect('transaksi');
        }

        $detail = q("SELECT d.*, p.nama_paket, p.jenis, p.harga, (d.qty*p.harga) subtotal
                     FROM tb_detail_transaksi d JOIN tb_paket p ON p.id=d.id_paket
                     WHERE d.id_transaksi=?", [$id], false);

        // hitung total via STORED PROCEDURE
        $st = db()->prepare("CALL sp_hitung_total(?, @tot)");
        $st->execute([$id]);
        $total = (float) (q("SELECT @tot tot")['tot'] ?? 0);

        view('transaksi/detail', [
            'judul' => 'Detail Transaksi ' . $t['kode_invoice'],
            't' => $t,
            'detail' => $detail,
            'total' => $total,
        ]);
    }

    /** Update status / pembayaran */
    public function updateStatusAction()
    {
        csrf_check();
        $id = (int) ($_POST['id'] ?? 0);
        $user = $_SESSION['user'];
        if ($user['role'] !== 'admin') {
            $transaction = q("SELECT id_outlet FROM tb_transaksi WHERE id=?", [$id]);
            if (!$transaction || (int) $transaction['id_outlet'] !== (int) $user['id_outlet']) {
                flash('Transaksi ini bukan milik outlet Anda!', 'danger');
                redirect('transaksi');
            }
        }
        $status = in_array($_POST['status'] ?? '', ['baru', 'proses', 'selesai', 'diambil']) ? $_POST['status'] : 'baru';
        $dibayar = ($_POST['dibayar'] ?? '') === 'dibayar' ? 'dibayar' : 'belum_dibayar';
        $tglBayar = $dibayar === 'dibayar' ? NOWSQL() : null;

        if ($tglBayar) {
            q("UPDATE tb_transaksi SET status=?, dibayar=?, tgl_bayar=? WHERE id=?", [$status, $dibayar, $tglBayar, $id]);
        } else {
            q("UPDATE tb_transaksi SET status=?, dibayar=? WHERE id=?", [$status, $dibayar, $id]);
        }
        // Trigger DB otomatis mencatat perubahan ke tb_log
        $this->log("Update status transaksi #$id menjadi $status / $dibayar");
        flash('Status transaksi berhasil diupdate!');
        redirect('transaksi', 'detail', ['id' => $id]);
    }
}

/** NOW() sebagai SQL literal */
function NOWSQL(): string
{
    return date('Y-m-d H:i:s');
}
