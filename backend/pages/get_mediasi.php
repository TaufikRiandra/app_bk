<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['login'])){
    echo json_encode([]);
    exit;
}

$tanggal = mysqli_real_escape_string($conn, $_GET['tanggal'] ?? '');
$id_guru_bk = intval($_GET['id_guru_bk'] ?? 0);

if (!$tanggal || !$id_guru_bk) {
    echo json_encode([]);
    exit;
}

$query = "SELECT * FROM layanan_mediasi 
          WHERE tanggal = '$tanggal' AND id_guru_bk = $id_guru_bk
          ORDER BY tanggal DESC";

$result = mysqli_query($conn, $query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
