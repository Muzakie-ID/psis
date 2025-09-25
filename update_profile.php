<?php
require_once 'config.php';
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$user = current_user($pdo);
$user_id = $user['id'];
$_SESSION['flash_type'] = 'error'; // Default message type

// Logika untuk memperbarui profil
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $kelas = trim($_POST['kelas']);
    $nisn = trim($_POST['nisn']);

    if (empty($full_name) || empty($kelas)) {
        $_SESSION['flash_message'] = 'Nama lengkap dan kelas tidak boleh kosong.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, kelas = ?, nisn = ? WHERE id = ?");
        if ($stmt->execute([$full_name, $kelas, $nisn, $user_id])) {
            $_SESSION['flash_type'] = 'success';
            $_SESSION['flash_message'] = 'Profil berhasil diperbarui.';
        } else {
            $_SESSION['flash_message'] = 'Gagal memperbarui profil.';
        }
    }
}

// Logika untuk mengganti password
if (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validasi input
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['flash_message'] = 'Semua field password wajib diisi.';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['flash_message'] = 'Password baru dan konfirmasi tidak cocok.';
    } elseif (strlen($new_password) < 8) {
        $_SESSION['flash_message'] = 'Password baru minimal harus 8 karakter.';
    } else {
        // 2. Verifikasi password lama
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $db_password_hash = $stmt->fetchColumn();

        if (password_verify($old_password, $db_password_hash)) {
            // 3. Update dengan password baru
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$new_password_hash, $user_id])) {
                $_SESSION['flash_type'] = 'success';
                $_SESSION['flash_message'] = 'Password berhasil diubah.';
            } else {
                $_SESSION['flash_message'] = 'Gagal mengubah password.';
            }
        } else {
            $_SESSION['flash_message'] = 'Password lama yang Anda masukkan salah.';
        }
    }
}

// Redirect kembali ke halaman profil di dashboard
header('Location: dashboard.php#profile');
exit;
