<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('Location: /frontend/dashboard.php');
    exit;
}
include '../../config/database.php';

$id_guru_bk = intval($_POST['id_guru_bk'] ?? 0);
$nip        = trim($_POST['nip'] ?? '');
$nama       = trim($_POST['nama'] ?? '');
$no_telp    = trim($_POST['no_telp'] ?? '');
$new_pass   = trim($_POST['new_password'] ?? '');

$back = "/frontend/pages/guru_bk/edit.php?id=$id_guru_bk";

if (!$id_guru_bk || empty($nip) || empty($nama)) {
    header("Location: $back&error=" . urlencode('Data tidak valid'));
    exit;
}

// Ambil data saat ini
$cur = mysqli_prepare($conn, 'SELECT gb.*, u.id_user FROM guru_bk gb JOIN users u ON u.id_user = gb.id_user WHERE gb.id_guru_bk = ?');
mysqli_stmt_bind_param($cur, 'i', $id_guru_bk);
mysqli_stmt_execute($cur);
$guru_bk = mysqli_fetch_assoc(mysqli_stmt_get_result($cur));

if (!$guru_bk) {
    header('Location: /frontend/pages/guru_bk/index.php?error=' . urlencode('Guru BK tidak ditemukan'));
    exit;
}

// Cek NIP unik (kecuali diri sendiri)
$chk = mysqli_prepare($conn, 'SELECT id_guru_bk FROM guru_bk WHERE nip = ? AND id_guru_bk != ?');
mysqli_stmt_bind_param($chk, 'si', $nip, $id_guru_bk);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    header("Location: $back&error=" . urlencode('NIP sudah digunakan'));
    exit;
}

$foto = $guru_bk['foto'];

// Upload foto baru
if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/uploads/guru_bk/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
        header("Location: $back&error=" . urlencode('Format foto tidak didukung'));
        exit;
    }
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
        header("Location: $back&error=" . urlencode('Ukuran foto maksimal 5MB'));
        exit;
    }

    // Hapus foto lama
    if ($guru_bk['foto']) {
        $old = $_SERVER['DOCUMENT_ROOT'] . $guru_bk['foto'];
        if (file_exists($old)) unlink($old);
    }

    $filename = 'guru_bk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $filename)) {
        $foto = '/frontend/assets/uploads/guru_bk/' . $filename;
    }
}

mysqli_begin_transaction($conn);
try {
    // Update guru_bk
    $upd = mysqli_prepare($conn,
        'UPDATE guru_bk SET nip=?, nama=?, no_telp=?, foto=? WHERE id_guru_bk=?'
    );
    mysqli_stmt_bind_param($upd, 'ssssi', $nip, $nama, $no_telp, $foto, $id_guru_bk);
    mysqli_stmt_execute($upd);

    // Ganti password jika diisi
    if (!empty($new_pass)) {
        if (strlen($new_pass) < 6) throw new Exception('Password baru minimal 6 karakter');
        $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $upd_pw = mysqli_prepare($conn, 'UPDATE users SET password=? WHERE id_user=?');
        mysqli_stmt_bind_param($upd_pw, 'si', $hashed, $guru_bk['id_user']);
        mysqli_stmt_execute($upd_pw);
    }

    mysqli_commit($conn);
    header('Location: /frontend/pages/guru_bk/index.php?success=' . urlencode('Guru BK berhasil diperbarui'));
} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: $back&error=" . urlencode($e->getMessage()));
}
exit;
