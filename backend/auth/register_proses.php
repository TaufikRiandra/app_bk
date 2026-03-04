<?php
session_start();
include '../config/database.php';

$username         = trim($_POST['username'] ?? '');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validasi
if (empty($username) || empty($password) || empty($password_confirm)) {
    $_SESSION['flash_error'] = 'Semua field harus diisi.';
    header('Location: ../../frontend/auth/register.php');
    exit;
}
if (strlen($username) < 3) {
    $_SESSION['flash_error'] = 'Username minimal 3 karakter.';
    header('Location: ../../frontend/auth/register.php');
    exit;
}
if (strlen($password) < 6) {
    $_SESSION['flash_error'] = 'Password minimal 6 karakter.';
    header('Location: ../../frontend/auth/register.php');
    exit;
}
if ($password !== $password_confirm) {
    $_SESSION['flash_error'] = 'Password tidak cocok.';
    header('Location: ../../frontend/auth/register.php');
    exit;
}

// Cek username unik
$chk = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($chk, 's', $username);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    $_SESSION['flash_error']  = 'Username sudah terdaftar.';
    $_SESSION['old_username'] = $username;
    header('Location: ../../frontend/auth/register.php');
    exit;
}

// Simpan akun baru: role = guru_bk, is_active = 0 (menunggu aktivasi admin)
$hashed = password_hash($password, PASSWORD_BCRYPT);
$role   = 'guru_bk';

$ins = mysqli_prepare($conn,
    'INSERT INTO users (username, password, role, is_active) VALUES (?, ?, ?, 0)'
);
mysqli_stmt_bind_param($ins, 'sss', $username, $hashed, $role);

if (mysqli_stmt_execute($ins)) {
    $_SESSION['flash_success'] = 'Registrasi berhasil! Akun Anda sedang menunggu aktivasi dari admin.';
    header('Location: ../../frontend/auth/login.php');
} else {
    $_SESSION['flash_error'] = 'Terjadi kesalahan: ' . mysqli_error($conn);
    header('Location: ../../frontend/auth/register.php');
}
exit;
