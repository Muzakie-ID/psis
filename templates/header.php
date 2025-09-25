<?php
// templates/header.php

// Pastikan variabel $user sudah ada
if (!isset($user)) {
    // Jika belum ada, panggil fungsi untuk mendapatkannya
    // Ini penting agar nama user di sidebar & modal tetap muncul
    $user = current_user($pdo);
}

$cssPath = file_exists(__DIR__ . '/../static/css/style.css') ? 'static/css/style.css' : '../static/css/style.css';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Sistem Pengaduan Siswa' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath) ?>">
    <style>
        /* Tambahkan style tambahan jika perlu */
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            <span class="logo-text">Sistem Pengaduan</span>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item <?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                <a href="dashboard.php" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-home"></i><span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?= ($active_page ?? '') === 'complaint' ? 'active' : '' ?>">
                <a href="dashboard.php#complaint" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-plus-circle"></i><span class="menu-text">Buat Pengaduan</span>
                </a>
            </li>
            <li class="menu-item <?= ($active_page ?? '') === 'history' ? 'active' : '' ?>">
                <a href="history.php" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-history"></i><span class="menu-text">Riwayat</span>
                </a>
            </li>
            <li class="menu-item <?= ($active_page ?? '') === 'profile' ? 'active' : '' ?>">
                <a href="dashboard.php#profile" style="display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; width:100%;">
                    <i class="fas fa-user"></i><span class="menu-text">Profil</span>
                </a>
            </li>
            <li class="menu-item" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i><span class="menu-text">Keluar</span>
            </li>
        </ul>
    </div>
