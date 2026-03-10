<?php
// PDF Export - Generate printable HTML that can be saved as PDF
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

// Set header untuk menampilkan HTML di browser (bukan download)
header('Content-Type: text/html; charset=utf-8');

// Generate HTML content dengan print stylesheet
$html = '<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rekap Absen Kelas ' . htmlspecialchars($kelas) . '</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		body {
			font-family: "Segoe UI", Arial, sans-serif;
			font-size: 11px;
			padding: 10px;
		}
		.header {
			text-align: center;
			margin-bottom: 20px;
			border-bottom: 2px solid #333;
			padding-bottom: 15px;
		}
		.header h1 {
			font-size: 18px;
			margin-bottom: 8px;
			font-weight: bold;
		}
		.header p {
			font-size: 12px;
			margin: 4px 0;
		}
		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 20px;
		}
		th {
			background-color: #4472C4;
			color: white;
			border: 1px solid #000;
			padding: 8px;
			text-align: center;
			font-weight: bold;
			font-size: 10px;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		td {
			border: 1px solid #000;
			padding: 6px;
			text-align: center;
			font-size: 10px;
		}
		.col-no {
			width: 35px;
			background-color: #4472C4;
			color: white;
			font-weight: bold;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		.col-name {
			width: 120px;
			text-align: left;
			background-color: #f9f9f9;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		th.col-name {
			background-color: #4472C4;
			color: white;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		tr:nth-child(odd) td.col-name {
			background-color: #f9f9f9;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		tr:nth-child(even) td.col-name {
			background-color: #ffffff;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		.data-blue-primary {
			background-color: #4472C4;
			color: white;
			font-weight: bold;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		.data-blue-secondary {
			background-color: #6BA3D6;
			color: white;
			font-weight: bold;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		.data-yellow {
			background-color: #FFC000;
			color: black;
			font-weight: bold;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		.data-gray {
			background-color: #B4B4B4;
			color: black;
			font-weight: bold;
			print-color-adjust: exact;
			-webkit-print-color-adjust: exact;
		}
		@media print {
			body {
				margin: 0;
				padding: 5mm;
			}
			.header {
				page-break-after: avoid;
			}
			table {
				page-break-inside: avoid;
			}
			* {
				print-color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
			}
		}
		.footer {
			text-align: center;
			margin-top: 30px;
			font-size: 10px;
			color: #666;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>REKAP ABSEN KELAS ' . htmlspecialchars($kelas) . '</h1>
		<p><strong>' . $semester_text . '</strong></p>
		<p>Nama Sekolah: ' . htmlspecialchars($nama_sekolah) . '</p>
	</div>
	
	<table>
		<!-- Month header row -->
		<tr>
			<th class="col-no" rowspan="2">NO</th>
			<th class="col-name" rowspan="2">NAMA</th>';

// Add month headers
foreach($months as $bulan_kode => $bulan_nama) {
	$html .= '<th colspan="6">' . htmlspecialchars($bulan_nama) . '</th>';
}

$html .= '</tr>';

// Status code header row
$html .= '<tr>';

foreach($months as $bulan_kode => $bulan_nama) {
	foreach(['H', 'I', 'S', 'A', 'C', 'T'] as $code) {
		$html .= '<th style="width: 28px;">' . $code . '</th>';
	}
}

$html .= '</tr>';

// Add data rows
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
		
		// Display setiap status dengan nilai
		foreach(['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alfa', 'C' => 'Cabut', 'T' => 'Terlambat'] as $code => $status) {
			$value = isset($absen_data[$status]) ? $absen_data[$status] : 0;
			
			$html .= '<td class="' . $month_bg_class . '">' . $value . '</td>';
		}
	}
	
	$html .= '</tr>';
	$no++;
}

$html .= '
	</table>
	
	<div class="footer">
		<p>Tanggal cetak: ' . date('d-m-Y H:i:s') . '</p>
		<p><em>Dokumen ini dapat disimpan sebagai PDF menggunakan fitur Print to PDF di browser Anda</em></p>
	</div>
	
	<script>
		window.print();
	</script>
</body>
</html>';

echo $html;
?>
