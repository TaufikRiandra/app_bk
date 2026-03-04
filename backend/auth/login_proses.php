<?php
session_start();
include '../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['flash_error'] = 'Username dan password wajib diisi.';
    header('Location: ../../frontend/auth/login.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['flash_error']    = 'Username atau password salah.';
    $_SESSION['old_username']   = $username;
    header('Location: ../../frontend/auth/login.php');
    exit;
}

// Cek apakah akun sudah diaktifkan admin
if (!$user['is_active']) {
    $_SESSION['flash_error']  = 'Akun Anda belum diaktifkan. Hubungi admin untuk aktivasi.';
    $_SESSION['old_username'] = $username;
    header('Location: ../../frontend/auth/login.php');
    exit;
}

// Ambil data guru_bk jika role bukan admin
$id_guru_bk = null;
if ($user['role'] === 'guru_bk') {
    $s2 = mysqli_prepare($conn, 'SELECT id_guru_bk, nama, foto FROM guru_bk WHERE id_user = ? LIMIT 1');
    mysqli_stmt_bind_param($s2, 'i', $user['id_user']);
    mysqli_stmt_execute($s2);
    $gb = mysqli_fetch_assoc(mysqli_stmt_get_result($s2));
    if ($gb) {
        $id_guru_bk              = $gb['id_guru_bk'];
        $_SESSION['nama_guru']   = $gb['nama'];
        $_SESSION['foto_profil'] = $gb['foto'];
    }
}

$_SESSION['login']      = true;
$_SESSION['id_user']    = $user['id_user'];
$_SESSION['username']   = $user['username'];
$_SESSION['role']       = $user['role'];
$_SESSION['id_guru_bk'] = $id_guru_bk;

header('Location: ../../frontend/dashboard.php');
exit;
