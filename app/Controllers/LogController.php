<?php
/** LogController - riwayat aktivitas (admin) */
class LogController
{
    public function indexAction()
    {
        $data['rows'] = q("SELECT l.*, u.nama nama_user, u.username
                           FROM tb_log l LEFT JOIN tb_user u ON u.id=l.id_user
                           ORDER BY l.waktu DESC LIMIT 200", [], false);
        view('log/log', ['judul' => 'Log Aktivitas Sistem', 'data' => $data]);
    }
}
