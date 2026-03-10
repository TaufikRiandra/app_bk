<?php
header('Content-Type: application/json; charset=utf-8');

session_start();
if(!isset($_SESSION['login'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../config/database.php';

$kelas = $_GET['kelas'] ?? '';

if (!$kelas) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kelas tidak valid']);
    exit;
}

$kelas_escaped = mysqli_real_escape_string($conn, $kelas);

// Get siswa by kelas with all fields
$query = "SELECT id_siswa, nis, nama_siswa, jk, tempat_lahir, tgl_lahir, alamat, agama, 
                 sekolah_asal, no_hp, nama_ortu, no_hp_ortu
          FROM siswa 
          WHERE kelas = '$kelas_escaped' 
          ORDER BY nis";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

$siswa_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $siswa_list[] = $row;
}

echo json_encode([
    'success' => true,
    'siswa' => $siswa_list,
    'count' => count($siswa_list)
]);

mysqli_close($conn);
?>
