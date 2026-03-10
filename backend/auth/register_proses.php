<?php
session_start();
include '../config/database.php';

$nama     = trim($_POST['nama']            ?? '');
$nip      = trim($_POST['nip']             ?? '');
$no_telp  = trim($_POST['no_telp']         ?? '');
$username = trim($_POST['username']        ?? '');
$password = $_POST['password']             ?? '';
$confirm  = $_POST['password_confirm']     ?? '';

$_SESSION['old'] = compact('nama', 'nip', 'no_telp', 'username');

// Validasi
if (!$nama || !$nip || !$no_telp || !$username || !$password || !$confirm) {
    $_SESSION['flash_error'] = 'Semua field wajib diisi.';
    header('Location: ../../frontend/auth/register.php'); exit;
}
if (strlen($username) < 3) {
    $_SESSION['flash_error'] = 'Username minimal 3 karakter.';
    header('Location: ../../frontend/auth/register.php'); exit;
}
if (strlen($password) < 6) {
    $_SESSION['flash_error'] = 'Password minimal 6 karakter.';
    header('Location: ../../frontend/auth/register.php'); exit;
}
if ($password !== $confirm) {
    $_SESSION['flash_error'] = 'Konfirmasi password tidak cocok.';
    header('Location: ../../frontend/auth/register.php'); exit;
}
if (strlen(preg_replace('/\D/', '', $no_telp)) < 8) {
    $_SESSION['flash_error'] = 'Nomor telepon minimal 8 digit.';
    header('Location: ../../frontend/auth/register.php'); exit;
}

// Cek username duplikat
$cek = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($cek, 's', $username);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);
if (mysqli_stmt_num_rows($cek) > 0) {
    $_SESSION['flash_error'] = 'Username sudah digunakan, pilih username lain.';
    header('Location: ../../frontend/auth/register.php'); exit;
}

// Cek NIP duplikat
$cek_nip = mysqli_prepare($conn, 'SELECT id_guru_bk FROM guru_bk WHERE nip = ? LIMIT 1');
mysqli_stmt_bind_param($cek_nip, 's', $nip);
mysqli_stmt_execute($cek_nip);
mysqli_stmt_store_result($cek_nip);
if (mysqli_stmt_num_rows($cek_nip) > 0) {
    $_SESSION['flash_error'] = 'NIP sudah terdaftar.';
    header('Location: ../../frontend/auth/register.php'); exit;
}

// Transaksi: insert users + guru_bk sekaligus
mysqli_begin_transaction($conn);
try {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $ins_user = mysqli_prepare($conn,
        'INSERT INTO users (username, password, role, is_active) VALUES (?, ?, "guru_bk", 0)'
    );
    mysqli_stmt_bind_param($ins_user, 'ss', $username, $hash);
    if (!mysqli_stmt_execute($ins_user)) throw new Exception(mysqli_error($conn));

    $id_user = mysqli_insert_id($conn);

    $no_telp_val = $no_telp ?: null;
    $ins_guru = mysqli_prepare($conn,
        'INSERT INTO guru_bk (id_user, nip, nama, no_telp) VALUES (?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($ins_guru, 'isss', $id_user, $nip, $nama, $no_telp_val);
    if (!mysqli_stmt_execute($ins_guru)) throw new Exception(mysqli_error($conn));

    mysqli_commit($conn);
    unset($_SESSION['old']);
    $_SESSION['flash_success'] = 'Pendaftaran berhasil! Silakan tunggu aktivasi dari admin sebelum login.';
    header('Location: ../../frontend/auth/login.php');

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['flash_error'] = 'Gagal mendaftar: ' . $e->getMessage();
    header('Location: ../../frontend/auth/register.php');
}
exit;