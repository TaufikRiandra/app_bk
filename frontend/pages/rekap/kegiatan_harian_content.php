<?php
// Get database connection from global
$conn = $GLOBALS['conn'] ?? null;
if(!$conn) {
	include "../../../backend/config/database.php";
}

// Function to convert month to Indonesian
function formatDateIndonesian($dateString, $format = 'F Y') {
	$months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
	list($year, $month) = explode('-', $dateString);
	$month = intval($month);
	return $months_id[$month] . ' ' . $year;
}

$user_role = $_SESSION['role'] ?? 'guru_bk';
$current_month = date('Y-m');

// Get selected month from URL or use current month
$selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month;

// Get list of guru BK (for filter)
$guru_bk_list = [];
$selected_guru_bk_id = isset($_GET['guru_bk']) ? intval($_GET['guru_bk']) : null;

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

$result = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");
if ($result) {
	while ($row = mysqli_fetch_assoc($result)) {
		$guru_bk_list[] = $row;
	}
}

// Get kegiatan data for selected month
$kegiatan_data = [];
$selected_guru_bk = null;

if ($selected_guru_bk_id) {
	$guru_query = "SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = $selected_guru_bk_id";
	$guru_result = mysqli_query($conn, $guru_query);
	if ($guru_result && $row = mysqli_fetch_assoc($guru_result)) {
		$selected_guru_bk = $row;
	}

	$query = "SELECT * FROM kegiatan_harian 
		WHERE id_guru_bk = $selected_guru_bk_id 
		AND DATE_FORMAT(tanggal, '%Y-%m') = '$selected_month'
		ORDER BY tanggal DESC, waktu_mulai ASC";
} else {
	$query = "SELECT kh.*, gb.nama as guru_bk_nama, gb.nip as guru_bk_nip 
		FROM kegiatan_harian kh
		LEFT JOIN guru_bk gb ON kh.id_guru_bk = gb.id_guru_bk
		WHERE DATE_FORMAT(kh.tanggal, '%Y-%m') = '$selected_month'
		ORDER BY kh.tanggal DESC, kh.waktu_mulai ASC";
}

$result = mysqli_query($conn, $query);
if ($result) {
	while ($row = mysqli_fetch_assoc($result)) {
		$kegiatan_data[] = $row;
	}
}
?>

<div style="margin-bottom:1.5rem">
	<h2 style="margin-bottom:0.5rem;color:var(--brand)">Laporan Kegiatan Harian Bulanan</h2>
	<p style="color:var(--text-light);margin:0">Rekap kegiatan harian bimbingan konseling</p>
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
		<button onclick="applyFilterKegiatan()" style="padding:0.75rem 1.5rem;background:var(--brand);color:black;border:none;border-radius:6px;cursor:pointer;font-weight:600">
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
		<thead style="background:#FFC000;color:black;font-weight:600">
			<tr>
				<th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:40px;color:black">No</th>
				<?php if (!$selected_guru_bk_id): ?>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Guru BK</th>
				<?php endif; ?>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:100px;color:black">Hari/Tgl</th>
				<th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:80px;color:black">Waktu Mulai</th>
				<th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:80px;color:black">Waktu Selesai</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:200px;color:black">Uraian Kegiatan</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:150px;color:black">Jenis Layanan</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:150px;color:black">Sasaran Layanan</th>
				<th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:150px;color:black">Bidang/Kode Layanan</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			if (empty($kegiatan_data)): 
			?>
				<tr>
					<td colspan="<?= $selected_guru_bk_id ? '8' : '9' ?>" style="padding:1.5rem;text-align:center;color:var(--text-light)">Belum ada data kegiatan untuk periode ini</td>
				</tr>
			<?php 
			else:
				$no = 1;
				foreach ($kegiatan_data as $kegiatan):
					$tanggal = new DateTime($kegiatan['tanggal']);
					$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$tanggal->format('w')];
					$tgl = $tanggal->format('d/m/Y');
					$hari_tgl = $hari . ', ' . $tgl;
			?>
				<tr style="border-bottom:1px solid var(--border)">
					<td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= $no++ ?></td>
					<?php if (!$selected_guru_bk_id): ?>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($kegiatan['guru_bk_nama'] ?? '-') ?></td>
					<?php endif; ?>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= $hari_tgl ?></td>
					<td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= $kegiatan['waktu_mulai'] ?: '-' ?></td>
					<td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= $kegiatan['waktu_selesai'] ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($kegiatan['uraian_kegiatan'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($kegiatan['jenis_layanan'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($kegiatan['sasaran_layanan'] ?? '') ?: '-' ?></td>
					<td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($kegiatan['bidang_kode_layanan'] ?? '') ?: '-' ?></td>
				</tr>
			<?php 
				endforeach;
			endif;
			?>
		</tbody>
	</table>
</div>

		<div style="padding:1.5rem;text-align:center;border-top:1px solid var(--border);display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
			<button onclick="exportToExcel()" style="padding:0.75rem 1.5rem;background:linear-gradient(135deg, #27ae60 0%, #229954 100%);color:var(--text);border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 12px rgba(39, 174, 96, 0.3)">
				<i class="fas fa-file-excel"></i> Export Excel
			</button>
			<button onclick="exportToPDF()" style="padding:0.75rem 1.5rem;background:linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);color:var(--text);border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 12px rgba(231, 76, 60, 0.3)">
				<i class="fas fa-file-pdf"></i> Export PDF
			</button>
			<button onclick="window.history.pushState({}, '', '?tab=kegiatan_harian');window.location.reload()" style="padding:0.75rem 1.5rem;background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border);border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
				<i class="fas fa-arrow-left"></i> Kembali
			</button>
		</div>

<script>

		function exportToExcel() {
			window.location.href = '../../../backend/pages/export_kegiatan_excel.php';
		}

		function exportToPDF() {
			window.open('../../../backend/pages/export_kegiatan_pdf.php');
		}

function applyFilterKegiatan() {
	const month = document.getElementById('filterMonth').value;
	const guruBK = document.getElementById('filterGuruBK').value;
	
	const currentUrl = new URL(window.location);
	currentUrl.searchParams.set('tab', 'kegiatan_harian');
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
		applyFilterKegiatan();
	}
});
</script>
