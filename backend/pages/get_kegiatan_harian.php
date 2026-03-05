<?php
header('Content-Type: application/json; charset=utf-8');
ob_clean();
session_start();
include __DIR__ . '/../config/database.php';

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_role  = $_SESSION['role'] ?? 'guru_bk';
$tanggal    = $_GET['tanggal'] ?? '';
$id_guru_bk = $_GET['id_guru_bk'] ?? '';

if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit;
}

$t = mysqli_real_escape_string($conn, $tanggal);

if ($user_role === 'guru_bk') {
    // Ambil id_guru_bk dari session (diisi saat login)
    $session_id_guru_bk = intval($_SESSION['id_guru_bk'] ?? 0);
    if (!$session_id_guru_bk) {
        echo json_encode(['success' => true, 'kegiatan' => [], 'count' => 0,
                          'message' => 'Profil guru BK belum terhubung ke akun ini']);
        exit;
    }
    $id_guru_bk = $session_id_guru_bk;

    // Cek apakah guru ini ditetapkan untuk tanggal ini
    $jadwal_q = mysqli_query($conn,
        "SELECT id_guru_bk FROM guru_bk_jadwal WHERE tanggal = '$t' LIMIT 1"
    );
    $jadwal = $jadwal_q ? mysqli_fetch_assoc($jadwal_q) : null;
    $is_assigned = $jadwal && intval($jadwal['id_guru_bk']) === $id_guru_bk;

} else {
    // Admin: pakai id_guru_bk dari parameter
    $id_guru_bk = intval($id_guru_bk);
    if ($id_guru_bk <= 0) {
        echo json_encode(['success' => true, 'kegiatan' => [], 'count' => 0]);
        exit;
    }
    $is_assigned = true; // admin selalu bisa lihat
}

$q = mysqli_query($conn,
    "SELECT * FROM kegiatan_harian
     WHERE tanggal = '$t' AND id_guru_bk = $id_guru_bk
     ORDER BY waktu_mulai ASC"
);

if (!$q) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

$kegiatan = [];
while ($row = mysqli_fetch_assoc($q)) {
    $kegiatan[] = $row;
}

echo json_encode([
    'success'     => true,
    'kegiatan'    => $kegiatan,
    'count'       => count($kegiatan),
    'is_assigned' => $is_assigned, // apakah guru ini yg ditetapkan untuk tgl ini
    'id_guru_bk'  => $id_guru_bk,
]);
