<?php
include "../../config/database.php";

$id = intval($_POST['id']);
$pemerintah = mysqli_real_escape_string($conn, $_POST['pemerintah'] ?? '');
$dinas = mysqli_real_escape_string($conn, $_POST['dinas'] ?? '');
$nama_sekolah = mysqli_real_escape_string($conn, $_POST['nama_sekolah'] ?? '');
$alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
$jalan = mysqli_real_escape_string($conn, $_POST['jalan'] ?? '');
$kelas = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
$kepala_sekolah = mysqli_real_escape_string($conn, $_POST['kepala_sekolah'] ?? '');
$nip_kepala_sekolah = mysqli_real_escape_string($conn, $_POST['nip_kepala_sekolah'] ?? '');
$tahun_ajaran = mysqli_real_escape_string($conn, $_POST['tahun_ajaran'] ?? '');

mysqli_query($conn,"UPDATE sekolah SET
pemerintah='$pemerintah',
dinas='$dinas',
nama_sekolah='$nama_sekolah',
alamat='$alamat',
jalan='$jalan',
kelas='$kelas',
kepala_sekolah='$kepala_sekolah',
nip_kepala_sekolah='$nip_kepala_sekolah',
tahun_ajaran='$tahun_ajaran'
WHERE id_sekolah=$id");

header("Location: ../../../frontend/pages/sekolah/index.php");
?>