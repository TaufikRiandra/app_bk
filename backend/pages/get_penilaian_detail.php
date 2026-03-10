<?php
session_start();
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

include '../config/database.php';
include '../config/auth_helper.php';

$kelas = $_GET['kelas'] ?? '';

if (!$kelas) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Kelas tidak valid']));
}

// Blokir akses kelas yang tidak berwenang
if (!isAdmin() && !canAccessKelas($conn, $kelas)) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Akses ditolak']));
}

$kelas_escaped = mysqli_real_escape_string($conn, $kelas);

$query = "SELECT s.id_siswa, s.nis, s.nama_siswa,
                 COALESCE(p.scores, '[]') as scores,
                 COALESCE(p.jumlah_tugas, 5) as jumlah_tugas
          FROM siswa s
          LEFT JOIN penilaian p ON s.id_siswa = p.id_siswa
          WHERE s.kelas = '$kelas_escaped'
          ORDER BY s.nama_siswa ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

$siswa        = [];
$jumlah_tugas = 5;

while ($row = mysqli_fetch_assoc($result)) {
    $jumlah_tugas = max($jumlah_tugas, intval($row['jumlah_tugas']));
    $siswa[] = [
        'id_siswa'   => $row['id_siswa'],
        'nis'        => $row['nis'],
        'nama_siswa' => $row['nama_siswa'],
        'scores'     => json_decode($row['scores'], true) ?: []
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success'      => true,
    'kelas'        => $kelas,
    'jumlah_tugas' => $jumlah_tugas,
    'siswa'        => $siswa,
    'count'        => count($siswa)
]);

mysqli_close($conn);