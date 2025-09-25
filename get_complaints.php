<?php
// get_complaints.php
require_once 'config.php';
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$user = current_user($pdo);
$stmt = $pdo->prepare("SELECT id, title, description, status, DATE_FORMAT(created_at, '%d %M %Y') as created_at FROM complaints WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$user['id']]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
exit;
