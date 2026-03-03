<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}
// Proteksi: hanya admin yang bisa set BK
if(isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'){
    header("Location: /frontend/dashboard.php");
    exit;
}
include "../../backend/config/database.php";
include "../layouts/header.php";
include "../layouts/sidebar.php";

// Get all schools
$sekolah_data = mysqli_query($conn, "SELECT DISTINCT id_sekolah, nama_sekolah FROM sekolah ORDER BY nama_sekolah");

// Get all guru_bk
$guru_bk_data = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");

// Define kelas options
$kelas_7 = array_map(function($k) { return '7'.$k; }, range('A', 'F'));
$kelas_8 = array_map(function($k) { return '8'.$k; }, range('A', 'F'));
$kelas_9 = array_map(function($k) { return '9'.$k; }, range('A', 'F'));
$all_kelas = array_merge($kelas_7, $kelas_8, $kelas_9);
?>

<section class="card" style="max-width:600px;margin:0 auto">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem">👨‍🏫 Tetapkan Guru BK</h1>
		<p style="color:var(--text-light);margin:0">Tetapkan guru BK untuk setiap kelas</p>
	</div>

	<!-- Success Message -->
	<?php if(isset($_GET['success'])): ?>
		<div style="padding:1rem;background:#dcfce7;border:1px solid #86efac;border-radius:6px;margin-bottom:1.5rem;color:#166534">
			✅ Penugasan Guru BK berhasil disimpan!
		</div>
	<?php endif; ?>

	<!-- Error Message -->
	<?php if(isset($_GET['error'])): ?>
		<div style="padding:1rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;margin-bottom:1.5rem;color:#991b1b">
			❌ <?= htmlspecialchars($_GET['error']) ?>
		</div>
	<?php endif; ?>

	<!-- Info: Manage Guru BK -->
	<div style="padding:1rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:6px;margin-bottom:1.5rem;color:#78350f">
		ℹ️ Belum ada guru BK? <a href="./guru_bk/tambah.php" style="font-weight:600;color:#b45309;text-decoration:none">Tambah Guru BK di sini</a>
	</div>

	<form action="../../backend/sekolah/set_bk.php" method="POST">
		<label for="id_sekolah" style="display:block;margin-bottom:0.5rem;font-weight:600">Pilih Sekolah <span style="color:#ef4444">*</span></label>
		<select id="id_sekolah" name="id_sekolah" required style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;margin-bottom:1.5rem;box-sizing:border-box">
			<option value="">-- Pilih Sekolah --</option>
			<?php 
			while($s = mysqli_fetch_assoc($sekolah_data)){ 
			?>
				<option value="<?= $s['id_sekolah'] ?>">
					<?= htmlspecialchars($s['nama_sekolah']) ?>
				</option>
			<?php } ?>
		</select>

		<label for="kelas" style="display:block;margin-bottom:0.5rem;font-weight:600">Pilih Kelas <span style="color:#ef4444">*</span></label>
		<select id="kelas" name="kelas" required style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;margin-bottom:1.5rem;box-sizing:border-box">
			<option value="">-- Pilih Kelas --</option>
			<?php foreach($all_kelas as $k): ?>
				<option value="<?= $k ?>"><?= $k ?></option>
			<?php endforeach; ?>
		</select>

		<label for="id_guru_bk" style="display:block;margin-bottom:0.5rem;font-weight:600">Guru BK <span style="color:#ef4444">*</span></label>
		<select id="id_guru_bk" name="id_guru_bk" required style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;margin-bottom:1.5rem;box-sizing:border-box">
			<option value="">-- Pilih Guru BK --</option>
			<?php 
			// Reset pointer untuk query guru_bk
			mysqli_data_seek($guru_bk_data, 0);
			while($g = mysqli_fetch_assoc($guru_bk_data)){ 
			?>
				<option value="<?= $g['id_guru_bk'] ?>">
					<?= htmlspecialchars($g['nama']) ?> (<?= htmlspecialchars($g['nip']) ?>)
				</option>
			<?php } ?>
		</select>

		<div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
			<button type="submit" class="btn">✅ Simpan Penugasan</button>
			<a href="/frontend/sekolah/index.php" class="btn secondary" style="text-decoration:none">← Kembali</a>
			<a href="./guru_bk/index.php" class="btn secondary" style="text-decoration:none">👨‍🏫 Kelola Guru BK</a>
		</div>

	</form>

</section>

<?php include "../layouts/footer.php"; ?>

		</div>
	</form>
</section>

<?php include "../layouts/footer.php"; ?>

