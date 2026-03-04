<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('Location: /frontend/dashboard.php');
    exit;
}
include '../../../config/database.php';

$username  = trim($_POST['username'] ?? '');
$password  = trim($_POST['password'] ?? '');
$nip       = trim($_POST['nip'] ?? '');
$nama      = trim($_POST['nama'] ?? '');
$no_telp   = trim($_POST['no_telp'] ?? '');
$foto_path = '';

$back = '/frontend/pages/guru_bk/tambah.php';

// Validasi
if (empty($username) || empty($password) || empty($nip) || empty($nama)) {
    header("Location: $back?error=" . urlencode('Semua field wajib diisi'));
    exit;
}
if (strlen($username) < 3) {
    header("Location: $back?error=" . urlencode('Username minimal 3 karakter'));
    exit;
}
if (strlen($password) < 6) {
    header("Location: $back?error=" . urlencode('Password minimal 6 karakter'));
    exit;
}

// Cek username unik
$chk = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($chk, 's', $username);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    header("Location: $back?error=" . urlencode('Username sudah terdaftar'));
    exit;
}

// Cek NIP unik
$chk2 = mysqli_prepare($conn, 'SELECT id_guru_bk FROM guru_bk WHERE nip = ? LIMIT 1');
mysqli_stmt_bind_param($chk2, 's', $nip);
mysqli_stmt_execute($chk2);
mysqli_stmt_store_result($chk2);
if (mysqli_stmt_num_rows($chk2) > 0) {
    header("Location: $back?error=" . urlencode('NIP sudah terdaftar'));
    exit;
}

// Upload foto
if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/uploads/guru_bk/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
        header("Location: $back?error=" . urlencode('Format foto tidak didukung'));
        exit;
    }
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
        header("Location: $back?error=" . urlencode('Ukuran foto maksimal 5MB'));
        exit;
    }
    $filename = 'guru_bk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $filename)) {
        $foto_path = '/frontend/assets/uploads/guru_bk/' . $filename;
    } else {
        header("Location: $back?error=" . urlencode('Gagal upload foto'));
        exit;
    }
}

// Transaksi: insert users + guru_bk sekaligus
mysqli_begin_transaction($conn);
try {
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // 1. Buat akun user (is_active = 1 karena dibuat oleh admin langsung)
    $ins_user = mysqli_prepare($conn,
        'INSERT INTO users (username, password, role, is_active) VALUES (?, ?, "guru_bk", 1)'
    );
    mysqli_stmt_bind_param($ins_user, 'ss', $username, $hashed);
    mysqli_stmt_execute($ins_user);
    $id_user = mysqli_insert_id($conn);

    // 2. Buat profil guru_bk
    $ins_gb = mysqli_prepare($conn,
        'INSERT INTO guru_bk (id_user, nip, nama, no_telp, foto) VALUES (?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($ins_gb, 'issss', $id_user, $nip, $nama, $no_telp, $foto_path);
    mysqli_stmt_execute($ins_gb);

    mysqli_commit($conn);
    header('Location: /frontend/pages/guru_bk/index.php?success=' . urlencode('Guru BK berhasil ditambahkan'));
} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: $back?error=" . urlencode('Gagal menyimpan data: ' . $e->getMessage()));
}
exit;
