<?php
// submit_complaint.php
require_once 'config.php';
if (!is_logged_in()) {
    header("Location: index.php");
    exit;
}

$user = current_user($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$category = $_POST['category'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$urgency = $_POST['urgency'] ?? 'low';

// handle attachment
$attachment_path = null;
if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    $file = $_FILES['attachment'];
    // simple validation: size < 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['flash_error'] = "File terlalu besar (maks 5MB).";
        header("Location: dashboard.php");
        exit;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_name = uniqid('att_') . '.' . $ext;
    $target = UPLOAD_DIR . $safe_name;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        $attachment_path = 'uploads/' . $safe_name;
    }
}

$stmt = $pdo->prepare("INSERT INTO complaints (user_id, category, title, description, attachment, urgency, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$stmt->execute([$user['id'], $category, $title, $description, $attachment_path, $urgency]);

// setelah berhasil, redirect ke dashboard dengan pesan (flash)
$_SESSION['flash_success'] = "Pengaduan berhasil dikirim.";
header("Location: dashboard.php");
exit;
