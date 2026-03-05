<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../../auth/login.php");
    exit;
}
// Proteksi: hanya admin yang bisa tambah sekolah
if(isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'){
    header("Location: ../../dashboard.php");
    exit;
}
include "../../layouts/header.php";
include "../../layouts/sidebar.php";
?>

<section class="card" style="max-width:600px;margin:0 auto">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem"><i class="fas fa-plus-circle"></i> Tambah Data Sekolah</h1>
		<p style="color:var(--text-light);margin:0">Isi form di bawah untuk menambahkan sekolah baru ke sistem</p>
	</div>

	<form action="../../../backend/pages/sekolah/create.php" method="POST">
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="pemerintah">Pemerintah</label>
				<input type="text" id="pemerintah" name="pemerintah" required placeholder="Contoh: Pemerintah Provinsi">
			</div>
			
			<div>
				<label for="dinas">Dinas</label>
				<input type="text" id="dinas" name="dinas" required placeholder="Contoh: Dinas Pendidikan">
			</div>
		</div>

		<label for="nama_sekolah">Nama Sekolah</label>
		<input type="text" id="nama_sekolah" name="nama_sekolah" required placeholder="Contoh: SMA Negeri 1 Jakarta">
		
		<label for="alamat">Alamat</label>
		<textarea id="alamat" name="alamat" required placeholder="Alamat lengkap sekolah" style="height:80px;resize:vertical"></textarea>
		
		<label for="jalan">Jalan</label>
		<input type="text" id="jalan" name="jalan" required placeholder="Nama jalan sekolah">
		
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="kelas">Kelas</label>
				<input type="text" id="kelas" name="kelas" required placeholder="Contoh: X, XI, XII">
			</div>
			
			<div>
				<label for="tahun_ajaran">Tahun Ajaran</label>
				<input type="text" id="tahun_ajaran" name="tahun_ajaran" placeholder="2026/2027" required>
			</div>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="kepala_sekolah">Kepala Sekolah</label>
				<input type="text" id="kepala_sekolah" name="kepala_sekolah" required placeholder="Nama kepala sekolah">
			</div>
			
			<div>
				<label for="nip_kepala_sekolah">NIP Kepala Sekolah</label>
				<input type="text" id="nip_kepala_sekolah" name="nip_kepala_sekolah" required placeholder="19xx0101 xxxxxx x xxx">
			</div>
		</div>
		
		<div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
			<button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Simpan Data</button>
			<a href="index.php" class="btn secondary" style="text-decoration:none">← Kembali</a>
		</div>
	</form>
</section>

<?php include "../../layouts/footer.php"; ?>

