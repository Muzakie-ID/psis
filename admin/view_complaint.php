<?php
require_once '../config.php';
if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$complaint_id) {
    header("Location: index.php");
    exit;
}

$admin_user = current_user($pdo);

// Handle form submission for new response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['response']))) {
    $response_text = trim($_POST['response']);
    
    $stmt = $pdo->prepare("INSERT INTO complaint_responses (complaint_id, admin_id, response) VALUES (?, ?, ?)");
    $stmt->execute([$complaint_id, $admin_user['id'], $response_text]);
    
    // Optional: Update complaint status to 'process' when admin responds
    $stmt = $pdo->prepare("UPDATE complaints SET status = 'process', updated_at = NOW() WHERE id = ? AND status = 'pending'");
    $stmt->execute([$complaint_id]);

    header("Location: view_complaint.php?id=" . $complaint_id);
    exit;
}


// Fetch complaint details with user info
$stmt = $pdo->prepare("SELECT c.*, u.full_name, u.username, u.kelas, u.nisn
                       FROM complaints c 
                       JOIN users u ON c.user_id = u.id 
                       WHERE c.id = ?");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    die("Pengaduan tidak ditemukan.");
}

// Fetch responses for this complaint
$stmt = $pdo->prepare("SELECT r.*, u.full_name as admin_name
                       FROM complaint_responses r
                       JOIN users u ON r.admin_id = u.id
                       WHERE r.complaint_id = ?
                       ORDER BY r.created_at ASC");
$stmt->execute([$complaint_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Detail Pengaduan #<?= $complaint['id'] ?></title>
  <link rel="stylesheet" href="../static/css/style.css">
  <style>
    body { padding: 20px; background: #f8f9fa; font-family: sans-serif; }
    .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
    .complaint-details p { margin: 0 0 10px; line-height: 1.6; }
    .complaint-details strong { display: inline-block; width: 120px; color: #555; }
    .attachment a { color: #007bff; }
    .response-section { margin-top: 30px; }
    .response-item { border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; }
    .response-item .meta { font-size: 0.9em; color: #6c757d; margin-bottom: 8px; }
    .response-form textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 10px; }
    .btn { padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block; border: none; cursor: pointer; }
    .btn-primary { background: #007bff; color: white; }
    .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .topbar a { color: #007bff; text-decoration: none; font-size: 0.9em; }
  </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1>Detail Pengaduan #<?= htmlspecialchars($complaint['id']) ?></h1>
        <a href="index.php">&laquo; Kembali ke Dashboard</a>
    </div>

    <div class="complaint-details">
        <p><strong>Judul:</strong> <?= htmlspecialchars($complaint['title']) ?></p>
        <p><strong>Dari Siswa:</strong> <?= htmlspecialchars($complaint['full_name'] ?: $complaint['username']) ?> (<?= htmlspecialchars($complaint['kelas']) ?>)</p>
        <p><strong>NISN:</strong> <?= htmlspecialchars($complaint['nisn'] ?: '-') ?></p>
        <p><strong>Kategori:</strong> <?= htmlspecialchars(ucfirst($complaint['category'])) ?></p>
        <p><strong>Tanggal:</strong> <?= date('d M Y, H:i', strtotime($complaint['created_at'])) ?></p>
        <p><strong>Status:</strong> <span class="status-<?= htmlspecialchars($complaint['status']) ?>"><?= ucfirst($complaint['status']) ?></span></p>
        <p><strong>Deskripsi:</strong></p>
        <div style="padding:15px; background:#f9f9f9; border-radius:5px; white-space: pre-wrap;"><?= htmlspecialchars($complaint['description']) ?></div>
        
        <?php if($complaint['attachment']): ?>
        <p class="attachment"><strong>Lampiran:</strong> <a href="../<?= htmlspecialchars($complaint['attachment']) ?>" target="_blank">Lihat Lampiran</a></p>
        <?php endif; ?>
    </div>

    <div class="response-section">
        <h2>Tanggapan</h2>
        <?php if (empty($responses)): ?>
            <p>Belum ada tanggapan untuk pengaduan ini.</p>
        <?php else: ?>
            <?php foreach ($responses as $response): ?>
                <div class="response-item">
                    <div class="meta">
                        <strong><?= htmlspecialchars($response['admin_name']) ?> (Admin)</strong> - 
                        <span><?= date('d M Y, H:i', strtotime($response['created_at'])) ?></span>
                    </div>
                    <p><?= nl2br(htmlspecialchars($response['response'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="response-form">
            <h3>Beri Tanggapan</h3>
            <form method="post">
                <textarea name="response" rows="4" placeholder="Tulis tanggapan Anda di sini..." required></textarea>
                <button type="submit" class="btn btn-primary">Kirim Tanggapan</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
