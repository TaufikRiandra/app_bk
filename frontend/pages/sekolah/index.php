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

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? '';

$data = mysqli_query($conn,"SELECT * FROM sekolah");
?>

    <div style="background:var(--brand,#4472C4);color:white;padding:1.25rem 1.5rem;border-radius:8px;margin-bottom:1.25rem">
		<div>
        	<h2 style="margin:0 0 4px;font-size:1.1rem;font-weight:700">
            	<i class="fas fa-school" style="margin-right:8px"></i>DATA SEKOLAH</h2>
        	<p style="margin:0;font-size:.85rem;opacity:.9">Bimbingan dan Konseling</p>
        	<p><?= htmlspecialchars($school_name) ?></p>
		</div>
		<div style="display:flex;gap:0.75rem;flex-wrap:wrap">

		</div>
    </div>

<section class="card">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
		<div style="display:flex;gap:0.75rem;flex-wrap:wrap">
			<a href="./kelas.php" class="btn" style="white-space:nowrap"><i class="fa-solid fa-people-roof"></i>Data Kelas</a>
			<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
				<a href="../guru_bk/index.php" class="btn" style="white-space:nowrap"><i class="fa-solid fa-chalkboard-user"></i>Kelola Guru BK</a>
				<a href="../guru_bk/bk.php" class="btn" style="white-space:nowrap"><i class="fa-solid fa-link"></i>Tetapkan BK</a>
			<?php endif; ?>
			<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'developer'): ?>
				<a href="./tambah.php" class="btn" style="white-space:nowrap"><i class="fa-solid fa-plus"></i>Tambah Sekolah</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if (mysqli_num_rows($data) > 0): ?>
		<table>
			<thead>
				<tr>
					<th style="width:30%"><i class="fas fa-school" style="margin-right:8px"></i>Nama Sekolah</th>
					<th style="width:25%"><i class="fas fa-user" style="margin-right:8px"></i>Kepala Sekolah</th>
					<th style="width:20%"><i class="fas fa-calendar" style="margin-right:8px"></i>Tahun Ajaran</th>
					<th style="width:30%;text-align:center"><i class="fas fa-cogs" style="margin-right:8px"></i>Aksi</th>
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
								<a href="./edit.php?id=<?= $r['id_sekolah'] ?>" style="color:var(--brand);text-decoration:none;margin-right:1rem;font-weight:500" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
									<i class="fas fa-edit"></i> Edit
								</a>
							<?php endif; ?>
							<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'developer'): ?>
								<a href="./edit.php?id=<?= $r['id_sekolah'] ?>" style="color:var(--brand);text-decoration:none;margin-right:1rem;font-weight:500" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
									<i class="fas fa-edit"></i> Edit
								</a>
								<a href="../../../backend/pages/sekolah/delete.php?id=<?= $r['id_sekolah'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn-delete">
									<i class="fas fa-trash"></i> Hapus
								</a>
							<?php endif; ?>
							<?php if($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer'): ?>
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

