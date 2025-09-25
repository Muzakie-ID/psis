<?php
require_once 'config.php';
if (!is_logged_in()) {
    header("Location: index.php");
    exit;
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$complaint_id) {
    header("Location: dashboard.php");
    exit;
}

$user = current_user($pdo);

// Ambil detail pengaduan, PASTIKAN HANYA MILIK USER YANG LOGIN
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE id = ? AND user_id = ?");
$stmt->execute([$complaint_id, $user['id']]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    header("Location: dashboard.php");
    exit;
}

// Handle form balasan dari siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['response']))) {
    $response_text = trim($_POST['response']);
    
    $stmt = $pdo->prepare("INSERT INTO complaint_responses (complaint_id, user_id, response) VALUES (?, ?, ?)");
    $stmt->execute([$complaint_id, $user['id'], $response_text]);
    
    header("Location: view_complaint.php?id=" . $complaint_id);
    exit;
}

// Ambil semua tanggapan untuk pengaduan ini, join dengan tabel user untuk dapat role
$stmt = $pdo->prepare("SELECT r.*, u.full_name, u.role
                       FROM complaint_responses r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.complaint_id = ?
                       ORDER BY r.created_at ASC");
$stmt->execute([$complaint_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Pengaduan #<?= $complaint['id'] ?></title>
  <link rel="stylesheet" href="static/css/style.css">
  <style>
    body { background: #f5f7fa; }
    .container { max-width: 800px; margin: 30px auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    h1 { font-size: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
    .complaint-details p { margin: 0 0 12px; line-height: 1.6; color: #333; }
    .complaint-details strong { display: inline-block; width: 120px; color: #555; }
    .description-box { background: #f8f9fa; border: 1px solid #e9ecef; padding: 15px; border-radius: 8px; white-space: pre-wrap; margin-top: 10px; }
    .response-section { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
    .response-item { border-radius: 12px; padding: 12px 18px; margin-bottom: 12px; max-width: 80%; }
    .response-item p { margin: 0; }
    .response-item .meta { font-size: 0.8em; color: #6c757d; margin-bottom: 5px; font-weight: 600; }
    /* Styling untuk membedakan balasan admin dan siswa */
    .admin-response { background: #e7f5ff; border: 1px solid #b3d7f0; align-self: flex-start; }
    .student-response { background: #f0f0f0; border: 1px solid #ddd; align-self: flex-end; }
    .responses-container { display: flex; flex-direction: column; }
    .back-link { display: inline-block; margin-bottom: 20px; color: var(--primary); text-decoration: none; }
    .status { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white; }
    .status.pending { background-color: var(--warning); }
    .status.process { background-color: var(--primary); }
    .status.resolved { background-color: var(--success); }
    .response-form textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 10px; }
    .btn-primary { background: #007bff; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block; border: none; cursor: pointer; }
  </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="back-link">&laquo; Kembali ke Dashboard</a>
    <h1>Detail Pengaduan Anda</h1>

    <div class="complaint-details">
        <p><strong>Judul:</strong> <?= htmlspecialchars($complaint['title']) ?></p>
        <p><strong>Status:</strong> <span class="status <?= htmlspecialchars($complaint['status']) ?>"><?= ucfirst($complaint['status']) ?></span></p>
        <p><strong>Deskripsi Awal:</strong></p>
        <div class="description-box"><?= htmlspecialchars($complaint['description']) ?></div>
    </div>

    <div class="response-section">
        <h2>Percakapan</h2>
        <div class="responses-container">
            <?php if (empty($responses)): ?>
                <p>Belum ada tanggapan.</p>
            <?php else: ?>
                <?php foreach ($responses as $response): ?>
                    <?php 
                        $is_admin = ($response['role'] === 'admin');
                        $response_class = $is_admin ? 'admin-response' : 'student-response';
                        $align_class = $is_admin ? 'align-left' : 'align-right';
                        $author_name = $is_admin ? htmlspecialchars($response['full_name']) . ' (Admin)' : 'Anda';
                    ?>
                    <div class="response-item <?= $response_class ?>">
                        <div class="meta">
                            <span><?= $author_name ?></span>
                            <span style="float:right; font-weight:normal;"><?= date('d M Y, H:i', strtotime($response['created_at'])) ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($response['response'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="response-form" style="margin-top: 20px;">
            <hr>
            <h3 style="margin-top:20px;">Kirim Balasan</h3>
            <form method="post">
                <textarea name="response" rows="4" placeholder="Tulis balasan Anda di sini..." required></textarea>
                <button type="submit" class="btn-primary">Kirim</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
