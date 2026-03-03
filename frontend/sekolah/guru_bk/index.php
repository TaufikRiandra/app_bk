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

// Get all guru_bk
$guru_bk_list = mysqli_query($conn, "SELECT * FROM guru_bk ORDER BY nama ASC");
?>

<section class="card">
	<div style="margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
		<div>
			<h1 style="margin-bottom:0.25rem;font-size:1.75rem">👨‍🏫 Kelola Guru BK</h1>
			<p style="color:var(--text-light);margin:0">Manajemen data guru bimbingan konseling</p>
		</div>
		<a href="./tambah.php" class="btn" style="text-decoration:none">➕ Tambah Guru BK</a>
	</div>

	<!-- Success Message -->
	<?php if(isset($_GET['success'])): ?>
		<div style="padding:1rem;background:#dcfce7;border:1px solid #86efac;border-radius:6px;margin-bottom:1.5rem;color:#166534">
			✅ <?= htmlspecialchars($_GET['success']) ?>
		</div>
	<?php endif; ?>

	<!-- Error Message -->
	<?php if(isset($_GET['error'])): ?>
		<div style="padding:1rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;margin-bottom:1.5rem;color:#991b1b">
			❌ <?= htmlspecialchars($_GET['error']) ?>
		</div>
	<?php endif; ?>

	<!-- Table Guru BK -->
	<?php if(mysqli_num_rows($guru_bk_list) > 0): ?>
		<div style="overflow-x:auto">
			<table style="width:100%;border-collapse:collapse">
				<thead style="background:var(--bg-light);border-bottom:2px solid var(--border)">
					<tr>
						<th style="padding:1rem;text-align:left;font-weight:600">#</th>
						<th style="padding:1rem;text-align:left;font-weight:600">Foto</th>
						<th style="padding:1rem;text-align:left;font-weight:600">Nama</th>
						<th style="padding:1rem;text-align:left;font-weight:600">NIP</th>
						<th style="padding:1rem;text-align:left;font-weight:600">No. Telp</th>
						<th style="padding:1rem;text-align:left;font-weight:600">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$no = 1;
					while($row = mysqli_fetch_assoc($guru_bk_list)): 
					?>
						<tr style="border-bottom:1px solid var(--border);transition:all 0.3s" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='transparent'">
							<td style="padding:1rem"><?= $no++ ?></td>
							<td style="padding:1rem">
								<?php if($row['foto']): ?>
									<img src="<?= htmlspecialchars($row['foto']) ?>" alt="<?= htmlspecialchars($row['nama']) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover">
								<?php else: ?>
									<div style="width:40px;height:40px;background:var(--brand);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:1.2rem">👨‍🏫</div>
								<?php endif; ?>
							</td>
							<td style="padding:1rem">
								<strong><?= htmlspecialchars($row['nama']) ?></strong>
							</td>
							<td style="padding:1rem">
								<code style="background:var(--bg-light);padding:0.25rem 0.5rem;border-radius:4px"><?= htmlspecialchars($row['nip']) ?></code>
							</td>
							<td style="padding:1rem"><?= htmlspecialchars($row['no_telp']) ?></td>
							<td style="padding:1rem">
								<div style="display:flex;gap:0.5rem;flex-wrap:wrap">
									<a href="./edit.php?id=<?= $row['id_guru_bk'] ?>" class="btn" style="text-decoration:none;padding:0.5rem 1rem;font-size:0.9rem">✏️ Edit</a>
									<a href="javascript:void(0)" onclick="if(confirm('Yakin hapus guru BK ini?')) window.location.href='../../backend/sekolah/guru_bk/delete.php?id=<?= $row['id_guru_bk'] ?>'" class="btn" style="text-decoration:none;padding:0.5rem 1rem;font-size:0.9rem;background:#ef4444;color:white">🗑️ Hapus</a>
								</div>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<div style="text-align:center;padding:2rem;background:var(--bg-light);border-radius:8px">
			<p style="color:var(--text-light);margin:0">📭 Belum ada guru BK terdaftar</p>
			<a href="./tambah.php" class="btn" style="text-decoration:none;margin-top:1rem;display:inline-block">Tambah Guru BK Pertama</a>
		</div>
	<?php endif; ?>

</section>

<?php include "../../layouts/footer.php"; ?>
