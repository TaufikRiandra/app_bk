<?php
header('Content-Type: application/json; charset=utf-8');
ob_clean();
session_start();

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include __DIR__ . '/../config/database.php';

$tanggal = $_GET['tanggal'] ?? '';
if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit;
}

$t = mysqli_real_escape_string($conn, $tanggal);

// Ambil dari tabel jadwal (persisten, ditetapkan admin)
$q = mysqli_query($conn,
    "SELECT j.id_guru_bk, j.tanggal,
            gb.nama, gb.nip
     FROM guru_bk_jadwal j
     LEFT JOIN guru_bk gb ON gb.id_guru_bk = j.id_guru_bk
     WHERE j.tanggal = '$t'
     LIMIT 1"
);

$assigned = null;
if ($q && $row = mysqli_fetch_assoc($q)) {
    // id_guru_bk NULL berarti admin set "tidak ada"
    if ($row['id_guru_bk'] !== null) {
        $assigned = [
            'id_guru_bk' => $row['id_guru_bk'],
            'nama'        => $row['nama'],
            'nip'         => $row['nip'],
        ];
    } else {
        // Jadwal ada tapi sengaja dikosongkan
        $assigned = 'none';
    }
}

echo json_encode([
    'success'          => true,
    'assigned_guru_bk' => $assigned, // null = belum ditetapkan, 'none' = sengaja tidak ada, array = guru terpilih
]);
