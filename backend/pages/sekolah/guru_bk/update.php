<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin'){
	header("Location: /frontend/dashboard.php");
	exit;
}
include "../../config/database.php";

$id_guru_bk = intval($_POST['id_guru_bk'] ?? 0);
$nip = mysqli_real_escape_string($conn, $_POST['nip'] ?? '');
$nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
$no_telp = mysqli_real_escape_string($conn, $_POST['no_telp'] ?? '');

if(!$id_guru_bk || empty($nama) || empty($nip) || empty($no_telp)){
	header("Location: /frontend/sekolah/guru_bk/index.php?error=Data tidak valid");
	exit;
}

// Get current guru_bk data
$current = mysqli_query($conn, "SELECT * FROM guru_bk WHERE id_guru_bk = $id_guru_bk");
$guru_bk = mysqli_fetch_assoc($current);

if(!$guru_bk){
	header("Location: /frontend/sekolah/guru_bk/index.php?error=Guru BK tidak ditemukan");
	exit;
}

// Check if NIP changed and if new NIP already exists
if($nip !== $guru_bk['nip']){
	$check_nip = mysqli_query($conn, "SELECT id_guru_bk FROM guru_bk WHERE nip = '$nip' AND id_guru_bk != $id_guru_bk");
	if(mysqli_num_rows($check_nip) > 0){
		header("Location: /frontend/sekolah/guru_bk/edit.php?id=$id_guru_bk&error=NIP sudah terdaftar");
		exit;
	}
}

$foto = $guru_bk['foto'];

// Handle foto upload
if(isset($_FILES['foto']) && $_FILES['foto']['size'] > 0){
	$upload_dir = '../../assets/uploads/guru_bk/';
	
	if(!is_dir($upload_dir)){
		mkdir($upload_dir, 0755, true);
	}
	
	$file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
	$allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
	
	if(!in_array($file_ext, $allowed_ext)){
		header("Location: /frontend/sekolah/guru_bk/edit.php?id=$id_guru_bk&error=Format foto tidak didukung");
		exit;
	}
	
	if($_FILES['foto']['size'] > 5 * 1024 * 1024){
		header("Location: /frontend/sekolah/guru_bk/edit.php?id=$id_guru_bk&error=Ukuran foto maksimal 5MB");
		exit;
	}
	
	// Delete old foto
	if($guru_bk['foto'] && file_exists('.' . $guru_bk['foto'])){
		unlink('.' . $guru_bk['foto']);
	}
	
	$new_filename = 'guru_bk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
	$foto_path = $upload_dir . $new_filename;
	
	if(move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)){
		$foto = '/frontend/assets/uploads/guru_bk/' . $new_filename;
	} else {
		header("Location: /frontend/sekolah/guru_bk/edit.php?id=$id_guru_bk&error=Gagal upload foto");
		exit;
	}
}

// Update database
if($foto){
	$query = "UPDATE guru_bk SET nip = '$nip', nama = '$nama', no_telp = '$no_telp', foto = '$foto' WHERE id_guru_bk = $id_guru_bk";
} else {
	$query = "UPDATE guru_bk SET nip = '$nip', nama = '$nama', no_telp = '$no_telp' WHERE id_guru_bk = $id_guru_bk";
}

$result = mysqli_query($conn, $query);

if($result){
	header("Location: /frontend/sekolah/guru_bk/index.php?success=Guru BK berhasil diperbarui");
} else {
	header("Location: /frontend/sekolah/guru_bk/edit.php?id=$id_guru_bk&error=Gagal memperbarui guru BK");
}
?>
