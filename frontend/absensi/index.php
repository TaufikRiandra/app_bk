<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login'])){
	header("Location: /frontend/auth/login.php");
	exit;
}
include "../../backend/config/database.php";
include "../layouts/header.php";
include "../layouts/sidebar.php";

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'data_siswa';
// Make $conn global for included files
$GLOBALS['conn'] = $conn;
?>

<section class="card">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem">📋 Absensi</h1>
		<p style="color:var(--text-light);margin:0">Kelola data siswa, absensi, dan rekap absensi</p>
	</div>

	<!-- Tab Navigation -->
	<div style="display:flex;gap:1rem;margin-bottom:2rem;border-bottom:2px solid var(--border);padding-bottom:1rem">
		<a href="?tab=data_siswa" style="padding:0.75rem 1.5rem;text-decoration:none;color:<?= $tab === 'data_siswa' ? 'var(--brand)' : 'var(--text-light)' ?>;border-bottom:3px solid <?= $tab === 'data_siswa' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'data_siswa' ? '600' : 'normal' ?>">
			👤 Data Pribadi Siswa
		</a>
		<a href="?tab=absen_siswa" style="padding:0.75rem 1.5rem;text-decoration:none;color:<?= $tab === 'absen_siswa' ? 'var(--brand)' : 'var(--text-light)' ?>;border-bottom:3px solid <?= $tab === 'absen_siswa' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'absen_siswa' ? '600' : 'normal' ?>">
			✓ Absen Siswa
		</a>
		<a href="?tab=rekap_absen" style="padding:0.75rem 1.5rem;text-decoration:none;color:<?= $tab === 'rekap_absen' ? 'var(--brand)' : 'var(--text-light)' ?>;border-bottom:3px solid <?= $tab === 'rekap_absen' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'rekap_absen' ? '600' : 'normal' ?>">
			📊 Rekap Absensi
		</a>
	</div>

	<!-- Content Tab -->
	<?php if($tab === 'data_siswa'): ?>
		<?php include 'data_siswa.php'; ?>
	<?php elseif($tab === 'absen_siswa'): ?>
		<?php include 'absen_siswa.php'; ?>
	<?php elseif($tab === 'rekap_absen'): ?>
		<?php include 'rekap_absen.php'; ?>
	<?php endif; ?>

</section>

<?php include "../layouts/footer.php"; ?>
