<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('Location: /frontend/dashboard.php');
    exit;
}
include '../../config/database.php';

$id_guru_bk = intval($_GET['id'] ?? 0);
if (!$id_guru_bk) {
    header('Location: /frontend/pages/guru_bk/index.php?error=' . urlencode('ID tidak valid'));
    exit;
}

// Ambil id_user dan foto untuk cleanup
$stmt = mysqli_prepare($conn,
    'SELECT gb.foto, gb.id_user FROM guru_bk gb WHERE gb.id_guru_bk = ?'
);
mysqli_stmt_bind_param($stmt, 'i', $id_guru_bk);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
    header('Location: /frontend/pages/guru_bk/index.php?error=' . urlencode('Guru BK tidak ditemukan'));
    exit;
}

// Hapus foto lama
if ($row['foto']) {
    $path = $_SERVER['DOCUMENT_ROOT'] . $row['foto'];
    if (file_exists($path)) unlink($path);
}

// Hapus user → ON DELETE CASCADE akan hapus guru_bk juga
$del = mysqli_prepare($conn, 'DELETE FROM users WHERE id_user = ?');
mysqli_stmt_bind_param($del, 'i', $row['id_user']);

if (mysqli_stmt_execute($del)) {
    header('Location: /frontend/pages/guru_bk/index.php?success=' . urlencode('Guru BK berhasil dihapus'));
} else {
    header('Location: /frontend/pages/guru_bk/index.php?error=' . urlencode('Gagal menghapus'));
}
exit;
