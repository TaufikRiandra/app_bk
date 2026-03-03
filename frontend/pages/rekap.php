<?php
// Single session guard and includes to avoid duplicates
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login'])){
	header("Location: /frontend/auth/login.php");
	exit;
}
include "../../backend/config/database.php";
include "../layouts/header.php";
include "../layouts/sidebar.php";

$data = mysqli_query($conn,
"SELECT rekap_layanan.*, siswa.nama_siswa, jenis_layanan.nama_layanan
FROM rekap_layanan
JOIN siswa ON rekap_layanan.id_siswa=siswa.id_siswa
JOIN jenis_layanan ON rekap_layanan.id_layanan=jenis_layanan.id_layanan
");
?>

<section class="card">
	<div style="margin-bottom:2rem">
		<h1 style="margin:0;font-size:1.75rem">📋 Rekap Layanan Bimbingan</h1>
		<p style="color:var(--text-light);margin:0.5rem 0 0 0">Catatan lengkap semua layanan yang telah diberikan kepada siswa</p>
	</div>

	<?php if (mysqli_num_rows($data) > 0): ?>
		<div style="overflow-x:auto">
			<table>
				<thead>
					<tr>
						<th style="width:20%">👥 Nama Siswa</th>
						<th style="width:20%">📝 Jenis Layanan</th>
						<th style="width:15%">📅 Tanggal</th>
						<th style="width:45%">💭 Permasalahan</th>
					</tr>
				</thead>
				<tbody>
					<?php while($r=mysqli_fetch_assoc($data)){ ?>
						<tr>
							<td><strong><?= htmlspecialchars($r['nama_siswa']) ?></strong></td>
							<td><span style="background:rgba(37,99,235,0.08);color:var(--brand);padding:0.25rem 0.75rem;border-radius:4px;font-size:0.9rem"><?= htmlspecialchars($r['nama_layanan']) ?></span></td>
							<td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
							<td style="color:var(--text-light);font-size:0.95rem"><?= htmlspecialchars(substr($r['permasalahan'], 0, 80)) . (strlen($r['permasalahan']) > 80 ? '...' : '') ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<div style="text-align:center;padding:2rem;background:rgba(37,99,235,0.04);border-radius:8px">
			<div style="font-size:2rem;margin-bottom:1rem">📭</div>
			<p style="color:var(--text-light);margin:0">Belum ada data rekap layanan. Mulai dengan menambahkan layanan baru.</p>
		</div>
	<?php endif; ?>
</section>

<?php include "../layouts/footer.php"; ?>