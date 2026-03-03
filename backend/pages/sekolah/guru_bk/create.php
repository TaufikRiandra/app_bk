<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin'){
	header("Location: /frontend/dashboard.php");
	exit;
}
include "../../../config/database.php";

$nip = mysqli_real_escape_string($conn, $_POST['nip'] ?? '');
$nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
$no_telp = mysqli_real_escape_string($conn, $_POST['no_telp'] ?? '');
$foto = '';

// Validasi input
if(empty($nip) || empty($nama) || empty($no_telp)){
	header("Location: /frontend/sekolah/guru_bk/tambah.php?error=Semua field harus diisi");
	exit;
}

// Handle foto upload
if(isset($_FILES['foto']) && $_FILES['foto']['size'] > 0){
	$upload_dir = '../../assets/uploads/guru_bk/';
	
	// Buat directory jika belum ada
	if(!is_dir($upload_dir)){
		mkdir($upload_dir, 0755, true);
	}
	
	$file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
	$allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
	
	if(!in_array($file_ext, $allowed_ext)){
		header("Location: /frontend/sekolah/guru_bk/tambah.php?error=Format foto tidak didukung. Gunakan JPG, PNG, atau GIF");
		exit;
	}
	
	if($_FILES['foto']['size'] > 5 * 1024 * 1024){ // 5MB max
		header("Location: /frontend/sekolah/guru_bk/tambah.php?error=Ukuran foto maksimal 5MB");
		exit;
	}
	
	$new_filename = 'guru_bk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
	$foto_path = $upload_dir . $new_filename;
	
	if(move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)){
		$foto = '/frontend/assets/uploads/guru_bk/' . $new_filename;
	} else {
		header("Location: /frontend/sekolah/guru_bk/tambah.php?error=Gagal upload foto");
		exit;
	}
}

// Check if NIP already exists
$check_nip = mysqli_query($conn, "SELECT id_guru_bk FROM guru_bk WHERE nip = '$nip'");
if(mysqli_num_rows($check_nip) > 0){
	header("Location: /frontend/sekolah/guru_bk/tambah.php?error=NIP sudah terdaftar");
	exit;
}

// Insert ke database
if($foto){
	$query = "INSERT INTO guru_bk (nip, nama, no_telp, foto) VALUES ('$nip', '$nama', '$no_telp', '$foto')";
} else {
	$query = "INSERT INTO guru_bk (nip, nama, no_telp) VALUES ('$nip', '$nama', '$no_telp')";
}

$result = mysqli_query($conn, $query);

if($result){
	header("Location: /frontend/sekolah/guru_bk/index.php?success=Guru BK berhasil ditambahkan");
} else {
	header("Location: /frontend/sekolah/guru_bk/tambah.php?error=Gagal menambahkan guru BK");
}
?>
