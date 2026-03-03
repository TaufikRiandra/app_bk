<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['login'])){
    header("Location: ../../frontend/auth/login.php");
    exit;
}

// Function to convert month to Indonesian
function formatDateIndonesian($dateString, $format = 'F Y') {
    $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    list($year, $month) = explode('-', $dateString);
    $month = intval($month);
    return $months_id[$month] . ' ' . $year;
}

$guru_bk_id = intval($_GET['guru_bk'] ?? 0);
$month = $_GET['month'] ?? null;

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

// Initialize guru variable
$guru = null;

// Get guru info if guru_bk_id is set
if ($guru_bk_id > 0) {
    $guru_result = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = $guru_bk_id");
    if ($guru_result) {
        $guru = mysqli_fetch_assoc($guru_result);
    }
}

if ($month) {
    // Export for month-based report
    $query = "SELECT kh.*, gb.nama as guru_bk_nama, gb.nip as guru_bk_nip 
              FROM kegiatan_harian kh
              LEFT JOIN guru_bk gb ON kh.id_guru_bk = gb.id_guru_bk
              WHERE DATE_FORMAT(kh.tanggal, '%Y-%m') = '$month'";
    
    if ($guru_bk_id > 0) {
        $query .= " AND kh.id_guru_bk = $guru_bk_id";
    }
    
    $query .= " ORDER BY kh.tanggal DESC, kh.waktu_mulai ASC";
    $filename_prefix = "Rekap_Kegiatan_" . $month;
} else if ($guru_bk_id > 0) {
    // Export for specific guru_bk (legacy)
    $query = "SELECT * FROM kegiatan_harian 
              WHERE id_guru_bk = $guru_bk_id 
              ORDER BY tanggal DESC, waktu_mulai ASC";
    $filename_prefix = "Rekap_Kegiatan_Harian_" . str_replace(' ', '_', $guru['nama']) . '_' . date('Y-m-d');
} else {
    die('Invalid parameters');
}

$result = mysqli_query($conn, $query);
$kegiatan_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kegiatan_data[] = $row;
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename_prefix . '.xls"');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { 
            background-color: #FFC000; 
            font-weight: bold; 
            color: black;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        .header-info { margin: 20px 0 10px 0; }
        .header-title { font-size: 14px; font-weight: bold; margin: 10px 0; }
        .header-subtitle { font-size: 12px; margin: 5px 0; }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <div class="header-title">KEGIATAN HARIAN</div>
        <div class="header-subtitle">Bimbingan dan Konseling</div>
        <div class="header-subtitle"><?= htmlspecialchars($school_name) ?></div>
    </div>

    <?php if ($guru_bk_id > 0): ?>
    <div class="header-info">
        <div><strong>NAMA / GURU BK:</strong> <?= htmlspecialchars($guru['nama']) ?></div>
        <div><strong>NIP:</strong> <?= $guru['nip'] ?></div>
        <?php if ($month): ?>
        <div><strong>PERIODE:</strong> <?php 
            echo htmlspecialchars(formatDateIndonesian($month));
        ?></div>
        <?php endif; ?>
    </div>
    <?php elseif ($month): ?>
    <div class="header-info">
        <div><strong>PERIODE:</strong> <?php 
            echo htmlspecialchars(formatDateIndonesian($month));
        ?></div>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr style="background-color: #FFC000;">
                <th style="background-color: #FFC000; color: black;">No</th>
                <th style="background-color: #FFC000; color: black;">Hari/Tgl</th>
                <th style="background-color: #FFC000; color: black;">Waktu Mulai</th>
                <th style="background-color: #FFC000; color: black;">Waktu Selesai</th>
                <th style="background-color: #FFC000; color: black;">Uraian Kegiatan</th>
                <th style="background-color: #FFC000; color: black;">Jenis Layanan</th>
                <th style="background-color: #FFC000; color: black;">Sasaran Layanan</th>
                <th style="background-color: #FFC000; color: black;">Bidang/Kode Layanan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($kegiatan_data as $kegiatan):
                $tanggal = new DateTime($kegiatan['tanggal']);
                $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$tanggal->format('w')];
                $tgl = $tanggal->format('d/m/Y');
                $hari_tgl = $hari . ', ' . $tgl;
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $hari_tgl ?></td>
                    <td><?= $kegiatan['waktu_mulai'] ?: '-' ?></td>
                    <td><?= $kegiatan['waktu_selesai'] ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['uraian_kegiatan'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['jenis_layanan'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['sasaran_layanan'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['bidang_kode_layanan'] ?? '') ?: '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 40px; text-align: right;">
        <div style="margin: 20px 0;">Solok Selatan, <?php 
            $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $day = date('d');
            $month_num = intval(date('m'));
            $year = date('Y');
            echo $day . ' ' . $months_id[$month_num] . ' ' . $year;
        ?></div>
        <?php if ($guru): ?>
        <div style="margin-top: 80px;"><?= htmlspecialchars($guru['nama'] ?? '') ?> (<?= $guru['nip'] ?? '' ?>)</div>
        <?php endif; ?>
    </div>
</body>
</html>
