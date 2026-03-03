<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}
// Proteksi: hanya admin yang bisa edit sekolah
if(isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'){
    header("Location: /frontend/dashboard.php");
    exit;
}
include "../../backend/config/database.php";
include "../layouts/header.php";
include "../layouts/sidebar.php";

$data = mysqli_query($conn,"SELECT * FROM sekolah WHERE id_sekolah='$_GET[id]'");
$d = mysqli_fetch_assoc($data);
?>

<section class="card" style="max-width:600px;margin:0 auto">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem">✏️ Edit Data Sekolah</h1>
		<p style="color:var(--text-light);margin:0">Perbarui informasi sekolah di bawah ini</p>
	</div>

	<form action="../../backend/sekolah/update.php" method="POST">
		<input type="hidden" name="id" value="<?= $d['id_sekolah'] ?>">

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="pemerintah">Pemerintah</label>
				<input type="text" id="pemerintah" name="pemerintah" value="<?= htmlspecialchars($d['pemerintah'] ?? '') ?>" placeholder="Contoh: Pemerintah Provinsi">
			</div>
			
			<div>
				<label for="dinas">Dinas</label>
				<input type="text" id="dinas" name="dinas" value="<?= htmlspecialchars($d['dinas'] ?? '') ?>" placeholder="Contoh: Dinas Pendidikan">
			</div>
		</div>
		
		<label for="nama_sekolah">Nama Sekolah</label>
		<input type="text" id="nama_sekolah" name="nama_sekolah" value="<?= htmlspecialchars($d['nama_sekolah'] ?? '') ?>" required placeholder="Nama lengkap sekolah">
		
		<label for="alamat">Alamat</label>
		<textarea id="alamat" name="alamat" placeholder="Alamat lengkap sekolah" style="height:80px;resize:vertical"><?= htmlspecialchars($d['alamat'] ?? '') ?></textarea>
		
		<label for="jalan">Jalan</label>
		<input type="text" id="jalan" name="jalan" value="<?= htmlspecialchars($d['jalan'] ?? '') ?>" placeholder="Nama jalan sekolah">
		
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="kelas">Kelas</label>
				<input type="text" id="kelas" name="kelas" value="<?= htmlspecialchars($d['kelas'] ?? '') ?>" placeholder="Contoh: X, XI, XII">
			</div>
			
			<div>
				<label for="tahun_pelajaran">Tahun Pelajaran</label>
				<input type="text" id="tahun_pelajaran" name="tahun_pelajaran" value="<?= htmlspecialchars($d['tahun_pelajaran'] ?? '') ?>" placeholder="2024/2025">
			</div>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="kepala_sekolah">Kepala Sekolah</label>
				<input type="text" id="kepala_sekolah" name="kepala_sekolah" value="<?= htmlspecialchars($d['kepala_sekolah'] ?? '') ?>" required placeholder="Nama kepala sekolah">
			</div>
			
			<div>
				<label for="nip_kepala_sekolah">NIP Kepala Sekolah</label>
				<input type="text" id="nip_kepala_sekolah" name="nip_kepala_sekolah" value="<?= htmlspecialchars($d['nip_kepala_sekolah'] ?? '') ?>" placeholder="19xx0101 xxxxxx x xxx">
			</div>
		</div>

		<label for="tahun_ajaran">Tahun Ajaran</label>
		<input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?= htmlspecialchars($d['tahun_ajaran'] ?? '') ?>" placeholder="2024/2025">
		
		<div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
			<button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Update Data</button>
			<a href="../../frontend/sekolah/index.php" class="btn secondary" style="text-decoration:none">← Kembali</a>
		</div>
	</form>
</section>

<?php include "../layouts/footer.php"; ?>

