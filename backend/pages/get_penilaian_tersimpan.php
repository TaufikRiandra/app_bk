<?php
session_start();
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

include '../config/database.php';
include '../config/auth_helper.php';

// Admin: semua kelas. Guru BK: hanya kelas miliknya
if (isAdmin()) {
    $query = "SELECT DISTINCT 
                s.kelas,
                p.jumlah_tugas,
                COUNT(DISTINCT p.id_siswa) as jumlah_siswa,
                MAX(p.updated_at) as updated_at
              FROM penilaian p
              INNER JOIN siswa s ON p.id_siswa = s.id_siswa
              GROUP BY s.kelas, p.jumlah_tugas
              ORDER BY MAX(p.updated_at) DESC";
} else {
    $id_gbk = intval(getSessionGuruBkId());
    $query = "SELECT DISTINCT 
                s.kelas,
                p.jumlah_tugas,
                COUNT(DISTINCT p.id_siswa) as jumlah_siswa,
                MAX(p.updated_at) as updated_at
              FROM penilaian p
              INNER JOIN siswa s ON p.id_siswa = s.id_siswa
              INNER JOIN kelas k ON s.kelas = k.nama_kelas
              WHERE k.id_guru_bk = $id_gbk
              GROUP BY s.kelas, p.jumlah_tugas
              ORDER BY MAX(p.updated_at) DESC";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]));
}

$penilaian = [];
while ($row = mysqli_fetch_assoc($result)) {
    $penilaian[] = [
        'kelas'        => $row['kelas'],
        'jumlah_tugas' => $row['jumlah_tugas'],
        'jumlah_siswa' => $row['jumlah_siswa'],
        'updated_at'   => $row['updated_at']
    ];
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'penilaian' => $penilaian]);

mysqli_close($conn);