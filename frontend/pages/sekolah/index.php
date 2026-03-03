<?php
// Single session start and includes — avoid duplicates
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login'])){
	header("Location: /frontend/auth/login.php");
	exit;
}
// Protect: hanya admin yang bisa akses halaman ini
if(isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'){
	header("Location: /frontend/sekolah/kelas.php");
	exit;
}
include "../../../backend/config/database.php";
include "../../layouts/header.php";
include "../../layouts/sidebar.php";

$data = mysqli_query($conn,"SELECT * FROM sekolah");
?>

<section class="card">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
		<div>
			<h1 style="margin:0;font-size:1.75rem">🏫 Data Sekolah</h1>
			<p style="color:var(--text-light);margin:0.25rem 0 0 0">Kelola informasi sekolah dan tahun ajaran</p>
		</div>
		<div style="display:flex;gap:0.75rem;flex-wrap:wrap">
			<a href="./kelas.php" class="btn" style="white-space:nowrap">📚 Data Kelas</a>
			<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
				<a href="./guru_bk/index.php" class="btn" style="white-space:nowrap">👨‍🏫 Kelola Guru BK</a>
				<a href="./bk.php" class="btn" style="white-space:nowrap">🔗 Tetapkan BK</a>
				<a href="./tambah.php" class="btn" style="white-space:nowrap">➕ Tambah Sekolah</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if (mysqli_num_rows($data) > 0): ?>
		<table>
			<thead>
				<tr>
					<th style="width:30%">🏫 Nama Sekolah</th>
					<th style="width:25%">👨‍💼 Kepala Sekolah</th>
					<th style="width:15%">📅 Tahun Ajaran</th>
					<th style="width:30%;text-align:center">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php while($r=mysqli_fetch_assoc($data)){ ?>
					<tr>
						<td><strong><?= htmlspecialchars($r['nama_sekolah']) ?></strong></td>
						<td><?= htmlspecialchars($r['kepala_sekolah']) ?></td>
						<td><?= htmlspecialchars($r['tahun_ajaran']) ?></td>
						<td style="text-align:center">
							<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
								<a href="./edit.php?id=<?= $r['id_sekolah'] ?>" style="color:var(--brand);text-decoration:none;margin-right:1rem;font-weight:500;transition:all 0.3s" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">✏️ Edit</a>
									<a href="../../../backend/sekolah/delete.php?id=<?= $r['id_sekolah'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn-delete"><i class="fas fa-trash"></i> Hapus</a>
							<?php else: ?>
								<span style="color:var(--text-light)">No action available</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php else: ?>
		<div style="text-align:center;padding:2rem;background:rgba(37,99,235,0.04);border-radius:8px">
			<div style="font-size:2rem;margin-bottom:1rem">📭</div>
			<p style="color:var(--text-light);margin:0 0 1rem 0">Belum ada data sekolah. Tambahkan data pertama Anda sekarang!</p>
			<a href="./tambah.php" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Sekolah</a>
		</div>
	<?php endif; ?>
</section>

<?php include "../../layouts/footer.php"; ?>

