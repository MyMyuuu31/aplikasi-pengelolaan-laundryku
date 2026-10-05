<?php
/**
 * AuthController - login & logout
 */
class AuthController
{
    public function indexAction()
    {
        if (!empty($_SESSION['user']))
            redirect('dashboard');
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function loginAction()
    {
        csrf_check();
        $u = q("SELECT * FROM tb_user WHERE username = ?", [$_POST['username'] ?? '']);
        if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $u['id'],
                'nama' => $u['nama'],
                'username' => $u['username'],
                'role' => $u['role'],
                'id_outlet' => (int) $u['id_outlet'],
            ];
            q(
                "INSERT INTO tb_log (id_user, aktivitas) VALUES (?, ?)",
                [$u['id'], 'Login ke aplikasi']
            );
            redirect('dashboard');
        }
        $_SESSION['login_error'] = 'Username atau password salah!';
        redirect('auth');
    }

    public function logoutAction()
    {
        if (!empty($_SESSION['user'])) {
            q(
                "INSERT INTO tb_log (id_user, aktivitas) VALUES (?, ?)",
                [$_SESSION['user']['id'], 'Logout dari aplikasi']
            );
        }
        session_destroy();
        session_start();
        redirect('auth');
    }
}
