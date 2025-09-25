<?php
// index.php (login)
require_once 'config.php';

// jika sudah login redirect ke dashboard
if (is_logged_in()) {
    // cek role dulu
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $err = "Isi username dan password.";
    } else {
        // ambil role juga
        $stmt = $pdo->prepare("SELECT id, password, full_name, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // login success
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'] ?: $username;
            $_SESSION['user_role'] = $user['role']; // simpan role

            // cek role buat redirect
            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $err = "Username atau password salah.";
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Sistem Pengaduan Siswa</title>
  <link rel="stylesheet" href="static/css/style.css">
  <style>
    /* sederhana menyesuaikan style.css agar form login terlihat */
    .login-wrapper{
      min-height:100vh;
      display:flex;align-items:center;justify-content:center;background:#f5f7fa;
      padding:20px;
    }
    .login-card{
      background:white;padding:30px;border-radius:10px;width:420px;box-shadow:0 8px 30px rgba(0,0,0,0.08);
    }
    .login-card h2{margin-bottom:15px}
    .form-group{margin-bottom:12px}
    input[type="text"],input[type="password"]{width:100%;padding:10px;border-radius:8px;border:1px solid #ddd}
    .error{color: #b00020;margin-bottom:12px}
    .brand{display:flex;gap:10px;align-items:center;margin-bottom:10px}
    .brand .logo{width:40px;height:40px;border-radius:8px;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-weight:700}
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="brand">
        <div class="logo">SP</div>
        <div>
          <h3 style="margin:0">Sistem Pengaduan Siswa</h3>
          <small style="color:#6c757d">Silakan login untuk melanjutkan</small>
        </div>
      </div>

      <?php if($err): ?>
        <div class="error"><?=htmlspecialchars($err)?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
          <button class="btn btn-primary" type="submit">Login</button>
          <a href="#" style="color:#6c757d;text-decoration:none;margin-left:auto;font-size:0.9rem">Bantuan?</a>
        </div>
      </form>
      <p style="margin-top:12px;color:#6c757d;font-size:0.9rem">Gunakan create_user.php untuk membuat akun jika belum ada.</p>
    </div>
  </div>
</body>
</html>
