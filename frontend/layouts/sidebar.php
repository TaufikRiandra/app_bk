<?php
$GLOBALS['sidebar_included'] = true;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
?>
<div class="main-layout">
	<aside class="sidebar">
		<ul>
			<li><a href="/frontend/dashboard.php">📊 Dashboard</a></li>
			<li><a href="/frontend/siswa/index.php">👥 Data Siswa</a></li>
			<li><a href="/frontend/konseling/data_pribadi.php">📋 Data Pribadi Siswa</a></li>
			<li><a href="/frontend/absensi/index.php">📋 Absensi</a></li>
			<li><a href="/frontend/pages/kegiatan_harian_manual.php">📝 Kegiatan Harian</a></li>
			<li><a href="/frontend/pages/penilaian.php">⭐ Penilaian Siswa</a></li>
			
			<?php if($role === 'admin'): ?>
				<!-- Menu untuk Admin saja -->
				<li><a href="/frontend/sekolah/index.php">🏫 Data Sekolah</a></li>
				<li><a href="/frontend/sekolah/guru_bk/index.php">👨‍🏫 Kelola Guru BK</a></li>
			<?php elseif($role === 'guru_bk'): ?>
				<!-- Menu untuk Guru BK - langsung ke Data Kelas -->
				<li><a href="/frontend/sekolah/kelas.php">📚 Data Kelas</a></li>
			<?php endif; ?>
			
			<li><a href="/frontend/rekap/konseling.php">📋 Input Layanan</a></li>		<li><a href="/frontend/rekap/kegiatan_harian.php">📊 Rekap Kegiatan Harian</a></li>			<li><a href="/backend/auth/logout.php">🚪 Logout</a></li>
		</ul>
	</aside>
	<section class="content-area">