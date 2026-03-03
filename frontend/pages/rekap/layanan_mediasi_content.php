<?php
// Get database connection from global
$conn = $GLOBALS['conn'] ?? null;
if(!$conn) {
	include "../../../backend/config/database.php";
}

$current_month = date('Y-m');
$selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month;
$guru_bk_list = [];
$selected_guru_bk_id = isset($_GET['guru_bk']) ? intval($_GET['guru_bk']) : null;

$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

$result = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");
if ($result) {
	while ($row = mysqli_fetch_assoc($result)) {
		$guru_bk_list[] = $row;
	}
}

$mediasi_data = [];
$selected_guru_bk = null;

if ($selected_guru_bk_id) {
	$guru_query = "SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = $selected_guru_bk_id";
	$guru_result = mysqli_query($conn, $guru_query);
	if ($guru_result && $row = mysqli_fetch_assoc($guru_result)) {
		$selected_guru_bk = $row;
	}

	$query = "SELECT * FROM layanan_mediasi 
		WHERE id_guru_bk = $selected_guru_bk_id 
		AND DATE_FORMAT(tanggal, '%Y-%m') = '$selected_month'
		ORDER BY tanggal DESC";
} else {
	$query = "SELECT lm.*, gb.nama as guru_bk_nama, gb.nip as guru_bk_nip 
		FROM layanan_mediasi lm
		LEFT JOIN guru_bk gb ON lm.id_guru_bk = gb.id_guru_bk
		WHERE DATE_FORMAT(lm.tanggal, '%Y-%m') = '$selected_month'
		ORDER BY lm.tanggal DESC";
}

$result = mysqli_query($conn, $query);
if ($result) {
	while ($row = mysqli_fetch_assoc($result)) {
		$mediasi_data[] = $row;
	}
}

function formatDateIndonesian($dateString, $format = 'F Y') {
	$months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
	list($year, $month) = explode('-', $dateString);
	$month = intval($month);
	return $months_id[$month] . ' ' . $year;
}
?>

<div style="margin-bottom:1.5rem">
	<h2 style="margin-bottom:0.5rem;color:var(--brand)">Laporan Layanan Mediasi</h2>
	<p style="color:var(--text-light);margin:0">Rekap layanan mediasi bimbingan konseling</p>
</div>

<!-- Filter Section -->
<div style="margin-bottom:1.5rem;padding:1.5rem;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border)">
	<label style="font-weight:600;display:block;margin-bottom:0.75rem">Filter:</label>
	<div style="display:flex;gap:0.75rem;align-items:flex-start;flex-wrap:wrap">
		<input type="month" id="filterMonth" value="<?= $selected_month ?>" style="padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem">
		<select id="filterGuruBK" style="padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;min-width:300px">
			<option value="">-- Semua Guru BK --</option>
			<?php foreach ($guru_bk_list as $guru): ?>
				<option value="<?= $guru['id_guru_bk'] ?>" <?= $selected_guru_bk_id === $guru['id_guru_bk'] ? 'selected' : '' ?>>
					<?= htmlspecialchars($guru['nama']) ?> (<?= $guru['nip'] ?>)
				</option>
			<?php endforeach; ?>
		</select>
		<button onclick="applyFilterMediasi()" style="padding:0.75rem 1.5rem;background:var(--brand);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600">
			<i class="fas fa-filter"></i> Filter
		</button>
	</div>
</div>

<!-- Guru BK Info Section (if selected) -->
<?php if ($selected_guru_bk): ?>
<div style="margin-bottom:1.5rem;padding:1rem;background:var(--bg-light);border-left:4px solid var(--brand);border-radius:6px">
	<p style="margin:0;font-size:0.9rem"><strong>GURU BK:</strong> <?= htmlspecialchars($selected_guru_bk['nama']) ?> (<?= $selected_guru_bk['nip'] ?>)</p>
</div>
<?php endif; ?>

<!-- Data Table Section -->
<div style="overflow-x:auto">
	<p style="color:var(--text-light);margin-bottom:1rem"><strong>Periode:</strong> <?= htmlspecialchars(formatDateIndonesian($selected_month)) ?></p>
	<table style="width:100%;border-collapse:collapse;font-size:0.9rem">
		<thead style="background:#CD5C5C;color:black;font-weight:600">
			<tr>
				<th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:40px;color:black">No</th>
				<?php if (!$selected_guru_bk_id): ?>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:100px;color:black">Guru BK</th>
				<?php endif; ?>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:90px;color:black">Tanggal</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Nama Pihak 1</th>
				<th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:60px;color:black">Kelas</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:100px;color:black">Masalah Pihak 1</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Nama Pihak 2</th>
				<th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:60px;color:black">Kelas</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:100px;color:black">Masalah Pihak 2</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:100px;color:black">Hasil Mediasi</th>
				<th style="padding:0.75rem;text-align:center;border:1px solid var(--border);min-width:120px;color:black">Dokumentasi</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			if (empty($mediasi_data)): 
			?>
				<tr>
					<td colspan="<?= $selected_guru_bk_id ? '10' : '11' ?>" style="padding:1.5rem;text-align:center;color:var(--text-light)">
						Belum ada data mediasi untuk periode ini
					</td>
				</tr>
			<?php 
			else:
				$no = 1;
				foreach ($mediasi_data as $mediasi):
					$tanggal = new DateTime($mediasi['tanggal']);
					$tgl = $tanggal->format('d/m/Y');
			?>
				<tr style="border-bottom:1px solid var(--border)">
					<td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= $no++ ?></td>
					<?php if (!$selected_guru_bk_id): ?>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['guru_bk_nama'] ?? '-') ?></td>
					<?php endif; ?>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= $tgl ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['nama_pihak_1'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['kelas_pihak_1'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['masalah_pihak_1'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['nama_pihak_2'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['kelas_pihak_2'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['masalah_pihak_2'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['hasil_mediasi'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important">
						<?php if (!empty($mediasi['foto'])): ?>
							<a href="../../assets/uploads/mediasi/<?= htmlspecialchars($mediasi['foto']) ?>" target="_blank" style="color:var(--brand);text-decoration:none;font-weight:600">
								🖼️ Lihat
							</a>
						<?php else: ?>
							<span style="color:#999">-</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php 
				endforeach;
			endif;
			?>
		</tbody>
	</table>
</div>

<script>
function applyFilterMediasi() {
	const month = document.getElementById('filterMonth').value;
	const guruBK = document.getElementById('filterGuruBK').value;
	
	const currentUrl = new URL(window.location);
	currentUrl.searchParams.set('tab', 'layanan_mediasi');
	currentUrl.searchParams.set('month', month);
	
	if (guruBK) {
		currentUrl.searchParams.set('guru_bk', guruBK);
	} else {
		currentUrl.searchParams.delete('guru_bk');
	}
	
	window.location.href = currentUrl.toString();
}

document.getElementById('filterMonth')?.addEventListener('keypress', function(e) {
	if (e.key === 'Enter') {
		applyFilterMediasi();
	}
});
</script>
