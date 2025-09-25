<?php
require_once '../config.php';
if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$user = current_user($pdo);

// update status laporan
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

// ambil semua laporan
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
  <title>Admin Dashboard - Pengaduan Siswa</title>
  <link rel="stylesheet" href="../static/css/style.css">
  <style>
    body{padding:20px;background:#f8f9fa;font-family:sans-serif}
    h1{margin-bottom:20px}
    table{width:100%;border-collapse:collapse;background:white}
    th,td{border:1px solid #ddd;padding:8px;text-align:left}
    th{background:#f1f1f1}
    .status-pending{color:#b36b00;font-weight:600}
    .status-process{color:#0057b3;font-weight:600}
    .status-resolved{color:#2a8a2a;font-weight:600}
    a.btn{padding:5px 10px;border-radius:5px;text-decoration:none;margin-right:5px;font-size:0.9em; display: inline-block; margin-bottom: 5px;}
    .btn-detail{background:#6c757d; color:white;}
    .btn-process{background:#007bff;color:white}
    .btn-resolved{background:#28a745;color:white}
    .btn-delete{background:#dc3545;color:white}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px}
    .topbar a{color:#007bff;text-decoration:none;font-size:0.9em}
  </style>
</head>
<body>
  <div class="topbar">
    <h1>Admin Dashboard</h1>
    <div>
      <span>Halo, <?= htmlspecialchars($user['full_name'] ?: "Admin") ?> (Admin)</span> | 
      <a href="../dashboard.php">User Dashboard</a> | 
      <a href="../logout.php">Logout</a>
    </div>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Judul</th>
      <th>Kategori</th>
      <th>Siswa</th>
      <th>Lampiran</th>
      <th>Status</th>
      <th>Tanggal</th>
      <th>Aksi</th>
    </tr>
    <?php foreach($complaints as $c): ?>
    <tr>
      <td><?= $c['id'] ?></td>
      <td><?= htmlspecialchars($c['title']) ?></td>
      <td><?= htmlspecialchars($c['category']) ?></td>
      <td><?= htmlspecialchars($c['full_name'] ?: $c['username']) ?> (<?= htmlspecialchars($c['kelas']) ?>)</td>
      <td>
        <?php if($c['attachment']): ?>
          <a href="../<?= htmlspecialchars($c['attachment']) ?>" target="_blank">Lihat</a>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
      <td class="status-<?= htmlspecialchars($c['status']) ?>">
        <?= ucfirst($c['status']) ?>
      </td>
      <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
      <td>
        <a class="btn btn-detail" href="view_complaint.php?id=<?= $c['id'] ?>">Detail/Balas</a>
        <?php if($c['status'] === 'pending'): ?>
          <a class="btn btn-process" href="?action=process&id=<?= $c['id'] ?>">Proses</a>
        <?php endif; ?>
        <?php if($c['status'] !== 'resolved'): ?>
          <a class="btn btn-resolved" href="?action=resolved&id=<?= $c['id'] ?>">Selesai</a>
        <?php endif; ?>
        <a class="btn btn-delete" href="?action=delete&id=<?= $c['id'] ?>" onclick="return confirm('Yakin hapus laporan ini?')">Hapus</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
