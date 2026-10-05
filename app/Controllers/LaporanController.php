<?php
/** LaporanController - laporan & cetak (owner & admin) */
class LaporanController
{
    private function dataLaporan(): array
    {
        $u = $_SESSION['user'];
        $filters = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? $_POST : $_GET;
        $d1 = $filters['dari'] ?? date('Y-m-01');
        $d2 = $filters['sampai'] ?? date('Y-m-d');
        $outlet = $u['role'] === 'admin' ? (int) ($filters['id_outlet'] ?? 0) : (int) $u['id_outlet'];
        $status = in_array($filters['status'] ?? '', ['baru', 'proses', 'selesai', 'diambil'], true)
            ? $filters['status'] : '';

        foreach ([$d1, $d2] as $date) {
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) {
                $d1 = date('Y-m-01');
                $d2 = date('Y-m-d');
                break;
            }
        }
        if ($d1 > $d2) {
            [$d1, $d2] = [$d2, $d1];
        }

        $sql = "SELECT t.*, m.nama nama_member, o.nama nama_outlet, us.nama nama_kasir,
                   IFNULL(dt.total,0) total_paket, dt.detail_paket
                FROM tb_transaksi t
                JOIN tb_member m ON m.id=t.id_member
                JOIN tb_outlet o ON o.id=t.id_outlet
                JOIN tb_user us ON us.id=t.id_user
                LEFT JOIN (
                    SELECT d.id_transaksi, SUM(d.qty*p.harga) total,
                           GROUP_CONCAT(CONCAT(p.nama_paket, ' x', d.qty) ORDER BY p.nama_paket SEPARATOR ', ') detail_paket
                    FROM tb_detail_transaksi d JOIN tb_paket p ON p.id=d.id_paket
                    GROUP BY d.id_transaksi
                ) dt ON dt.id_transaksi = t.id
                WHERE DATE(t.tgl) BETWEEN ? AND ?";
        $p = [$d1, $d2];
        if ($outlet > 0) {
            $sql .= " AND t.id_outlet = ?";
            $p[] = $outlet;
        }
        if ($status !== '') {
            $sql .= " AND t.status = ?";
            $p[] = $status;
        }
        $sql .= " ORDER BY t.tgl DESC";
        $data['rows'] = q($sql, $p, false);

        foreach ($data['rows'] as &$r) {
            $r['detail_paket'] = $r['detail_paket'] ?? '-';
            $r['total_tagihan'] = (($r['total_paket'] + $r['biaya_tambahan'] + $r['pajak']) -
                ($r['diskon'] / 100 * ($r['total_paket'] + $r['biaya_tambahan'] + $r['pajak'])));
        }
        unset($r);
        $data['total_all'] = array_sum(array_column($data['rows'], 'total_tagihan'));
        $data['total_bayar'] = array_sum(array_map(fn($r) => $r['dibayar'] === 'dibayar' ? $r['total_tagihan'] : 0, $data['rows']));
        $data['outlets'] = $u['role'] === 'admin'
            ? q("SELECT id, nama FROM tb_outlet ORDER BY nama", [], false)
            : q("SELECT id, nama FROM tb_outlet WHERE id=?", [$u['id_outlet']], false);
        $data['filter'] = ['dari' => $d1, 'sampai' => $d2, 'id_outlet' => $outlet, 'status' => $status];
        return $data;
    }

    public function indexAction(): void
    {
        $data = $this->dataLaporan();
        $user = $_SESSION['user'];
        $sql = "SELECT r.*, u.nama nama_user, o.nama nama_outlet
                FROM tb_laporan_pdf r
                JOIN tb_user u ON u.id=r.id_user
                LEFT JOIN tb_outlet o ON o.id=r.id_outlet";
        $params = [];
        if ($user['role'] !== 'admin') {
            $sql .= " WHERE r.id_outlet=?";
            $params[] = $user['id_outlet'];
        }
        $data['saved'] = q($sql . " ORDER BY r.created_at DESC LIMIT 30", $params, false);
        view('laporan/laporan', ['judul' => 'Laporan Transaksi', 'data' => $data]);
    }

    public function generateAction(): void
    {
        csrf_check();
        $data = $this->dataLaporan();
        $directory = dirname(__DIR__, 2) . '/storage/private/reports';
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            flash('Folder penyimpanan laporan tidak dapat dibuat.', 'danger');
            redirect('laporan');
        }

        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            ob_start();
            require __DIR__ . '/../../views/laporan/pdf.php';
            $html = (string) ob_get_clean();

            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $pdf = $dompdf->output();

            $filename = 'londryraa-laporan-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.pdf';
            if (file_put_contents($directory . '/' . $filename, $pdf, LOCK_EX) === false) {
                throw new RuntimeException('File PDF tidak dapat disimpan.');
            }

            q("INSERT INTO tb_laporan_pdf (id_user, id_outlet, dari, sampai, status, nama_file)
               VALUES (?, ?, ?, ?, ?, ?)", [
                $_SESSION['user']['id'],
                $data['filter']['id_outlet'] ?: null,
                $data['filter']['dari'],
                $data['filter']['sampai'],
                $data['filter']['status'] ?: null,
                $filename,
            ]);
            flash('PDF berhasil disimpan. Anda dapat mengunduh atau mencetaknya dari daftar laporan.');
        } catch (Throwable $e) {
            if (isset($filename) && is_file($directory . '/' . $filename)) {
                unlink($directory . '/' . $filename);
            }
            flash('Gagal membuat PDF: ' . $e->getMessage(), 'danger');
        }
        redirect('laporan');
    }

    public function downloadPdfAction(): void
    {
        $this->servePdf(true);
    }

    public function printPdfAction(): void
    {
        $this->servePdf(false);
    }

    private function servePdf(bool $download): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $report = q("SELECT * FROM tb_laporan_pdf WHERE id=?", [$id]);
        $user = $_SESSION['user'];
        if (!$report || ($user['role'] !== 'admin' && (int) $report['id_outlet'] !== (int) $user['id_outlet'])) {
            http_response_code(404);
            exit('Laporan tidak ditemukan.');
        }

        $filename = basename($report['nama_file']);
        $path = dirname(__DIR__, 2) . '/storage/private/reports/' . $filename;
        if (!is_file($path)) {
            http_response_code(404);
            exit('File PDF tidak ditemukan.');
        }

        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }
}
