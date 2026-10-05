<?php
/**
 * DashboardController - statistik per role
 */
class DashboardController
{
    public function indexAction()
    {
        $role = $_SESSION['user']['role'];
        $o = $_SESSION['user']['id_outlet'];

        if ($role === 'admin') {
            $data['transaksi_total'] = (int) (q("SELECT COUNT(*) c FROM tb_transaksi")['c'] ?? 0);
            $data['transaksi_baru'] = (int) (q("SELECT COUNT(*) c FROM tb_transaksi WHERE status='baru'")['c'] ?? 0);
        } else {
            $data['transaksi_total'] = (int) (q("SELECT COUNT(*) c FROM tb_transaksi WHERE id_outlet=?", [$o])['c'] ?? 0);
            $data['transaksi_baru'] = (int) (q("SELECT COUNT(*) c FROM tb_transaksi WHERE id_outlet=? AND status='baru'", [$o])['c'] ?? 0);
        }
        $data['member_total'] = (int) (q("SELECT COUNT(*) c FROM tb_member")['c'] ?? 0);
        $data['outlet_total'] = $role === 'admin'
            ? (int) (q("SELECT COUNT(*) c FROM tb_outlet")['c'] ?? 0)
            : (int) ($o > 0 ? 1 : 0);
        $data['user_total'] = (int) (q("SELECT COUNT(*) c FROM tb_user")['c'] ?? 0);

        $totalSql = "SELECT SUM(d.qty*p.harga) t FROM tb_detail_transaksi d
                     JOIN tb_paket p ON p.id=d.id_paket";
        $where = $role !== 'admin' ? " AND p.id_outlet = $o" : "";
        $data['pendapatan'] = (float) (q($totalSql . " WHERE 1=1" . $where)['t'] ?? 0);

        // grafik: pendapatan 7 hari terakhir
        $rows = q("SELECT DATE(t.tgl) tgl, SUM(d.qty*p.harga) total
                   FROM tb_transaksi t
                   JOIN tb_detail_transaksi d ON d.id_transaksi=t.id
                   JOIN tb_paket p ON p.id=d.id_paket
                   WHERE t.tgl >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)" . $where . "
                   GROUP BY DATE(t.tgl)", [], false);
        $grafik = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = date('Y-m-d', strtotime("-$i day"));
            $val = 0;
            foreach ($rows as $r)
                if ($r['tgl'] === $tgl)
                    $val = (float) $r['total'];
            $grafik[] = ['tgl' => date('d M', strtotime($tgl)), 'total' => $val];
        }
        $data['grafik'] = $grafik;

        // transaksi terbaru
        $recentSql = "SELECT t.*, m.nama nama_member FROM tb_transaksi t
                      JOIN tb_member m ON m.id=t.id_member";
        $recentParams = [];
        if ($role !== 'admin') {
            $recentSql .= " WHERE t.id_outlet=?";
            $recentParams[] = $o;
        }
        $data['terbaru'] = q($recentSql . " ORDER BY t.tgl DESC LIMIT 5", $recentParams, false);

        view('dashboard/dashboard', ['judul' => 'Dashboard', 'data' => $data]);
    }
}
