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

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'kegiatan_harian';
// Make $conn global for included files
$GLOBALS['conn'] = $conn;
?>

<div>
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem"><i class="fas fa-chart-pie"></i> Rekap Layanan Bimbingan</h1>
		<p style="color:var(--text-light);margin:0">Lihat rekap semua layanan bimbingan yang telah diberikan</p>
	</div>

	<!-- Tab Navigation -->
	<div style="display:flex;gap:0;margin-bottom:2rem;border-bottom:2px solid var(--border);flex-wrap:wrap">
		<a href="?tab=kegiatan_harian" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'kegiatan_harian' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'kegiatan_harian' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'kegiatan_harian' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'kegiatan_harian' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-calendar-check"></i> Kegiatan Harian
		</a>
		<a href="?tab=konseling" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'konseling' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'konseling' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'konseling' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'konseling' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-handshake"></i> Konseling
		</a>
		<a href="?tab=layanan_mediasi" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'layanan_mediasi' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'layanan_mediasi' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'layanan_mediasi' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'layanan_mediasi' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-medkit"></i> Layanan Mediasi
		</a>
	</div>

	<!-- Content Tab -->
	<div class="card">
		<?php if($tab === 'kegiatan_harian'): ?>
			<?php include 'kegiatan_harian_content.php'; ?>
		<?php elseif($tab === 'konseling'): ?>
			<?php include 'konseling_content.php'; ?>
		<?php elseif($tab === 'layanan_mediasi'): ?>
			<?php include 'layanan_mediasi_content.php'; ?>
		<?php endif; ?>
	</div>

</div>

<?php include "../../layouts/footer.php"; ?>
