<?php
require_once 'config.php';
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}
$user = current_user($pdo);

// Query hitung status PENGGUNA SAAT INI
$stmt = $pdo->prepare("SELECT status, COUNT(*) as total FROM complaints WHERE user_id = ? GROUP BY status");
$stmt->execute([$user['id']]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$counts = ['pending' => 0, 'process' => 0, 'resolved' => 0];
foreach ($results as $row) {
    if (isset($counts[$row['status']])) {
        $counts[$row['status']] = $row['total'];
    }
}

// Ambil dan hapus flash message dari session
$flash_message = $_SESSION['flash_message'] ?? null;
$flash_type = $_SESSION['flash_type'] ?? 'error';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Menambahkan versi untuk memaksa browser memuat file terbaru (cache busting)
$cssPath = 'static/css/style.css?v=' . time();
$jsPath = 'static/js/script.js?v=' . time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Siswa - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath) ?>">
    <style>
        .flash-message { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 8px; 
            font-weight: 500; 
            border: 1px solid transparent;
        }
        .flash-message.success { 
            background-color: #d4edda; 
            color: #155724; 
            border-color: #c3e6cb; 
        }
        .flash-message.error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border-color: #f5c6cb; 
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            <span class="logo-text">Sistem Pengaduan</span>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active" data-page="dashboard">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </li>
            <li class="menu-item" data-page="complaint">
                <i class="fas fa-plus-circle"></i>
                <span class="menu-text">Buat Pengaduan</span>
            </li>
            <li class="menu-item">
                <a href="history.php" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-history"></i>
                    <span class="menu-text">Riwayat</span>
                </a>
            </li>
            <li class="menu-item" data-page="profile">
                <i class="fas fa-user"></i>
                <span class="menu-text">Profil</span>
            </li>
            <li class="menu-item" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Keluar</span>
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="page-title" id="page-title">Dashboard</div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></div>
                    <div class="user-role"><?= htmlspecialchars($user['kelas'] ?: 'Siswa')?></div>
                </div>
                <div class="user-avatar"><?= strtoupper(substr(htmlspecialchars($user['full_name'] ?: $user['username']),0,2)) ?></div>
            </div>
        </div>
        
        <div class="content">
             <?php if ($flash_message): ?>
                <div class="flash-message <?= htmlspecialchars($flash_type) ?>">
                    <?= htmlspecialchars($flash_message) ?>
                </div>
            <?php endif; ?>

            <div class="page active" id="dashboard-page">
                <div class="dashboard-grid">
                    <div class="card card-warning"><i class="fas fa-exclamation-circle"></i><div><div class="card-title">Pending</div><div class="card-value"><?= $counts['pending'] ?></div></div></div>
                    <div class="card card-primary"><i class="fas fa-tasks"></i><div><div class="card-title">Diproses</div><div class="card-value"><?= $counts['process'] ?></div></div></div>
                    <div class="card card-success"><i class="fas fa-check-circle"></i><div><div class="card-title">Selesai</div><div class="card-value"><?= $counts['resolved'] ?></div></div></div>
                </div>
                <div class="form-container">
                    <div class="form-header"><h2 class="form-title">Pengaduan Terbaru</h2><p class="form-description">Daftar pengaduan yang baru Anda ajukan</p></div>
                    <div class="complaint-list" id="complaint-list"></div>
                </div>
            </div>
            
            <div class="page" id="complaint-page">
                <div class="form-container">
                    <div class="form-header"><h2 class="form-title">Buat Pengaduan Baru</h2><p class="form-description">Isi formulir di bawah untuk mengajukan pengaduan</p></div>
                    <form id="form-complaint" method="post" enctype="multipart/form-data" action="submit_complaint.php">
                        <div class="form-group"><label for="category">Kategori Pengaduan</label><select id="category" name="category" required><option value="">Pilih kategori</option><option value="academic">Akademik</option><option value="facility">Fasilitas Sekolah</option><option value="bullying">Perundungan</option><option value="other">Lainnya</option></select></div>
                        <div class="form-group"><label for="title">Judul Pengaduan</label><input type="text" id="title" name="title" placeholder="Masukkan judul pengaduan" required></div>
                        <div class="form-group"><label for="description">Deskripsi</label><textarea id="description" name="description" rows="5" placeholder="Jelaskan pengaduan Anda secara detail"></textarea></div>
                        
                        <div class="form-group">
                            <label for="urgency">Tingkat Urgensi</label>
                            <select id="urgency" name="urgency" required>
                                <option value="low">Rendah</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                            </select>
                        </div>

                        <div class="form-group"><label for="attachment">Lampiran (opsional, maks 5MB)</label><input type="file" id="attachment" name="attachment"></div>
                        <div class="form-group"><button type="submit" class="btn btn-primary">Ajukan Pengaduan</button></div>
                    </form>
                </div>
            </div>

            <div class="page" id="profile-page">
                <div class="form-container">
                    <div class="form-header"><h2 class="form-title">Profil Saya</h2><p class="form-description">Perbarui informasi data diri Anda di sini.</p></div>
                    <form action="update_profile.php" method="POST">
                        <div class="form-group"><label>Username</label><input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled></div>
                        <div class="form-group"><label for="full_name">Nama Lengkap</label><input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required></div>
                        <div class="form-group"><label for="kelas">Kelas</label><input type="text" id="kelas" name="kelas" value="<?= htmlspecialchars($user['kelas']) ?>" required></div>
                        <div class="form-group"><label for="nisn">NISN</label><input type="text" id="nisn" name="nisn" value="<?= htmlspecialchars($user['nisn']) ?>"></div>
                        <div class="form-group"><button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan Profil</button></div>
                    </form>
                </div>
                <div class="form-container">
                    <div class="form-header"><h2 class="form-title">Ganti Password</h2><p class="form-description">Biarkan kosong jika Anda tidak ingin mengubah password.</p></div>
                    <form action="update_profile.php" method="POST">
                        <div class="form-group"><label for="old_password">Password Lama</label><input type="password" id="old_password" name="old_password" required></div>
                        <div class="form-group"><label for="new_password">Password Baru (min. 8 karakter)</label><input type="password" id="new_password" name="new_password" required></div>
                        <div class="form-group"><label for="confirm_password">Konfirmasi Password Baru</label><input type="password" id="confirm_password" name="confirm_password" required></div>
                        <div class="form-group"><button type="submit" name="change_password" class="btn btn-danger">Ganti Password</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="logout-modal" id="logout-modal">
        <div class="logout-modal-content">
             <div class="logout-modal-header"><i class="fas fa-sign-out-alt"></i><h2>Konfirmasi Keluar</h2></div>
             <p>Apakah Anda yakin ingin keluar, <span id="logout-user-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></span>?</p>
             <div class="logout-modal-buttons">
                <button class="btn btn-secondary" id="logout-cancel-btn">Batal</button>
                <a href="logout.php" class="btn btn-danger">Keluar</a>
             </div>
        </div>
    </div>
    
    <script src="<?= htmlspecialchars($jsPath) ?>"></script>
    
</body>
</html>
