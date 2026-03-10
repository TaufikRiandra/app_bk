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

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'absen_siswa';
// Make $conn global for included files
$GLOBALS['conn'] = $conn;
?>

    <div style="background-color: #4472C4; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;"><i class="fas fa-clipboard-list"></i> Absensi</h2>
        <p style="margin: 0 0 5px 0; font-size: 14px;">Kelola absensi siswa dan rekap absensi</p>
        <p style="margin: 0; font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($school_name) ?></p>
    </div>

	<!-- Tab Navigation -->
	<div style="display:flex;gap:0;margin-bottom:2rem;border-bottom:2px solid var(--border);flex-wrap:wrap">
		<a href="?tab=absen_siswa" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'absen_siswa' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'absen_siswa' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'absen_siswa' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'absen_siswa' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-user"></i> Absen Siswa
		</a>
		<a href="?tab=rekap_absen" style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;color:<?= $tab === 'rekap_absen' ? 'var(--brand)' : 'var(--text-light)' ?>;background-color:<?= $tab === 'rekap_absen' ? '#E8E4FF' : 'transparent' ?>;border-bottom:3px solid <?= $tab === 'rekap_absen' ? 'var(--brand)' : 'transparent' ?>;cursor:pointer;font-weight:<?= $tab === 'rekap_absen' ? '600' : 'normal' ?>;border-radius:4px 4px 0 0">
			<i class="fas fa-chart-bar"></i> Rekap Absensi
		</a>
	</div>

	<!-- Content Tab -->
	<div class="card">
		<?php if($tab === 'absen_siswa'): ?>
			<?php include 'absen_siswa.php'; ?>
		<?php elseif($tab === 'rekap_absen'): ?>
			<?php include 'rekap_absen.php'; ?>
		<?php endif; ?>
	</div>

</div>

<?php include "../../layouts/footer.php"; ?>

