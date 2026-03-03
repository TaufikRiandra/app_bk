<?php
// Get database connection from global
$conn = $GLOBALS['conn'] ?? null;
if(!$conn) {
	include "../../../backend/config/database.php";
}

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

<div style="margin-bottom:1.5rem">
	<h2 style="margin-bottom:0.5rem;color:var(--brand)">Rekap Konseling</h2>
	<p style="color:var(--text-light);margin:0">Pilih jenis konseling/layanan untuk melihat rekap</p>
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
					? 'background:#E8E4FF;color:var(--brand);border-left:4px solid var(--brand)' 
					: 'background:transparent;color:var(--text-dark);border-left:4px solid transparent';
			?>
				<li style="margin-bottom:0.5rem">
					<a href="<?= isset($_GET['tab']) ? '?tab=' . $_GET['tab'] . '&' : '?' ?>layanan=<?= $layanan['id_layanan'] ?>" 
						style="<?= $style ?>;display:block;padding:0.75rem 1rem;border-radius:6px;text-decoration:none;cursor:pointer;font-weight:500">
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
						<i class="fas fa-check-circle"></i> Rekap untuk layanan <strong><?= htmlspecialchars($selected_layanan['nama_layanan']) ?></strong> akan ditampilkan di sini.
					</p>
				</div>
			</div>

		<?php else: ?>
			<!-- Default: tidak ada layanan dipilih -->
			<div style="text-align:center;padding:3rem 2rem;background:var(--bg-light);border-radius:8px;border:2px dashed var(--border)">
				<div style="font-size:3rem;margin-bottom:1rem">👈</div>
				<h3 style="color:var(--text-light);margin:0">Pilih Jenis Layanan</h3>
				<p style="color:var(--text-light);margin:0.5rem 0 0 0">Klik salah satu jenis layanan di sebelah kiri untuk melihat rekap</p>
			</div>
		<?php endif; ?>
	</div>

</div>
