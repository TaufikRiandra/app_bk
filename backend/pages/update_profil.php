<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sesi habis, silakan login ulang']);
    exit;
}
header('Content-Type: application/json');

// Path database: backend/pages/ → backend/config/
include __DIR__ . '../../config/database.php';

$id_user    = intval($_SESSION['id_user'] ?? 0);
$id_guru_bk = intval($_SESSION['id_guru_bk'] ?? 0);
$username   = $_SESSION['username'] ?? '';
$action     = $_POST['action'] ?? '';

// Fallback: cari id_user dari username jika session lama tidak punya id_user
if (!$id_user && $username) {
    $r = mysqli_query($conn, "SELECT id_user FROM users WHERE username='" . mysqli_real_escape_string($conn, $username) . "' LIMIT 1");
    if ($r && $row = mysqli_fetch_assoc($r)) {
        $id_user = intval($row['id_user']);
        $_SESSION['id_user'] = $id_user; // simpan ke session supaya tidak perlu query lagi
    }
}

if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'Session tidak valid, silakan login ulang']);
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

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        exit;
    }

    // Dukung password lama md5 (migrasi bertahap)
    $valid = password_verify($current, $user['password'])
          || (strlen($user['password']) === 32 && md5($current) === $user['password']);

    if (!$valid) {
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
    // Fallback: cari id_guru_bk dari id_user jika belum ada di session
    if (!$id_guru_bk && $id_user) {
        $r2 = mysqli_prepare($conn, 'SELECT id_guru_bk FROM guru_bk WHERE id_user = ? LIMIT 1');
        mysqli_stmt_bind_param($r2, 'i', $id_user);
        mysqli_stmt_execute($r2);
        $g = mysqli_fetch_assoc(mysqli_stmt_get_result($r2));
        if ($g) {
            $id_guru_bk = intval($g['id_guru_bk']);
            $_SESSION['id_guru_bk'] = $id_guru_bk;
        }
    }

    if (!$id_guru_bk) {
        echo json_encode(['success' => false, 'message' => 'Data guru BK tidak ditemukan untuk akun ini']);
        exit;
    }

    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $err = $_FILES['foto']['error'] ?? -1;
        echo json_encode(['success' => false, 'message' => "File upload error (kode: $err)"]);
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

    // Upload dir: coba DOCUMENT_ROOT dulu, fallback ke path relatif
    $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
    $upload_dir = $doc_root . '/frontend/assets/uploads/guru_bk/';

    // Fallback jika DOCUMENT_ROOT tidak sesuai struktur project (Laragon)
    if (!is_dir($doc_root . '/frontend')) {
        // Cari dari __FILE__: backend/pages/ → naik 3 → root project
        $project_root = dirname(dirname(dirname(__FILE__)));
        $upload_dir   = $project_root . '/frontend/assets/uploads/guru_bk/';
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Hapus foto lama
    $old_stmt = mysqli_prepare($conn, 'SELECT foto FROM guru_bk WHERE id_guru_bk = ?');
    mysqli_stmt_bind_param($old_stmt, 'i', $id_guru_bk);
    mysqli_stmt_execute($old_stmt);
    $old_row = mysqli_fetch_assoc(mysqli_stmt_get_result($old_stmt));
    if ($old_row && $old_row['foto']) {
        $old_abs = $doc_root . $old_row['foto'];
        if (!file_exists($old_abs)) {
            // fallback path
            $old_abs = dirname(dirname(dirname(__FILE__))) . $old_row['foto'];
        }
        if (file_exists($old_abs)) unlink($old_abs);
    }

    $filename = 'guru_bk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $filename)) {
        echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file upload']);
        exit;
    }

    $foto_path = '/frontend/assets/uploads/guru_bk/' . $filename;
    $upd = mysqli_prepare($conn, 'UPDATE guru_bk SET foto = ? WHERE id_guru_bk = ?');
    mysqli_stmt_bind_param($upd, 'si', $foto_path, $id_guru_bk);
    mysqli_stmt_execute($upd);

    $_SESSION['foto_profil'] = $foto_path;

    echo json_encode([
        'success'  => true,
        'message'  => 'Foto berhasil diubah',
        'foto_url' => $foto_path,
    ]);
    exit;
}

/* ── Update No. Telepon ── */
if ($action === 'notelp') {
    if (!$id_guru_bk) {
        echo json_encode(['success' => false, 'message' => 'Hanya guru BK yang dapat mengubah no. telepon']);
        exit;
    }

    $no_telp = trim($_POST['no_telp'] ?? '');

    // Validasi: boleh kosong, tapi kalau diisi harus angka/tanda umum saja
    if ($no_telp !== '' && strlen(preg_replace('/\D/', '', $no_telp)) < 8) {
        echo json_encode(['success' => false, 'message' => 'Nomor telepon minimal 8 digit']);
        exit;
    }

    $upd = mysqli_prepare($conn, 'UPDATE guru_bk SET no_telp = ? WHERE id_guru_bk = ?');
    mysqli_stmt_bind_param($upd, 'si', $no_telp, $id_guru_bk);
    mysqli_stmt_execute($upd);

    echo json_encode(['success' => true, 'message' => 'No. telepon berhasil diperbarui']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action tidak dikenali']);
exit;
