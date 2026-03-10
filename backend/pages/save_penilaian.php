<?php
header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../config/database.php';
include '../config/auth_helper.php';

$id_siswa     = intval($_POST['id_siswa']     ?? 0);
$jumlah_tugas = intval($_POST['jumlah_tugas'] ?? 0);
$scores_json  = $_POST['scores'] ?? '[]';

if ($id_siswa <= 0 || $jumlah_tugas <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

if (!json_decode($scores_json, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format scores tidak valid']);
    exit;
}

// Validasi guru BK hanya bisa simpan nilai siswa di kelas miliknya
if (!isAdmin()) {
    $cek_siswa = mysqli_prepare($conn, 'SELECT kelas FROM siswa WHERE id_siswa = ? LIMIT 1');
    mysqli_stmt_bind_param($cek_siswa, 'i', $id_siswa);
    mysqli_stmt_execute($cek_siswa);
    $res_siswa = mysqli_stmt_get_result($cek_siswa);
    $data_siswa = mysqli_fetch_assoc($res_siswa);

    if (!$data_siswa || !canAccessKelas($conn, $data_siswa['kelas'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
        exit;
    }
}

$scores_json_esc = mysqli_real_escape_string($conn, $scores_json);

// Buat tabel jika belum ada
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'penilaian'");
if (mysqli_num_rows($table_check) == 0) {
    $create = "CREATE TABLE penilaian (
        id_penilaian INT AUTO_INCREMENT PRIMARY KEY,
        id_siswa INT NOT NULL,
        scores JSON,
        jumlah_tugas INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE
    )";
    if (!mysqli_query($conn, $create)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal membuat tabel penilaian']);
        exit;
    }
}

$check_res = mysqli_query($conn, "SELECT id_penilaian FROM penilaian WHERE id_siswa = $id_siswa");

if (mysqli_num_rows($check_res) > 0) {
    $query = "UPDATE penilaian 
              SET scores = '$scores_json_esc', jumlah_tugas = $jumlah_tugas, updated_at = CURRENT_TIMESTAMP
              WHERE id_siswa = $id_siswa";
} else {
    $query = "INSERT INTO penilaian (id_siswa, scores, jumlah_tugas, updated_at)
              VALUES ($id_siswa, '$scores_json_esc', $jumlah_tugas, CURRENT_TIMESTAMP)";
}

if (mysqli_query($conn, $query)) {
    echo json_encode(['success' => true, 'message' => 'Nilai siswa berhasil disimpan']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . mysqli_error($conn)]);
}

mysqli_close($conn);