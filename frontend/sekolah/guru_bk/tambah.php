<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin'){
	header("Location: /frontend/dashboard.php");
	exit;
}
include "../../../backend/config/database.php";
include "../../layouts/header.php";
include "../../layouts/sidebar.php";
?>

<section class="card" style="max-width:600px;margin:0 auto">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem">➕ Tambah Guru BK</h1>
		<p style="color:var(--text-light);margin:0">Daftarkan guru bimbingan konseling baru</p>
	</div>

	<!-- Error Message -->
	<?php if(isset($_GET['error'])): ?>
		<div style="padding:1rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;margin-bottom:1.5rem;color:#991b1b">
			❌ <?= htmlspecialchars($_GET['error']) ?>
		</div>
	<?php endif; ?>

	<form action="../../../backend/sekolah/guru_bk/create.php" method="POST" enctype="multipart/form-data">
		
		<div style="margin-bottom:1.5rem">
			<label for="nip" style="display:block;margin-bottom:0.5rem;font-weight:600">NIP <span style="color:#ef4444">*</span></label>
			<input type="text" id="nip" name="nip" required placeholder="19xx0101 xxxxxx x xxx" 
				style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;box-sizing:border-box" 
				pattern="[0-9\s]+" title="NIP hanya boleh berisi angka dan spasi">
			<small style="color:var(--text-light);display:block;margin-top:0.25rem">Format: 19xx0101 xxxxxx x xxx</small>
		</div>

		<div style="margin-bottom:1.5rem">
			<label for="nama" style="display:block;margin-bottom:0.5rem;font-weight:600">Nama Lengkap <span style="color:#ef4444">*</span></label>
			<input type="text" id="nama" name="nama" required placeholder="Nama guru BK" 
				style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;box-sizing:border-box">
		</div>

		<div style="margin-bottom:1.5rem">
			<label for="no_telp" style="display:block;margin-bottom:0.5rem;font-weight:600">No. Telepon <span style="color:#ef4444">*</span></label>
			<input type="tel" id="no_telp" name="no_telp" required placeholder="08xxxxxxxxxx" 
				style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;box-sizing:border-box"
				pattern="[0-9\+\-\s]+" title="No. telepon tidak valid">
		</div>

		<div style="margin-bottom:1.5rem">
			<label for="foto" style="display:block;margin-bottom:0.5rem;font-weight:600">Foto Profil <span style="color:#ef4444">*</span></label>
			<div style="display:flex;align-items:center;gap:1rem">
				<div id="preview" style="width:80px;height:80px;border:2px dashed var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--bg-light);color:var(--text-light);font-size:2rem">
					📷
				</div>
				<div>
					<input type="file" id="foto" name="foto" accept="image/*" required
						style="display:none" onchange="previewImage(event)">
					<button type="button" onclick="document.getElementById('foto').click()" class="btn" style="cursor:pointer;border:none">
						Pilih Foto
					</button>
					<small style="color:var(--text-light);display:block;margin-top:0.5rem">JPG, PNG, GIF max 5MB</small>
				</div>
			</div>
		</div>

		<div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
			<button type="submit" class="btn">✅ Simpan Guru BK</button>
			<a href="./index.php" class="btn secondary" style="text-decoration:none">← Kembali</a>
		</div>

	</form>

</section>

<script>
function previewImage(event) {
	const file = event.target.files[0];
	const preview = document.getElementById('preview');
	
	if (file) {
		const reader = new FileReader();
		reader.onload = function(e) {
			preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;border-radius:8px;object-fit:cover">';
		};
		reader.readAsDataURL(file);
	}
}
</script>

<?php include "../../layouts/footer.php"; ?>
