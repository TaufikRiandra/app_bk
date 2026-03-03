<?php
header('Content-Type: application/json; charset=utf-8');

session_start();
if(!isset($_SESSION['login'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../config/database.php';

$id_siswa = intval($_POST['id_siswa'] ?? 0);
$jumlah_tugas = intval($_POST['jumlah_tugas'] ?? 0);
$scores_json = $_POST['scores'] ?? '[]';

if ($id_siswa <= 0 || $jumlah_tugas <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Validate JSON
if (!json_decode($scores_json, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format scores tidak valid']);
    exit;
}

$scores_json_esc = mysqli_real_escape_string($conn, $scores_json);

// Check if penilaian table exists
$table_check = "SHOW TABLES LIKE 'penilaian'";
$check_result = mysqli_query($conn, $table_check);

if (mysqli_num_rows($check_result) == 0) {
    // Create penilaian table
    $create_table = "CREATE TABLE penilaian (
        id_penilaian INT AUTO_INCREMENT PRIMARY KEY,
        id_siswa INT NOT NULL,
        scores JSON,
        jumlah_tugas INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $create_table)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal membuat tabel penilaian']);
        exit;
    }
}

// Check if student already has penilaian record
$check_query = "SELECT id_penilaian FROM penilaian WHERE id_siswa = $id_siswa";
$check_res = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_res) > 0) {
    // Update existing record
    $query = "UPDATE penilaian 
              SET scores = '$scores_json_esc',
                  jumlah_tugas = $jumlah_tugas,
                  updated_at = CURRENT_TIMESTAMP
              WHERE id_siswa = $id_siswa";
} else {
    // Insert new record
    $query = "INSERT INTO penilaian (id_siswa, scores, jumlah_tugas, updated_at)
              VALUES ($id_siswa, '$scores_json_esc', $jumlah_tugas, CURRENT_TIMESTAMP)";
}

if (mysqli_query($conn, $query)) {
    echo json_encode([
        'success' => true,
        'message' => 'Nilai siswa berhasil disimpan'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan nilai: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>
