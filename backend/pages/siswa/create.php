<?php
include "../config/database.php";

mysqli_query($conn,"INSERT INTO siswa 
(nis,nama_siswa,jk,kelas,jurusan,no_hp,alamat)
VALUES 
('$_POST[nis]',
'$_POST[nama_siswa]',
'$_POST[jk]',
'$_POST[kelas]',
'$_POST[jurusan]',
'$_POST[no_hp]',
'$_POST[alamat]')");

header("Location: ../../frontend/siswa/index.php");
?>