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

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'data_siswa';
// Make $conn global for included files
$GLOBALS['conn'] = $conn;
?>

<div>
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem"><i class="fas fa-clipboard-list"></i> Absensi</h1>
		<p style="color:var(--text-light);margin:0">Kelola data siswa, absensi, dan rekap absensi</p>
	</div>

	<!-- Tab Navigation -->
	<div style="display:flex;gap:0;margin-bottom:2rem;border-bottom:2px solid var(--border);flex-wrap:wrap">
		<a href="?tab=data_siswa" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'data_siswa' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'data_siswa' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'data_siswa' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'data_siswa' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-user"></i> Data Nama Siswa
		</a>
		<a href="?tab=absen_siswa" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'absen_siswa' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'absen_siswa' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'absen_siswa' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'absen_siswa' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-check"></i> Absen Siswa
		</a>
		<a href="?tab=rekap_absen" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'rekap_absen' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'rekap_absen' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'rekap_absen' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'rekap_absen' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-chart-bar"></i> Rekap Absensi
		</a>
	</div>

	<!-- Content Tab -->
	<div class="card">
		<?php if($tab === 'data_siswa'): ?>
			<?php include 'data_siswa.php'; ?>
		<?php elseif($tab === 'absen_siswa'): ?>
			<?php include 'absen_siswa.php'; ?>
		<?php elseif($tab === 'rekap_absen'): ?>
			<?php include 'rekap_absen.php'; ?>
		<?php endif; ?>
	</div>

</div>

<?php include "../../layouts/footer.php"; ?>

