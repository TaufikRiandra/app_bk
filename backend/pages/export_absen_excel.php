<?php
// Excel Export using HTML table format
include "../config/database.php";
if(session_status()===PHP_SESSION_NONE)session_start();
require_once "../config/auth_helper.php";
if(!isset($_SESSION["login"]))die("Akses ditolak.");

$kelas = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : '';
if(!isAdmin()&&!canAccessKelas($conn,$kelas)){die("Akses ditolak: Anda tidak memiliki izin untuk kelas ini.");}
$semester = isset($_GET['semester']) ? htmlspecialchars($_GET['semester']) : '';

if(!$kelas || !$semester) {
	die('Invalid parameters');
}

// Ambil data sekolah
$sekolah_query = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$sekolah = mysqli_fetch_assoc($sekolah_query);
$nama_sekolah = $sekolah['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

// Tentukan bulan berdasarkan semester
if($semester === '1') {
	$months = ['07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
	$semester_text = 'Semester 1 (Juli - Desember)';
	$tahun = 2026;
} else {
	$months = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni'];
	$semester_text = 'Semester 2 (Januari - Juni)';
	$tahun = 2026;
}

// Ambil semua siswa di kelas ini
$kelas_escaped = mysqli_real_escape_string($conn, $kelas);
$siswa_query = mysqli_query($conn, "SELECT id_siswa, nis, nama_siswa FROM siswa WHERE kelas = '$kelas_escaped' ORDER BY CAST(SUBSTRING_INDEX(nis, '-', -1) AS UNSIGNED) ASC");
$siswa_list = [];
while($row = mysqli_fetch_assoc($siswa_query)) {
	$siswa_list[] = $row;
}

// Excel HTML output
$html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns="http://www.w3.org/TR/REC-html40">
<head>
	<meta charset="UTF-8">
	<style>
		body { font-family: Calibri; }
		table { border-collapse: collapse; }
		th, td { border: 1px solid #000; text-align: center; }
		.header-month th { background-color: #4472C4; color: white; font-weight: bold; }
		.header-status th { background-color: #4472C4; color: white; font-weight: bold; }
		.data-blue-primary { background-color: #4472C4; color: white; font-weight: bold; }
		.data-blue-secondary { background-color: #6BA3D6; color: white; font-weight: bold; }
		.data-yellow { background-color: #FFC000; color: black; font-weight: bold; }
		.data-gray { background-color: #B4B4B4; color: black; font-weight: bold; }
		.col-no { width: 40px; background-color: #4472C4; font-weight: bold; color: white; }
		.col-name { width: 150px; text-align: left; }
		th.col-name { background-color: #4472C4; color: white; font-weight: bold; }
	</style>
</head>
<body>';

$html .= '<table>';

// Header info
$html .= '<tr><td colspan="' . (2 + (count($months) * 6)) . '" style="background-color: #4472C4; color: white; font-size: 14px; font-weight: bold; padding: 15px;">
	REKAP ABSEN KELAS ' . htmlspecialchars($kelas) . '
</td></tr>';

$html .= '<tr><td colspan="' . (2 + (count($months) * 6)) . '" style="padding: 10px;">' . $semester_text . '</td></tr>';

$html .= '<tr><td colspan="' . (2 + (count($months) * 6)) . '" style="padding: 10px;">Nama Sekolah: ' . htmlspecialchars($nama_sekolah) . '</td></tr>';

$html .= '<tr><td colspan="' . (2 + (count($months) * 6)) . '">&nbsp;</td></tr>';

// Month header row
$html .= '<tr class="header-month">';
$html .= '<th rowspan="2" class="col-no">NO</th>';
$html .= '<th rowspan="2" class="col-name">NAMA</th>';

foreach($months as $bulan_kode => $bulan_nama) {
	$html .= '<th colspan="6" style="background-color: #4472C4; color: white;">' . htmlspecialchars($bulan_nama) . '</th>';
}

$html .= '</tr>';

// Status code header row
$html .= '<tr class="header-status">';

foreach($months as $bulan_kode => $bulan_nama) {
	foreach(['H', 'I', 'S', 'A', 'C', 'T'] as $code) {
		$html .= '<th style="width: 40px;">' . $code . '</th>';
	}
}

$html .= '</tr>';

// Data rows
$no = 1;
foreach($siswa_list as $siswa) {
	$id_siswa = $siswa['id_siswa'];
	$html .= '<tr>';
	$html .= '<td class="col-no">' . $no . '</td>';
	$html .= '<td class="col-name">' . htmlspecialchars($siswa['nama_siswa']) . '</td>';
	
	$bulan_index = 0;
	foreach($months as $bulan_kode => $bulan_nama) {
		// Tentukan warna berdasarkan nomor baris dan index bulan
		if($no <= 30) {
			// Baris 1-30: Kuning-Abu solid
			$month_bg_class = $bulan_index % 2 == 0 ? 'data-yellow' : 'data-gray';
		} else {
			// Baris 31+: Biru solid
			$month_bg_class = $bulan_index % 2 == 0 ? 'data-blue-primary' : 'data-blue-secondary';
		}
		$bulan_index++;
		
		$tanggal_awal = "$tahun-$bulan_kode-01";
		if($bulan_kode == '12') {
			$tanggal_akhir = ($tahun + 1) . "-01-01";
		} else {
			$next_bulan = str_pad((int)$bulan_kode + 1, 2, '0', STR_PAD_LEFT);
			$tanggal_akhir = "$tahun-$next_bulan-01";
		}
		
		$absen_query = mysqli_query($conn, "
			SELECT keterangan, COUNT(*) as jumlah 
			FROM absen_siswa 
			WHERE id_siswa = $id_siswa 
			AND tanggal >= '$tanggal_awal' 
			AND tanggal < '$tanggal_akhir'
			GROUP BY keterangan
		");
		
		$absen_data = [];
		while($absen_row = mysqli_fetch_assoc($absen_query)) {
			$absen_data[$absen_row['keterangan']] = $absen_row['jumlah'];
		}
		
		// Display setiap status dengan nilai 0 jika tidak ada
		foreach(['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alfa', 'C' => 'Cabut', 'T' => 'Terlambat'] as $code => $status) {
			$value = isset($absen_data[$status]) ? $absen_data[$status] : 0;
			
			$html .= '<td class="' . $month_bg_class . '">' . $value . '</td>';
		}
	}
	
	$html .= '</tr>';
	$no++;
}

$html .= '</table></body></html>';

// Generate Excel as HTML (Excel format)
$filename = 'Rekap_Absen_' . $kelas . '_' . date('YmdHis') . '.xls';
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Type: application/vnd.ms-excel; charset=utf-8");

echo $html;
exit;
?>
