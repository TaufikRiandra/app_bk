<?php
include "../../config/database.php";

$id = (int) $_GET['id'];
mysqli_query($conn,"DELETE FROM sekolah WHERE id_sekolah=$id");

header("Location: ../../../frontend/pages/sekolah/index.php");
?>