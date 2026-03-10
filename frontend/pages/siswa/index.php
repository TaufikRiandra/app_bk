<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login'])){
	header("Location: /frontend/auth/login.php");
	exit;
}
include "../../../backend/config/database.php";
include "../../layouts/header.php";
include "../../layouts/sidebar.php";

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? '';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'data_siswa';
// Make $conn global for included files
$GLOBALS['conn'] = $conn;
?>

    <div style="background-color: #4472C4; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;"><i class="fas fa-users"></i> Data Siswa</h2>
        <p style="margin: 0 0 5px 0; font-size: 14px;">Kelola data siswa</p>
        <p style="margin: 0; font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($school_name) ?></p>
    </div>

	<!-- Tab Navigation -->
	<div style="display:flex;gap:0;margin-bottom:2rem;border-bottom:2px solid var(--border);flex-wrap:wrap">
		<a href="?tab=data_siswa" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'data_siswa' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'data_siswa' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'data_siswa' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'data_siswa' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-user"></i> Data Nama Siswa
		</a>
		<a href="?tab=data_pribadi_siswa" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'data_pribadi_siswa' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'data_pribadi_siswa' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'data_pribadi_siswa' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'data_pribadi_siswa' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-user-circle"></i> Data Pribadi Siswa
		</a>
		<a href="?tab=data_siswa_content" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'data_siswa_content' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'data_siswa_content' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'data_siswa_content' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'data_siswa_content' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-user-circle"></i> Rekap Data Pribadi Siswa
		</a>
	</div>

	<!-- Content Tab -->
	<div class="card">
		<?php if($tab === 'data_siswa'): ?>
			<?php include 'data_siswa.php'; ?>
		<?php elseif($tab === 'data_pribadi_siswa'): ?>
			<?php include 'data_pribadi_siswa.php'; ?>
		<?php elseif($tab === 'data_siswa_content'): ?>
			<?php include '../rekap/data_siswa_content.php'; ?>
		<?php endif; ?>
	</div>

</div>

<?php include "../../layouts/footer.php"; ?>

