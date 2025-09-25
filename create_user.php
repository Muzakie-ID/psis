<?php
// create_user.php - gunakan sekali untuk membuat akun
require_once 'config.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');

    if ($username === '' || $password === '') {
        $msg = "Username & password wajib diisi.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, nisn, kelas) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$username, $hash, $full_name, $nisn, $kelas]);
            $msg = "User berhasil dibuat. Kamu bisa login di index.php";
        } catch (Exception $e) {
            $msg = "Gagal membuat user: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Buat User</title><link rel="stylesheet" href="static/css/style.css"></head>
<body>
<div style="padding:30px;max-width:600px;margin:30px auto;background:#fff;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.06)">
  <h2>Buat User Baru</h2>
  <?php if($msg): ?><div style="margin-bottom:12px;color:#b00020"><?=htmlspecialchars($msg)?></div><?php endif; ?>
  <form method="post">
    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="full_name"></div>
    <div class="form-group"><label>NISN</label><input type="text" name="nisn"></div>
    <div class="form-group"><label>Kelas</label><input type="text" name="kelas"></div>
    <div style="display:flex;gap:10px"><button class="btn btn-primary" type="submit">Buat User</button><a href="index.php" class="btn btn-secondary" style="text-decoration:none;padding:10px 15px;display:inline-block">Login</a></div>
  </form>
</div>
</body>
</html>
