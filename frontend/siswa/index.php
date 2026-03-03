<?php
session_start();
if(!isset($_SESSION['login'])){
	header("Location: /frontend/auth/login.php");
	exit;
}
include "../../backend/config/database.php";
include "../layouts/header.php";
include "../layouts/sidebar.php";

$data = mysqli_query($conn,"SELECT * FROM siswa");
?>

<section class="card">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
		<div>
			<h1 style="margin:0;font-size:1.75rem">👥 Data Siswa</h1>
			<p style="color:var(--text-light);margin:0.25rem 0 0 0">Kelola data siswa sekolah Anda</p>
		</div>
		<a href="./tambah.php" class="btn" style="white-space:nowrap">➕ Tambah Siswa</a>
	</div>

	<?php if (mysqli_num_rows($data) > 0): ?>
		<table>
			<thead>
				<tr>
					<th style="width:15%">NIS</th>
					<th style="width:25%">Nama Siswa</th>
					<th style="width:15%">Kelas</th>
					<th style="width:15%">Jenis Kelamin</th>
					<th style="width:30%;text-align:center">Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php while($r=mysqli_fetch_assoc($data)){ ?>
					<tr>
						<td><strong><?= htmlspecialchars($r['nis']) ?></strong></td>
						<td><?= htmlspecialchars($r['nama_siswa']) ?></td>
						<td><?= htmlspecialchars($r['kelas']) ?></td>
						<td><?= htmlspecialchars($r['jk'] ?? '-') ?></td>
						<td style="text-align:center">
						<a href="./edit.php?id=<?= $r['id_siswa'] ?>" style="color:var(--brand);text-decoration:none;margin-right:1rem;font-weight:500;transition:all 0.3s" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">✏️ Edit</a>
						<a href="../../backend/siswa/delete.php?id=<?= $r['id_siswa'] ?>" onclick="return confirm('Yakin hapus data ini?')" style="color:#ef4444;text-decoration:none;font-weight:500;transition:all 0.3s" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">🗑️ Hapus</a>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php else: ?>
		<div style="text-align:center;padding:2rem;background:rgba(37,99,235,0.04);border-radius:8px">
			<div style="font-size:2rem;margin-bottom:1rem">📭</div>
			<p style="color:var(--text-light);margin:0 0 1rem 0">Belum ada data siswa. Tambahkan data pertama Anda sekarang!</p>
				<a href="/frontend/siswa/tambah.php" class="btn">➕ Tambah Siswa</a>
		</div>
	<?php endif; ?>
</section>

<?php include "../layouts/footer.php"; ?>