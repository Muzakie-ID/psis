<?php
require_once 'config.php';
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}
$user = current_user($pdo);


// Koneksi database
$koneksi = new mysqli("localhost", "root", "", "SIS");

// Query hitung status
$sql = "SELECT status, COUNT(*) as total FROM complaints GROUP BY status";
$result = $koneksi->query($sql);

// Siapkan default
$counts = [
    'pending' => 0,
    'process' => 0,
    'resolved' => 0,
    'waiting' => 0
];

while ($row = $result->fetch_assoc()) {
    $counts[$row['status']] = $row['total'];
}


// pilih path css/js jika ada di static atau root
$cssPath = file_exists(__DIR__ . '/static/css/style.css') ? 'static/css/style.css' : (file_exists(__DIR__.'/style.css') ? 'style.css' : 'static/css/style.css');
$jsPath = file_exists(__DIR__ . '/static/js/script.js') ? 'static/js/script.js' : (file_exists(__DIR__.'/script.js') ? 'script.js' : 'static/js/script.js');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Siswa - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
// LOGOUT modal fix
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logout-btn');
    const logoutModal = document.getElementById('logout-modal');
    const logoutCancelBtn = document.getElementById('logout-cancel-btn');
    const logoutConfirmBtn = document.getElementById('logout-confirm-btn');

    if (logoutBtn && logoutModal) {
        logoutBtn.addEventListener('click', () => {
            logoutModal.style.display = 'flex';
        });
    }

    if (logoutCancelBtn) {
        logoutCancelBtn.addEventListener('click', () => {
            logoutModal.style.display = 'none';
        });
    }

    if (logoutConfirmBtn) {
        logoutConfirmBtn.addEventListener('click', () => {
            window.location.href = 'logout.php';
        });
    }
});
</script>

    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath) ?>">
</head>
<body>
    <!-- Sidebar Navigation -->
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
            <!--<li class="menu-item" data-page="physical-psychical">
                <i class="fas fa-heartbeat"></i>
                <span class="menu-text">Fisik/Psikis</span>
            </li>
            <li class="menu-item" data-page="history">
                <i class="fas fa-history"></i>
                <span class="menu-text">Riwayat</span>
            </li>-->
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
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-title" id="page-title">Dashboard</div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></div>
                    <div class="user-role"><?=htmlspecialchars($user['kelas'] ?: 'Siswa')?></div>
                </div>
                <div class="user-avatar"><?=strtoupper(substr(htmlspecialchars($user['full_name'] ?: $user['username']),0,2))?></div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content">
            <!-- Dashboard Page -->
            <div class="page active" id="dashboard-page">
                <div class="dashboard-grid">
    <div class="card card-warning">
        <i class="fas fa-exclamation-circle"></i>
        <div class="card-title">Pengaduan Baru</div>
        <div class="card-value" id="new-count"><?= $counts['pending'] ?></div>
    </div>
    
    <div class="card card-primary">
        <i class="fas fa-tasks"></i>
        <div class="card-title">Sedang Diproses</div>
        <div class="card-value" id="process-count"><?= $counts['process'] ?></div>
    </div>
    
    <div class="card card-success">
        <i class="fas fa-check-circle"></i>
        <div class="card-title">Selesai</div>
        <div class="card-value" id="resolved-count"><?= $counts['resolved'] ?></div>
    </div>
    
    <div class="card card-danger">
        <i class="fas fa-clock"></i>
        <div class="card-title">Menunggu Respons</div>
        <div class="card-value" id="waiting-count"><?= $counts['pending'] ?></div>
    </div>
</div>
                
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Pengaduan Terbaru</h2>
                        <p class="form-description">Daftar pengaduan yang baru Anda ajukan</p>
                    </div>
                    
                    <div class="complaint-list" id="complaint-list">
                        <!-- will be loaded by JS -->
                    </div>
                </div>
            </div>
            
            <!-- Complaint Page -->
            <div class="page" id="complaint-page">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Buat Pengaduan Baru</h2>
                        <p class="form-description">Isi formulir di bawah untuk mengajukan pengaduan</p>
                    </div>
                    
                    <form id="form-complaint" method="post" enctype="multipart/form-data" action="submit_complaint.php">
                        <div class="form-group">
                            <label for="category">Kategori Pengaduan</label>
                            <select id="category" name="category" required>
                                <option value="">Pilih kategori</option>
                                <option value="academic">Akademik</option>
                                <option value="facility">Fasilitas Sekolah</option>
                                <option value="bullying">Perundungan</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Judul Pengaduan</label>
                            <input type="text" id="title" name="title" placeholder="Masukkan judul pengaduan" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea id="description" name="description" rows="5" placeholder="Jelaskan pengaduan Anda secara detail"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="attachment">Lampiran (opsional)</label>
                            <input type="file" id="attachment" name="attachment">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Ajukan Pengaduan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Physical/Psychical Page (placeholder agar script.js tidak error) -->
            <div class="page" id="physical-psychical-page">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Fisik / Psikis</h2>
                        <p class="form-description">Halaman placeholder.</p>
                    </div>
                    <p style="color:#6c757d">Konten halaman Fisik / Psikis</p>
                </div>
            </div>

            <!-- History Page -->
            <div class="page" id="history-page">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Riwayat Pengaduan</h2>
                        <p class="form-description">Daftar lengkap pengaduan Anda</p>
                    </div>
                    <div id="history-list" class="complaint-list"></div>
                </div>
            </div>

            <!-- Profile Page -->
            <div class="page" id="profile-page">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Profil Saya</h2>
                        <p class="form-description">Informasi akun</p>
                    </div>
                    <div class="profile-info">
                        <p>Nama: <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></p>
                        <p>Username: <?= htmlspecialchars($user['username']) ?></p>
                        <p>Kelas: <?= htmlspecialchars($user['kelas'] ?: '-') ?></p>
                        <p>NISN: <?= htmlspecialchars($user['nisn'] ?: '-') ?></p>
                    </div>
                </div>
            </div>

            <!-- Settings Page -->
            <div class="page" id="settings-page">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Pengaturan</h2>
                        <p class="form-description">Atur preferensi akun Anda</p>
                    </div>
                    <p style="color:#6c757d">Pengaturan dasar (placeholder)</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Konfirmasi Pengaduan -->
    <div class="confirmation-modal" id="confirmation-modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-check-circle"></i>
                <h2>Pengaduan Terkirim!</h2>
            </div>
            <p>Pengaduan Anda telah berhasil dikirim.</p>
            <p>Anda akan menerima notifikasi ketika ada tanggapan.</p>
            <div class="modal-buttons">
                <button class="btn btn-secondary" id="modal-close-btn">Tutup</button>
                <button class="btn btn-primary" id="modal-dashboard-btn">Ke Dashboard</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <div class="logout-modal" id="logout-modal">
        <div class="logout-modal-content">
            <div class="logout-modal-header">
                <i class="fas fa-sign-out-alt"></i>
                <h2>Konfirmasi Keluar</h2>
            </div>
            <p>Apakah Anda yakin ingin keluar, <span id="logout-user-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></span>?</p>
            <div class="logout-modal-buttons">
                <button class="btn btn-secondary" id="logout-cancel-btn">Batal</button>
                <a href="logout.php" class="btn btn-danger" id="logout-confirm-btn">Keluar</a>
            </div>
        </div>
    </div>

    <div class="menu-toggle" id="menu-toggle" style="display: none;">
        <i class="fas fa-bars"></i>
    </div>

    <script>
    // sedikit adaptasi kecil agar elemen dynamic dari PHP tidak terjadi error
    document.addEventListener('DOMContentLoaded', function() {
        // load complaints via endpoint (if ada)
        if (typeof fetch === 'function') {
            fetch('get_complaints.php')
            .then(r => r.json())
            .then(data => {
                try {
                    const list = document.getElementById('complaint-list');
                    if (!list) return;
                    list.innerHTML = '';
                    if (!Array.isArray(data) || data.length === 0) {
                        list.innerHTML = '<p style="color:#6c757d">Belum ada pengaduan.</p>';
                        return;
                    }
                    data.forEach(c => {
                        const div = document.createElement('div');
                        div.className = 'complaint-item';
                        div.innerHTML = '<div class="complaint-info"><h3>' + (c.title||'') + '</h3><p>Diajukan pada: ' + (c.created_at||'') + '</p></div><div class="status ' + (c.status||'') + '">' + (c.status||'') + '</div>';
                        list.appendChild(div);
                    });
                } catch(e) {
                    console.warn('Error render complaints', e);
                }
            }).catch(()=>{});
        }
    });
    </script>

    <script src="<?= htmlspecialchars($jsPath) ?>"></script>
</body>
</html>
