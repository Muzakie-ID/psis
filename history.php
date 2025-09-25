<?php
require_once 'config.php';
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}
$user = current_user($pdo);

// Ambil SEMUA pengaduan milik pengguna ini
$stmt = $pdo->prepare("SELECT id, title, description, status, DATE_FORMAT(created_at, '%d %M %Y') as created_at FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cssPath = file_exists(__DIR__ . '/static/css/style.css') ? 'static/css/style.css' : 'style.css';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengaduan - Sistem Pengaduan Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath) ?>">
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
                <a href="history.php" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit;">
                    <i class="fas fa-history"></i>
                    <span class="menu-text">Riwayat</span>
                </a>
            </li>
            <li class="menu-item" data-page="profile">
                <i class="fas fa-user"></i>
                <span class="menu-text">Profil</span>
            </li>
            <li class="menu-item" data-page="settings">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Pengaturan</span>
            </li>
            <li class="menu-item" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Keluar</span>
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="page-title">Riwayat Pengaduan</div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></div>
                    <div class="user-role"><?= htmlspecialchars($user['kelas'] ?: 'Siswa')?></div>
                </div>
                <div class="user-avatar"><?= strtoupper(substr(htmlspecialchars($user['full_name'] ?: $user['username']),0,2)) ?></div>
            </div>
        </div>
        
        <div class="content">
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">Semua Pengaduan Anda</h2>
                    <p class="form-description">Berikut adalah daftar lengkap semua pengaduan yang pernah Anda ajukan.</p>
                </div>
                
                <div class="complaint-list">
                    <?php if (empty($complaints)): ?>
                        <p style="color:#6c757d; text-align:center;">Anda belum pernah membuat pengaduan.</p>
                    <?php else: ?>
                        <?php foreach ($complaints as $c): ?>
                            <a href="view_complaint.php?id=<?= $c['id'] ?>" class="complaint-item-link">
                                <div class="complaint-item">
                                    <div class="complaint-info">
                                        <h3><?= htmlspecialchars($c['title']) ?></h3>
                                        <p>Diajukan pada: <?= htmlspecialchars($c['created_at']) ?></p>
                                    </div>
                                    <div class="status status-<?= htmlspecialchars($c['status']) ?>"><?= ucfirst($c['status']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    </body>
</html>
