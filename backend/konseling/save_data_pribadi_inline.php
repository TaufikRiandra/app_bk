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
$jk = trim($_POST['jk'] ?? '');
$tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
$tgl_lahir = trim($_POST['tgl_lahir'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$agama = trim($_POST['agama'] ?? '');
$sekolah_asal = trim($_POST['sekolah_asal'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$nama_ortu = trim($_POST['nama_ortu'] ?? '');
$no_hp_ortu = trim($_POST['no_hp_ortu'] ?? '');

if ($id_siswa <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID siswa tidak valid']);
    exit;
}

// Escape strings
$jk_esc = mysqli_real_escape_string($conn, $jk);
$tempat_lahir_esc = mysqli_real_escape_string($conn, $tempat_lahir);
$tgl_lahir_esc = mysqli_real_escape_string($conn, $tgl_lahir);
$alamat_esc = mysqli_real_escape_string($conn, $alamat);
$agama_esc = mysqli_real_escape_string($conn, $agama);
$sekolah_asal_esc = mysqli_real_escape_string($conn, $sekolah_asal);
$no_hp_esc = mysqli_real_escape_string($conn, $no_hp);
$nama_ortu_esc = mysqli_real_escape_string($conn, $nama_ortu);
$no_hp_ortu_esc = mysqli_real_escape_string($conn, $no_hp_ortu);

// Update siswa data
$query = "UPDATE siswa 
          SET jk = '$jk_esc',
              tempat_lahir = '$tempat_lahir_esc',
              tgl_lahir = " . ($tgl_lahir ? "'$tgl_lahir_esc'" : "NULL") . ",
              alamat = '$alamat_esc',
              agama = '$agama_esc',
              sekolah_asal = '$sekolah_asal_esc',
              no_hp = '$no_hp_esc',
              nama_ortu = '$nama_ortu_esc',
              no_hp_ortu = '$no_hp_ortu_esc'
          WHERE id_siswa = $id_siswa";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        'success' => true,
        'message' => 'Data pribadi siswa berhasil disimpan'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan data: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>
