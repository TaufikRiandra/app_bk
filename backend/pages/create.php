<?php
include "../config/database.php";

mysqli_query($conn,"INSERT INTO rekap_layanan
(id_siswa,id_layanan,tanggal,permasalahan,tindak_lanjut,hasil)
VALUES
('$_POST[id_siswa]',
'$_POST[id_layanan]',
'$_POST[tanggal]',
'$_POST[permasalahan]',
'$_POST[tindak_lanjut]',
'$_POST[hasil]')");

header("Location: ../../frontend/rekap/index.php");
?>