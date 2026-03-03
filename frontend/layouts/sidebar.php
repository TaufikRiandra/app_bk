<?php
$GLOBALS['sidebar_included'] = true;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
?>
<div class="main-container">
	<aside class="sidebar">
		<ul class="sidebar-menu">
			<li><a href="/frontend/dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
			<li><a href="/frontend/pages/konseling/data_pribadi.php" class="sidebar-link"><i class="fas fa-user-circle"></i> Data Pribadi Siswa</a></li>
			<li><a href="/frontend/pages/absensi/index.php" class="sidebar-link"><i class="fas fa-clipboard-list"></i> Absensi</a></li>
			<li><a href="/frontend/pages/kegiatan_harian_manual.php" class="sidebar-link"><i class="fas fa-calendar"></i> Kegiatan Harian</a></li>
			<li><a href="/frontend/pages/penilaian.php" class="sidebar-link"><i class="fas fa-star"></i> Penilaian Siswa</a></li>
			<li><a href="/frontend/pages/layanan_mediasi_manual.php" class="sidebar-link"><i class="fas fa-handshake"></i> Layanan Mediasi</a></li>
			
			<?php if($role === 'admin'): ?>
				<!-- Menu untuk Admin saja -->
				<li><a href="/frontend/pages/sekolah/index.php" class="sidebar-link"><i class="fas fa-school"></i> Data Sekolah</a></li>
				<li><a href="/frontend/pages/guru_bk/index.php" class="sidebar-link"><i class="fas fa-chalkboard-user"></i> Kelola Guru BK</a></li>
			<?php elseif($role === 'guru_bk'): ?>
				<!-- Menu untuk Guru BK - langsung ke Data Kelas -->
				<li><a href="/frontend/pages/sekolah/kelas.php" class="sidebar-link"><i class="fas fa-book"></i> Data Kelas</a></li>
			<?php endif; ?>
			
			<li><a href="/frontend/pages/rekap/layanan_mediasi.php" class="sidebar-link"><i class="fas fa-file-pdf"></i> Rekap</a></li>
			<li><a href="/backend/auth/logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
		</ul>
	</aside>
	<section class="content-area">
