<?php
require_once 'config.php';
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}
$user = current_user($pdo);

// --- LOGIKA FILTER BARU ---
$filter_status = $_GET['status'] ?? 'all'; 

// Kueri SQL untuk mengambil data pengaduan
$sql = "SELECT id, title, description, status, DATE_FORMAT(created_at, '%d %M %Y') as created_at FROM complaints WHERE user_id = :user_id";
$params = ['user_id' => $user['id']];

if ($filter_status !== 'all' && in_array($filter_status, ['pending', 'process', 'resolved'])) {
    $sql .= " AND status = :status";
    $params['status'] = $filter_status;
}

// INI BAGIAN YANG DIPERBAIKI
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cssPath = file_exists(__DIR__ . '/static/css/style.css') ? 'static/css/style.css' : 'style.css';
$jsPath = file_exists(__DIR__ . '/static/js/script.js') ? 'static/js/script.js' : 'script.js';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengaduan - Sistem Pengaduan Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath) ?>">
    <style>
        .filter-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-form select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }
        .filter-form button {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            background-color: var(--primary);
            color: white;
            cursor: pointer;
            font-size: 1rem;
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
            <li class="menu-item">
                <a href="dashboard.php" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-home"></i><span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="dashboard.php#complaint" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-plus-circle"></i><span class="menu-text">Buat Pengaduan</span>
                </a>
            </li>
            <li class="menu-item active">
                <a href="history.php" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-history"></i><span class="menu-text">Riwayat</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="dashboard.php#profile" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-user"></i><span class="menu-text">Profil</span>
                </a>
            </li>
            <li class="menu-item" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i><span class="menu-text">Keluar</span>
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
                    <p class="form-description">Filter untuk melihat pengaduan berdasarkan statusnya.</p>
                </div>
                
                <form action="history.php" method="GET" class="filter-form">
                    <label for="status">Filter Status:</label>
                    <select name="status" id="status">
                        <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Semua</option>
                        <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="process" <?= $filter_status == 'process' ? 'selected' : '' ?>>Diproses</option>
                        <option value="resolved" <?= $filter_status == 'resolved' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                    <button type="submit">Filter</button>
                </form>

                <div class="complaint-list">
                    <?php if (empty($complaints)): ?>
                        <p style="color:#6c757d; text-align:center;">Tidak ada pengaduan yang cocok dengan filter ini.</p>
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
