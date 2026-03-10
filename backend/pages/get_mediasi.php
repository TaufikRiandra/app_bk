<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login'])) {
    echo json_encode([]);
    exit;
}

$role       = $_SESSION['role'] ?? 'guru_bk';
$tanggal    = mysqli_real_escape_string($conn, $_GET['tanggal'] ?? date('Y-m-d'));
$id_guru_bk = intval($_GET['id_guru_bk'] ?? 0);

if (!$tanggal) {
    echo json_encode([]);
    exit;
}

if ($role === 'admin') {
    $query = "SELECT lm.*, gb.nama as guru_bk_nama
              FROM layanan_mediasi lm
              LEFT JOIN guru_bk gb ON lm.id_guru_bk = gb.id_guru_bk
              WHERE lm.tanggal = '$tanggal'
              ORDER BY lm.id_mediasi ASC";
} else {
    if (!$id_guru_bk) {
        echo json_encode([]);
        exit;
    }
    $query = "SELECT lm.*, gb.nama as guru_bk_nama
              FROM layanan_mediasi lm
              LEFT JOIN guru_bk gb ON lm.id_guru_bk = gb.id_guru_bk
              WHERE lm.tanggal = '$tanggal'
              AND lm.id_guru_bk = $id_guru_bk
              ORDER BY lm.id_mediasi ASC";
}

$result = mysqli_query($conn, $query);
$data   = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);