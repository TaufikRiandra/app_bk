<?php
session_start();
include "../config/database.php";

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validasi
if (empty($username) || empty($password) || empty($password_confirm)) {
    $_SESSION['flash_error'] = 'Semua field harus diisi.';
    header("Location: ../../frontend/auth/register.php");
    exit;
}

if (strlen($username) < 3) {
    $_SESSION['flash_error'] = 'Username minimal 3 karakter.';
    header("Location: ../../frontend/auth/register.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['flash_error'] = 'Password minimal 6 karakter.';
    header("Location: ../../frontend/auth/register.php");
    exit;
}

if ($password !== $password_confirm) {
    $_SESSION['flash_error'] = 'Password tidak cocok.';
    header("Location: ../../frontend/auth/register.php");
    exit;
}

// Cek username sudah ada
$check = mysqli_query($conn, "SELECT id_user FROM users WHERE username='$username'");
if (mysqli_num_rows($check) > 0) {
    $_SESSION['flash_error'] = 'Username sudah terdaftar.';
    $_SESSION['old_username'] = $username;
    header("Location: ../../frontend/auth/register.php");
    exit;
}

// Hash password
$hashed_password = md5($password);

// Insert user baru
$insert = mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'admin')");

if ($insert) {
    $_SESSION['flash_success'] = 'Registrasi berhasil! Silakan login.';
    header("Location: ../../frontend/auth/login.php");
} else {
    $_SESSION['flash_error'] = 'Terjadi kesalahan: ' . mysqli_error($conn);
    header("Location: ../../frontend/auth/register.php");
}
?>
