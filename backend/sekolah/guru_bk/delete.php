<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin'){
	header("Location: /frontend/dashboard.php");
	exit;
}
include "../../config/database.php";

$id_guru_bk = intval($_GET['id'] ?? 0);

if(!$id_guru_bk){
	header("Location: /frontend/sekolah/guru_bk/index.php?error=ID tidak valid");
	exit;
}

// Get guru_bk data
$guru_bk = mysqli_query($conn, "SELECT * FROM guru_bk WHERE id_guru_bk = $id_guru_bk");
$data = mysqli_fetch_assoc($guru_bk);

if(!$data){
	header("Location: /frontend/sekolah/guru_bk/index.php?error=Guru BK tidak ditemukan");
	exit;
}

// Delete foto if exists
if($data['foto'] && file_exists('.' . $data['foto'])){
	unlink('.' . $data['foto']);
}

// Delete dari database (cascade delete akan hapus guru_bk_kelas yang referensi id_guru_bk ini)
$result = mysqli_query($conn, "DELETE FROM guru_bk WHERE id_guru_bk = $id_guru_bk");

if($result){
	header("Location: /frontend/sekolah/guru_bk/index.php?success=Guru BK berhasil dihapus");
} else {
	header("Location: /frontend/sekolah/guru_bk/index.php?error=Gagal menghapus guru BK");
}
?>
