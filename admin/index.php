<?php
require_once '../config.php';
// Autentikasi dan otorisasi admin
if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$user = current_user($pdo);

// Logika untuk aksi (delete, process, resolved)
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM complaints WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] === 'process') {
        $stmt = $pdo->prepare("UPDATE complaints SET status = 'process', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] === 'resolved') {
        $stmt = $pdo->prepare("UPDATE complaints SET status = 'resolved', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: index.php");
    exit;
}

// Query untuk data kartu statistik
$stmt_counts = $pdo->query("SELECT status, COUNT(*) as total FROM complaints GROUP BY status");
$counts_data = $stmt_counts->fetchAll(PDO::FETCH_KEY_PAIR);

$counts = [
    'pending' => $counts_data['pending'] ?? 0,
    'process' => $counts_data['process'] ?? 0,
    'resolved' => $counts_data['resolved'] ?? 0,
];
$total_complaints = array_sum($counts);

// Query untuk mengambil semua data pengaduan untuk tabel
$stmt = $pdo->query("SELECT c.*, u.full_name, u.username, u.kelas 
                     FROM complaints c 
                     JOIN users u ON c.user_id = u.id 
                     ORDER BY c.created_at DESC");
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Pengaduan Siswa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
        --primary: #4361ee;
        --secondary: #3a0ca3;
        --light: #f8f9fa;
        --dark: #212529;
        --success: #20c997; /* Warna hijau yang lebih bagus */
        --warning: #fd7e14; /* Warna oranye yang lebih bagus */
        --info: #0dcaf0;
        --danger: #f94144;
    }
    body { 
        background: #f5f7fa; 
        font-family: 'Segoe UI', sans-serif; 
        margin: 0;
    }
    .main-content {
        padding: 30px;
    }
    .header {
        background: white;
        padding: 0 30px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .header h1 {
        font-size: 1.5rem;
        margin: 0;
        color: var(--dark);
    }
    .user-menu a {
        color: var(--primary);
        text-decoration: none;
        margin-left: 15px;
    }
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .card .icon {
        font-size: 1.8rem;
        padding: 18px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card.total .icon { background: var(--secondary); }
    .card.pending .icon { background: var(--warning); }
    .card.process .icon { background: var(--primary); }
    .card.resolved .icon { background: var(--success); }

    .card-title { font-size: 0.9rem; color: #6c757d; }
    .card-value { font-size: 1.7rem; font-weight: 700; color: var(--dark); }

    .table-container {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .table-container h2 { margin-top: 0; }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }
    th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    tr:hover {
        background-color: #f1f3f5;
    }
    .status { font-weight: 600; }
    .status-pending { color: var(--warning); }
    .status-process { color: var(--primary); }
    .status-resolved { color: var(--success); }

    a.btn {
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        margin-right: 5px;
        font-size: 0.9em;
        display: inline-block;
        border: none;
        color: white;
        cursor: pointer;
    }
    .btn-detail { background: #6c757d; }
    .btn-process { background: var(--primary); }
    .btn-resolved { background: #28a745; }
    .btn-delete { background: var(--danger); }
  </style>
</head>
<body>

  <header class="header">
    <h1>Admin Dashboard</h1>
    <div class="user-menu">
      <span>Halo, <strong><?= htmlspecialchars($user['full_name'] ?: "Admin") ?></strong></span>
      <a href="../dashboard.php">Lihat Dasbor Siswa</a>
      <a href="../logout.php">Logout</a>
    </div>
  </header>

  <main class="main-content">
    <div class="dashboard-grid">
        <div class="card total">
            <div class="icon"><i class="fas fa-inbox"></i></div>
            <div>
                <div class="card-title">Total Pengaduan</div>
                <div class="card-value"><?= $total_complaints ?></div>
            </div>
        </div>
        <div class="card pending">
            <div class="icon"><i class="fas fa-hourglass-start"></i></div>
            <div>
                <div class="card-title">Pending</div>
                <div class="card-value"><?= $counts['pending'] ?></div>
            </div>
        </div>
        <div class="card process">
            <div class="icon"><i class="fas fa-tasks"></i></div>
            <div>
                <div class="card-title">Diproses</div>
                <div class="card-value"><?= $counts['process'] ?></div>
            </div>
        </div>
        <div class="card resolved">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="card-title">Selesai</div>
                <div class="card-value"><?= $counts['resolved'] ?></div>
            </div>
        </div>
    </div>

    <div class="table-container">
      <h2>Daftar Semua Pengaduan</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Siswa</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($complaints as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td><?= htmlspecialchars($c['full_name'] ?: $c['username']) ?> (<?= htmlspecialchars($c['kelas']) ?>)</td>
            <td><?= htmlspecialchars($c['category']) ?></td>
            <td><span class="status status-<?= htmlspecialchars($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
            <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
            <td>
              <a class="btn btn-detail" href="view_complaint.php?id=<?= $c['id'] ?>">Detail</a>
              <?php if($c['status'] !== 'resolved'): ?>
                <a class="btn btn-resolved" href="?action=resolved&id=<?= $c['id'] ?>">Selesai</a>
              <?php endif; ?>
              <a class="btn btn-delete" href="?action=delete&id=<?= $c['id'] ?>" onclick="return confirm('Yakin hapus laporan ini?')">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
  
</body>
</html>
