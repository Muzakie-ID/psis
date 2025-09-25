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

// Ambil detail pengaduan
$stmt = $pdo->prepare("SELECT c.*, u.full_name, u.kelas FROM complaints c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    die("Pengaduan tidak ditemukan.");
}

// Handle form balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['response']))) {
    $response_text = trim($_POST['response']);
    $stmt = $pdo->prepare("INSERT INTO complaint_responses (complaint_id, user_id, response) VALUES (?, ?, ?)");
    $stmt->execute([$complaint_id, $admin_user['id'], $response_text]);
    
    if ($complaint['status'] == 'pending') {
        $pdo->prepare("UPDATE complaints SET status = 'process' WHERE id = ?")->execute([$complaint_id]);
    }
    
    header("Location: view_complaint.php?id=" . $complaint_id . "#chat-end");
    exit;
}

// Ambil semua tanggapan
$stmt = $pdo->prepare("SELECT r.*, u.full_name, u.role FROM complaint_responses r JOIN users u ON r.user_id = u.id WHERE r.complaint_id = ? ORDER BY r.created_at ASC");
$stmt->execute([$complaint_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Pengaduan #<?= $complaint['id'] ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root { --primary: #4361ee; --light: #f8f9fa; --dark: #212529; --success: #4cc9f0; --warning: #f9c74f; }
    body { background-color: #f5f7fa; font-family: 'Segoe UI', sans-serif; margin: 0; }
    .chat-container { max-width: 800px; margin: 30px auto; background: white; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); display: flex; flex-direction: column; height: calc(100vh - 60px); }
    .chat-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .chat-header h1 { font-size: 1.2rem; margin: 0; }
    .chat-header .student-info { font-size: 0.9rem; color: #6c757d; }
    .status { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: white; }
    .status.pending { background-color: var(--warning); }
    .status.process { background-color: var(--primary); }
    .status.resolved { background-color: var(--success); color: var(--dark); }
    .chat-body { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
    .message { display: flex; flex-direction: column; max-width: 75%; }
    .message .bubble { padding: 12px 18px; border-radius: 20px; line-height: 1.5; }
    .message .meta { font-size: 0.75rem; color: #6c757d; padding: 0 10px; margin-top: 4px; }
    .initial-complaint { background: #fafafa; border: 1px solid #eee; padding: 15px; border-radius: 12px; margin-bottom: 10px; }
    .initial-complaint p { margin: 0; }
    /* Admin messages */
    .message.admin { align-self: flex-end; align-items: flex-end; }
    .message.admin .bubble { background-color: var(--primary); color: white; border-bottom-right-radius: 5px; }
    /* Student messages */
    .message.student { align-self: flex-start; align-items: flex-start; }
    .message.student .bubble { background-color: #e9ecef; color: var(--dark); border-bottom-left-radius: 5px; }
    .chat-footer { padding: 20px; border-top: 1px solid #eee; background: #f8f9fa; }
    .chat-footer form { display: flex; gap: 10px; }
    .chat-footer textarea { flex: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 25px; resize: none; }
    .chat-footer button { padding: 0 20px; border-radius: 25px; border: none; background: var(--primary); color: white; cursor: pointer; font-size: 1.2rem; }
    .back-link { color: var(--primary); text-decoration: none; font-size: 0.9rem; }
  </style>
</head>
<body>
<div class="chat-container">
    <header class="chat-header">
        <div>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali</a>
            <h1><?= htmlspecialchars($complaint['title']) ?></h1>
            <div class="student-info">Dari: <?= htmlspecialchars($complaint['full_name']) ?> (<?= htmlspecialchars($complaint['kelas']) ?>)</div>
        </div>
        <span class="status <?= htmlspecialchars($complaint['status']) ?>"><?= ucfirst($complaint['status']) ?></span>
    </header>

    <main class="chat-body">
        <div class="initial-complaint">
            <strong>Deskripsi Awal Siswa:</strong>
            <p><?= nl2br(htmlspecialchars($complaint['description'])) ?></p>
        </div>
        
        <?php foreach ($responses as $response): ?>
            <?php 
                $is_admin = ($response['role'] === 'admin');
                $message_class = $is_admin ? 'admin' : 'student';
                $author_name = htmlspecialchars($response['full_name']);
            ?>
            <div class="message <?= $message_class ?>">
                <div class="bubble">
                    <?= nl2br(htmlspecialchars($response['response'])) ?>
                </div>
                <div class="meta">
                    <?= $author_name ?> &bull; <?= date('H:i', strtotime($response['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div id="chat-end"></div>
    </main>

    <?php if ($complaint['status'] !== 'resolved'): ?>
    <footer class="chat-footer">
        <form method="post">
            <textarea name="response" rows="1" placeholder="Ketik balasan sebagai admin..." required></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </footer>
    <?php endif; ?>
</div>
<script>
    const chatBody = document.querySelector('.chat-body');
    chatBody.scrollTop = chatBody.scrollHeight;
</script>
</body>
</html>
