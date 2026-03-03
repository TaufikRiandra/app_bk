<?php
include "../config/database.php";

$id = intval($_POST['id']);
$nis = mysqli_real_escape_string($conn, $_POST['nis'] ?? '');
$nama_siswa = mysqli_real_escape_string($conn, $_POST['nama_siswa'] ?? '');
$jk = mysqli_real_escape_string($conn, $_POST['jk'] ?? '');
$kelas = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
$jurusan = mysqli_real_escape_string($conn, $_POST['jurusan'] ?? '');
$no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');
$alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');

mysqli_query($conn,"UPDATE siswa SET
nis='$nis',
nama_siswa='$nama_siswa',
jk='$jk',
kelas='$kelas',
jurusan='$jurusan',
no_hp='$no_hp',
alamat='$alamat'
WHERE id_siswa=$id");

header("Location: ../../frontend/siswa/index.php");
?>