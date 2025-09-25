<?php
// config.php
session_start();

// ubah sesuai konfigurasi MySQL-mu
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sis');
define('DB_USER', 'root');
define('DB_PASS', ''); // isi password mysql jika ada

// folder upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("Koneksi DB gagal: " . $e->getMessage());
}

// simple helper
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user($pdo) {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT id, username, full_name, nisn, email, kelas FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
