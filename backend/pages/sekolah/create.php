<?php
include "../config/database.php";

$pemerintah = mysqli_real_escape_string($conn, $_POST['pemerintah'] ?? '');
$dinas = mysqli_real_escape_string($conn, $_POST['dinas'] ?? '');
$nama_sekolah = mysqli_real_escape_string($conn, $_POST['nama_sekolah'] ?? '');
$alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
$jalan = mysqli_real_escape_string($conn, $_POST['jalan'] ?? '');
$kelas = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
$tahun_pelajaran = mysqli_real_escape_string($conn, $_POST['tahun_pelajaran'] ?? '');
$kepala_sekolah = mysqli_real_escape_string($conn, $_POST['kepala_sekolah'] ?? '');
$nip_kepala_sekolah = mysqli_real_escape_string($conn, $_POST['nip_kepala_sekolah'] ?? '');
$tahun_ajaran = mysqli_real_escape_string($conn, $_POST['tahun_ajaran'] ?? '');

mysqli_query($conn,"INSERT INTO sekolah
(pemerintah,dinas,nama_sekolah,alamat,jalan,kelas,tahun_pelajaran,kepala_sekolah,nip_kepala_sekolah,tahun_ajaran)
VALUES
('$pemerintah','$dinas','$nama_sekolah','$alamat','$jalan','$kelas','$tahun_pelajaran','$kepala_sekolah','$nip_kepala_sekolah','$tahun_ajaran')");

header("Location: ../../frontend/sekolah/index.php");
?>