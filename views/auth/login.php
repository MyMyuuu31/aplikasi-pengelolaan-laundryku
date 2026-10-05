<?php require_once __DIR__ . '/../../config/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk | londryraa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body class="bg-login d-flex align-items-center justify-content-center min-vh-100">
  <div class="card login-card shadow-lg border-0">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="login-icon mx-auto mb-3"><i class="fa-solid fa-soap"></i></div>
        <h4 class="fw-bold mb-0">londryraa</h4>
        <small class="text-muted">Laundry terasa lebih ringan.</small>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small"><i
            class="fa-solid fa-triangle-exclamation me-1"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= url('auth', 'login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Username</label>
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label small fw-semibold">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
          </div>
        </div>
        <button class="btn btn-primary w-100 fw-semibold"><i class="fa-solid fa-right-to-bracket me-1"></i>
          Login</button>
      </form>

    </div>
  </div>
</body>

</html>