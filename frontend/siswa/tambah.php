<?php
session_start();
if(!isset($_SESSION['login'])){
	header("Location: /frontend/auth/login.php");
	exit;
}
include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<section class="card" style="max-width:600px;margin:0 auto">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem">➕ Tambah Data Siswa</h1>
		<p style="color:var(--text-light);margin:0">Isi form di bawah untuk menambahkan siswa baru ke sistem</p>
	</div>

	<form action="../../backend/siswa/create.php" method="POST">
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="nis">NIS</label>
				<input type="text" id="nis" name="nis" required placeholder="Nomor Induk Siswa">
			</div>
			
			<div>
				<label for="nama_siswa">Nama Lengkap</label>
				<input type="text" id="nama_siswa" name="nama_siswa" required placeholder="Nama lengkap siswa">
			</div>
		</div>
		
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="jk">Jenis Kelamin</label>
				<select id="jk" name="jk" required>
					<option value="">-- Pilih Jenis Kelamin --</option>
					<option value="L">👦 Laki-laki</option>
					<option value="P">👧 Perempuan</option>
				</select>
			</div>
			
			<div>
				<label for="kelas">Kelas</label>
				<input type="text" id="kelas" name="kelas" required placeholder="Contoh: X-A atau 10 IPA 1">
			</div>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
			<div>
				<label for="jurusan">Jurusan</label>
				<input type="text" id="jurusan" name="jurusan" placeholder="Contoh: IPA, IPS, Teknik">
			</div>
			
			<div>
				<label for="no_hp">No HP</label>
				<input type="text" id="no_hp" name="no_hp" placeholder="Nomor telepon siswa">
			</div>
		</div>
		
		<div>
			<label for="alamat">Alamat</label>
			<textarea id="alamat" name="alamat" placeholder="Alamat lengkap siswa" style="height:100px;resize:vertical"></textarea>
		</div>
		
		<div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
			<button type="submit" class="btn">✅ Simpan Data</button>
			<a href="/frontend/siswa/index.php" class="btn secondary" style="text-decoration:none">← Kembali</a>
		</div>
	</form>
</section>

<?php include "../layouts/footer.php"; ?>