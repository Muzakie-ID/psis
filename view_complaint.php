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

// Jika pengaduan tidak ditemukan atau bukan milik user, redirect ke dashboard
if (!$complaint) {
    header("Location: dashboard.php");
    exit;
}

// Ambil semua tanggapan untuk pengaduan ini
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
    .attachment a { color: var(--primary); text-decoration: none; }
    .attachment a:hover { text-decoration: underline; }
    .response-section { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
    .response-item { border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
    .response-item.admin-response { background: #f0f7ff; border-color: #cce5ff; }
    .response-item .meta { font-size: 0.9em; color: #6c757d; margin-bottom: 8px; font-weight: 600; }
    .back-link { display: inline-block; margin-bottom: 20px; color: var(--primary); text-decoration: none; }
    .back-link:hover { text-decoration: underline; }
    .status { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white; }
    .status.pending { background-color: var(--warning); }
    .status.process { background-color: var(--primary); }
    .status.resolved { background-color: var(--success); }
  </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="back-link">&laquo; Kembali ke Dashboard</a>
    <h1>Detail Pengaduan Anda</h1>

    <div class="complaint-details">
        <p><strong>Nomor Laporan:</strong> #<?= htmlspecialchars($complaint['id']) ?></p>
        <p><strong>Judul:</strong> <?= htmlspecialchars($complaint['title']) ?></p>
        <p><strong>Kategori:</strong> <?= htmlspecialchars(ucfirst($complaint['category'])) ?></p>
        <p><strong>Tanggal Diajukan:</strong> <?= date('d M Y, H:i', strtotime($complaint['created_at'])) ?></p>
        <p><strong>Status:</strong> <span class="status <?= htmlspecialchars($complaint['status']) ?>"><?= ucfirst($complaint['status']) ?></span></p>
        
        <?php if($complaint['attachment']): ?>
        <p class="attachment"><strong>Lampiran:</strong> <a href="<?= htmlspecialchars($complaint['attachment']) ?>" target="_blank">Lihat Lampiran</a></p>
        <?php endif; ?>

        <p><strong>Deskripsi Anda:</strong></p>
        <div class="description-box"><?= htmlspecialchars($complaint['description']) ?></div>
    </div>

    <div class="response-section">
        <h2>Riwayat Tanggapan</h2>
        <?php if (empty($responses)): ?>
            <p>Belum ada tanggapan dari admin untuk pengaduan ini.</p>
        <?php else: ?>
            <?php foreach ($responses as $response): ?>
                <div class="response-item admin-response">
                    <div class="meta">
                        <span>Admin (<?= htmlspecialchars($response['admin_name']) ?>)</span>
                        <span style="float:right;"><?= date('d M Y, H:i', strtotime($response['created_at'])) ?></span>
                    </div>
                    <p><?= nl2br(htmlspecialchars($response['response'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
