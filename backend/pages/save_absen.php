<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../config/database.php";
require_once "../config/auth_helper.php";

// Harus login
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Tidak terautentikasi']);
    exit;
}

// Check if this is POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['kelas']) || !isset($input['absen']) || !isset($input['tanggal'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$kelas   = trim($input['kelas']);
$tanggal = trim($input['tanggal']);

// === KONTROL AKSES: Guru BK hanya boleh menyimpan absen kelas yang ditetapkan ===
if (!isAdmin()) {
    if (!canAccessKelas($conn, $kelas)) {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Anda tidak memiliki akses ke kelas ' . htmlspecialchars($kelas)
        ]);
        exit;
    }
}

$kelas       = mysqli_real_escape_string($conn, $kelas);
$tanggal     = mysqli_real_escape_string($conn, $tanggal);
$absen_list  = $input['absen'];

try {
    mysqli_begin_transaction($conn);

    // Delete existing absen for this kelas and tanggal
    $delete_query = "DELETE FROM absen_siswa
                     WHERE id_siswa IN (SELECT id_siswa FROM siswa WHERE kelas = '$kelas')
                     AND tanggal = '$tanggal'";
    if (!mysqli_query($conn, $delete_query)) {
        throw new Exception('Error deleting existing data: ' . mysqli_error($conn));
    }

    // Insert new absen data
    $keterangan_map = [
        'H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit',
        'A' => 'Alfa',  'C' => 'Cabut','T' => 'Terlambat'
    ];

    foreach ($absen_list as $absen) {
        $id_siswa       = intval($absen['id_siswa']);
        $keterangan     = mysqli_real_escape_string($conn, $absen['keterangan']);
        $keterangan_full = $keterangan_map[$keterangan] ?? $keterangan;

        // Verifikasi siswa benar-benar ada di kelas ini
        $cek_siswa = mysqli_query($conn,
            "SELECT id_siswa FROM siswa WHERE id_siswa = $id_siswa AND kelas = '$kelas' LIMIT 1");
        if (!$cek_siswa || mysqli_num_rows($cek_siswa) === 0) {
            throw new Exception("Siswa ID $id_siswa tidak ditemukan di kelas $kelas");
        }

        $insert_query = "INSERT INTO absen_siswa (id_siswa, tanggal, keterangan)
                         VALUES ($id_siswa, '$tanggal', '$keterangan_full')";
        if (!mysqli_query($conn, $insert_query)) {
            throw new Exception('Error inserting data: ' . mysqli_error($conn));
        }
    }

    mysqli_commit($conn);

    echo json_encode([
        'status'  => 'success',
        'message' => count($absen_list) . ' absensi berhasil disimpan untuk kelas ' . $kelas . ' pada tanggal ' . $tanggal
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>
