<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['login'])){
    header("Location: ../../frontend/auth/login.php");
    exit;
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
    $query = "SELECT lm.*, gb.nama as guru_bk_nama, gb.nip as guru_bk_nip 
              FROM layanan_mediasi lm
              LEFT JOIN guru_bk gb ON lm.id_guru_bk = gb.id_guru_bk
              WHERE DATE_FORMAT(lm.tanggal, '%Y-%m') = '$month'";
    
    if ($guru_bk_id > 0) {
        $query .= " AND lm.id_guru_bk = $guru_bk_id";
    }
    
    $query .= " ORDER BY lm.tanggal DESC";
    $filename_prefix = "Rekap_Mediasi_" . $month;
} else if ($guru_bk_id > 0) {
    // Export for specific guru_bk (legacy)
    $query = "SELECT * FROM layanan_mediasi 
              WHERE id_guru_bk = $guru_bk_id 
              ORDER BY tanggal DESC";
    $filename_prefix = "Rekap_Mediasi_" . str_replace(' ', '_', $guru['nama']) . '_' . date('Y-m-d');
} else {
    die('Invalid parameters');
}

$result = mysqli_query($conn, $query);
$mediasi_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $mediasi_data[] = $row;
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
        <div class="header-title">LAYANAN MEDIASI</div>
        <div class="header-subtitle">Bimbingan dan Konseling</div>
        <div class="header-subtitle"><?= htmlspecialchars($school_name) ?></div>
    </div>

    <?php if ($guru_bk_id > 0): ?>
    <div class="header-info">
        <div><strong>NAMA / GURU BK:</strong> <?= htmlspecialchars($guru['nama'] ?? '') ?> (<?= $guru['nip'] ?? '' ?>)</div>
        <?php if ($month): ?>
        <div><strong>PERIODE:</strong> <?php 
            $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            list($year, $m) = explode('-', $month);
            echo htmlspecialchars($months_id[intval($m)] . ' ' . $year);
        ?></div>
        <?php endif; ?>
    </div>
    <?php elseif ($month): ?>
    <div class="header-info">
        <div><strong>PERIODE:</strong> <?php 
            $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            list($year, $m) = explode('-', $month);
            echo htmlspecialchars($months_id[intval($m)] . ' ' . $year);
        ?></div>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr style="background-color: #FFC000;">
                <th style="background-color: #FFC000; color: black;">No</th>
                <th style="background-color: #FFC000; color: black;">Tanggal</th>
                <th style="background-color: #FFC000; color: black;">Nama Pihak 1</th>
                <th style="background-color: #FFC000; color: black;">Kelas</th>
                <th style="background-color: #FFC000; color: black;">Masalah Pihak 1</th>
                <th style="background-color: #FFC000; color: black;">Nama Pihak 2</th>
                <th style="background-color: #FFC000; color: black;">Kelas</th>
                <th style="background-color: #FFC000; color: black;">Masalah Pihak 2</th>
                <th style="background-color: #FFC000; color: black;">Hasil Mediasi</th>
                <th style="background-color: #FFC000; color: black;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($mediasi_data as $mediasi):
                $tanggal = new DateTime($mediasi['tanggal']);
                $tgl = $tanggal->format('d/m/Y');
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $tgl ?></td>
                    <td><?= htmlspecialchars($mediasi['nama_pihak_1'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['kelas_pihak_1'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['masalah_pihak_1'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['nama_pihak_2'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['kelas_pihak_2'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['masalah_pihak_2'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['hasil_mediasi'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['keterangan'] ?? '') ?: '-' ?></td>
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
