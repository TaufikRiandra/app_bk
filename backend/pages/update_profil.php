<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /frontend/auth/login.php');
    exit;
}
header('Content-Type: application/json');
include '../../../config/database.php';

$id_user    = intval($_SESSION['id_user'] ?? 0);
$id_guru_bk = intval($_SESSION['id_guru_bk'] ?? 0);
$action     = $_POST['action'] ?? '';

if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'Session tidak valid']);
    exit;
}

/* ── Ganti Password ── */
if ($action === 'password') {
    $current  = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new_pass) || empty($confirm)) {
        echo json_encode(['success' => false, 'message' => 'Semua field password harus diisi']);
        exit;
    }
    if ($new_pass !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Password baru tidak cocok']);
        exit;
    }
    if (strlen($new_pass) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        exit;
    }

    $stmt = mysqli_prepare($conn, 'SELECT password FROM users WHERE id_user = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user || !password_verify($current, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password saat ini salah']);
        exit;
    }

    $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
    $upd    = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE id_user = ?');
    mysqli_stmt_bind_param($upd, 'si', $hashed, $id_user);
    mysqli_stmt_execute($upd);

    echo json_encode(['success' => true, 'message' => 'Password berhasil diubah']);
    exit;
}

/* ── Ganti Foto Profil ── */
if ($action === 'foto') {
    if (!$id_guru_bk) {
        echo json_encode(['success' => false, 'message' => 'Profil guru BK tidak ditemukan']);
        exit;
    }

    if (!isset($_FILES['foto']) || $_FILES['foto']['size'] === 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada file foto']);
        exit;
    }

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
        echo json_encode(['success' => false, 'message' => 'Format tidak didukung (jpg/png/gif)']);
        exit;
    }
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Ukuran foto maksimal 5MB']);
        exit;
    }

    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/uploads/guru_bk/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    // Hapus foto lama
    $old_stmt = mysqli_prepare($conn, 'SELECT foto FROM guru_bk WHERE id_guru_bk = ?');
    mysqli_stmt_bind_param($old_stmt, 'i', $id_guru_bk);
    mysqli_stmt_execute($old_stmt);
    $old_row = mysqli_fetch_assoc(mysqli_stmt_get_result($old_stmt));
    if ($old_row && $old_row['foto']) {
        $old_path = $_SERVER['DOCUMENT_ROOT'] . $old_row['foto'];
        if (file_exists($old_path)) unlink($old_path);
    }

    $filename = 'guru_bk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $filename)) {
        echo json_encode(['success' => false, 'message' => 'Gagal upload foto']);
        exit;
    }

    $foto_path = '/frontend/assets/uploads/guru_bk/' . $filename;
    $upd = mysqli_prepare($conn, 'UPDATE guru_bk SET foto = ? WHERE id_guru_bk = ?');
    mysqli_stmt_bind_param($upd, 'si', $foto_path, $id_guru_bk);
    mysqli_stmt_execute($upd);

    // Update session
    $_SESSION['foto_profil'] = $foto_path;

    echo json_encode([
        'success'   => true,
        'message'   => 'Foto berhasil diubah',
        'foto_url'  => $foto_path,
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action tidak dikenali']);
exit;
