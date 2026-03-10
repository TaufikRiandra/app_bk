<?php
session_start();

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

include '../config/database.php';
include '../config/auth_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$kelas = trim($_POST['kelas'] ?? '');

if (empty($kelas)) {
    echo json_encode(['success' => false, 'message' => 'Kelas tidak boleh kosong']);
    exit;
}

// Blokir hapus kelas yang tidak berwenang
if (!isAdmin() && !canAccessKelas($conn, $kelas)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$stmt = mysqli_prepare($conn,
    "DELETE p FROM penilaian p
     INNER JOIN siswa s ON s.id_siswa = p.id_siswa
     WHERE s.kelas = ?"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $kelas);

if (mysqli_stmt_execute($stmt)) {
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected === 0) {
        echo json_encode(['success' => false, 'message' => "Tidak ada data penilaian untuk kelas $kelas"]);
    } else {
        echo json_encode([
            'success'  => true,
            'message'  => "Berhasil menghapus penilaian kelas $kelas ($affected siswa)",
            'affected' => $affected,
            'kelas'    => $kelas
        ]);
    }
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . mysqli_error($conn)]);
}