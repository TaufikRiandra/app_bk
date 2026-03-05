<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}
header('Content-Type: application/json');
include '../../config/database.php';

$id_user  = intval($_POST['id_user'] ?? 0);
$is_active = intval($_POST['is_active'] ?? 0); // 1 = aktifkan, 0 = nonaktifkan

if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// Jangan nonaktifkan akun admin sendiri
$self = mysqli_prepare($conn, 'SELECT role FROM users WHERE id_user = ?');
mysqli_stmt_bind_param($self, 'i', $id_user);
mysqli_stmt_execute($self);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($self));

if (!$u) {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    exit;
}
if ($u['role'] === 'admin' && !$is_active) {
    echo json_encode(['success' => false, 'message' => 'Tidak bisa menonaktifkan akun admin']);
    exit;
}

$stmt = mysqli_prepare($conn, 'UPDATE users SET is_active = ? WHERE id_user = ?');
mysqli_stmt_bind_param($stmt, 'ii', $is_active, $id_user);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success'   => true,
        'message'   => $is_active ? 'Akun berhasil diaktifkan' : 'Akun berhasil dinonaktifkan',
        'is_active' => $is_active,
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . mysqli_error($conn)]);
}
exit;
