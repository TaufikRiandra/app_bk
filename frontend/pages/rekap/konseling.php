<?php
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

// Get jenis layanan dari database
$layanan_query = mysqli_query($conn, "SELECT * FROM jenis_layanan ORDER BY nama_layanan ASC");
$layanan_list = [];
while($row = mysqli_fetch_assoc($layanan_query)){
	$layanan_list[] = $row;
}

// Get selected layanan dari URL
$selected_layanan_id = intval($_GET['layanan'] ?? 0);
$selected_layanan = null;

if($selected_layanan_id > 0){
	foreach($layanan_list as $l){
		if($l['id_layanan'] == $selected_layanan_id){
			$selected_layanan = $l;
			break;
		}
	}
}
?>

<section class="card">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem"><i class="fas fa-tasks"></i> Input Layanan Manual</h1>
		<p style="color:var(--text-light);margin:0">Pilih jenis konseling/layanan untuk diinputkan</p>
	</div>

	<!-- Layout dengan Sidebar Konseling -->
	<div style="display:grid;grid-template-columns:250px 1fr;gap:2rem;align-items:start">
		
		<!-- Sidebar Kiri: Jenis Konseling -->
		<div style="background:var(--bg-secondary);padding:1.5rem;border-radius:8px;border:1px solid var(--border);height:fit-content;position:sticky;top:2rem">
			<h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Jenis Layanan</h3>
			<ul style="list-style:none;padding:0;margin:0">
				<?php foreach($layanan_list as $layanan): 
					$is_active = ($selected_layanan_id == $layanan['id_layanan']) ? true : false;
					$style = $is_active 
						? 'background:var(--brand);color:white;border-left:4px solid var(--brand)' 
						: 'background:transparent;color:var(--text-dark);border-left:4px solid transparent;transition:all 0.3s';
				?>
					<li style="margin-bottom:0.5rem">
						<a href="?layanan=<?= $layanan['id_layanan'] ?>" 
							style="<?= $style ?>;display:block;padding:0.75rem 1rem;border-radius:6px;text-decoration:none;cursor:pointer;font-weight:500"
							onmouseover="this.style.background = this.style.background === 'var(--brand)' ? 'var(--brand)' : 'var(--bg-light)'; this.style.color = this.style.color === 'white' ? 'white' : 'var(--text-dark)'"
							onmouseout="this.style.background = <?= $is_active ? "'var(--brand)'" : "'transparent'" ?>; this.style.color = <?= $is_active ? "'white'" : "'var(--text-dark)'" ?>">
							<?= htmlspecialchars($layanan['nama_layanan']) ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<!-- Content Kanan -->
		<div>
			<?php if($selected_layanan): ?>
				<!-- Content untuk layanan yang dipilih -->
				<div style="background:var(--surface);padding:2rem;border-radius:8px;border:1px solid var(--border)">
					<div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem">
						<div style="background:var(--brand);color:white;width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem">
							📝
						</div>
						<div>
							<h2 style="margin:0;color:var(--brand);font-size:1.5rem"><?= htmlspecialchars($selected_layanan['nama_layanan']) ?></h2>
							<p style="color:var(--text-light);margin:0.5rem 0 0 0">ID Layanan: <?= $selected_layanan['id_layanan'] ?></p>
						</div>
					</div>

					<div style="padding:1.5rem;background:var(--bg-light);border-radius:6px;border-left:4px solid var(--brand);margin-bottom:2rem">
						<p style="color:var(--text-light);margin:0">
							<i class="fas fa-check-circle"></i> Halaman input untuk layanan <strong><?= htmlspecialchars($selected_layanan['nama_layanan']) ?></strong> akan segera disediakan.
						</p>
					</div>

					<a href="<?= 
						$selected_layanan['id_layanan'] === 5 
							? '/frontend/pages/layanan_mediasi_manual.php' 
							: '#' 
					?>" class="btn" style="text-decoration:none;display:inline-block">
						➕ Input <?= htmlspecialchars($selected_layanan['nama_layanan']) ?>
					</a>
				</div>

			<?php else: ?>
				<!-- Default: tidak ada layanan dipilih -->
				<div style="text-align:center;padding:3rem 2rem;background:var(--bg-light);border-radius:8px;border:2px dashed var(--border)">
					<div style="font-size:3rem;margin-bottom:1rem">👈</div>
					<h3 style="color:var(--text-light);margin:0">Pilih Jenis Layanan</h3>
					<p style="color:var(--text-light);margin:0.5rem 0 0 0">Klik salah satu jenis layanan di sebelah kiri untuk memulai input</p>
				</div>
			<?php endif; ?>
		</div>

	</div>

</section>

<?php include "../layouts/footer.php"; ?>

