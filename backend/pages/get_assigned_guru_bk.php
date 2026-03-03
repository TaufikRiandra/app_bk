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

$tanggal = $_GET['tanggal'] ?? '';

// Validasi tanggal
if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit();
}

// Query untuk cek guru BK mana yang sudah punya kegiatan untuk tanggal tertentu
$tanggal_escaped = mysqli_real_escape_string($conn, $tanggal);
$query = "SELECT DISTINCT kh.id_guru_bk, gb.nama, gb.nip 
          FROM kegiatan_harian kh
          JOIN guru_bk gb ON kh.id_guru_bk = gb.id_guru_bk
          WHERE kh.tanggal = '$tanggal_escaped'
          LIMIT 1";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

$assigned_guru_bk = null;
if ($row = mysqli_fetch_assoc($result)) {
    $assigned_guru_bk = $row;
}

echo json_encode([
    'success' => true,
    'assigned_guru_bk' => $assigned_guru_bk
]);
?>
