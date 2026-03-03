<?php
header('Content-Type: application/json; charset=utf-8');
ob_clean();

session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['role'] ?? 'guru_bk';
$tanggal = $_GET['tanggal'] ?? '';
$id_guru_bk = $_GET['id_guru_bk'] ?? 'current';

// Validasi tanggal
if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit();
}

// Tentukan guru BK ID
if ($id_guru_bk === 'current' || $user_role === 'guru_bk') {
    // For guru_bk role, get first guru_bk record
    $query = "SELECT id_guru_bk FROM guru_bk LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $id_guru_bk = $row['id_guru_bk'];
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guru BK tidak ditemukan']);
        exit();
    }
} else if ($id_guru_bk === '' || $id_guru_bk === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pilih Guru BK terlebih dahulu']);
    exit();
} else {
    // Verify it's a valid integer for admin
    $id_guru_bk = intval($id_guru_bk);
    if ($id_guru_bk <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID Guru BK tidak valid']);
        exit();
    }
}

// Query kegiatan
$tanggal_escaped = mysqli_real_escape_string($conn, $tanggal);
$query = "SELECT * FROM kegiatan_harian 
          WHERE tanggal = '$tanggal_escaped' AND id_guru_bk = $id_guru_bk
          ORDER BY waktu_mulai ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

$kegiatan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kegiatan[] = $row;
}

echo json_encode([
    'success' => true,
    'kegiatan' => $kegiatan,
    'count' => count($kegiatan)
]);
