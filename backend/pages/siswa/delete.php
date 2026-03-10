<?php
include "../config/database.php";

$id = (int) $_GET['id'];
mysqli_query($conn,"DELETE FROM siswa WHERE id_siswa=$id");

header("Location: ../../frontend/siswa/index.php");
?>