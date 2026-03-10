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
} else if ($guru_bk_id > 0) {
    // Export for specific guru_bk (legacy)
    $query = "SELECT * FROM kegiatan_harian 
              WHERE id_guru_bk = $guru_bk_id 
              ORDER BY tanggal DESC, waktu_mulai ASC";
} else {
    die('Invalid parameters');
}

$result = mysqli_query($conn, $query);
$kegiatan_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kegiatan_data[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Kegiatan Harian</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header-subtitle {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 15px 0;
            padding: 10px;
            background-color: #F5F5F5;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .info-item strong {
            font-size: 11px;
            color: #666;
        }

        .info-item p {
            margin: 3px 0 0 0;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th {
            background-color: #FFC000;
            color: black;
            font-weight: bold;
            padding: 10px;
            text-align: left;
            border: 1px solid #333;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        th.col-no {
            width: 40px;
            text-align: center;
        }

        th.col-date {
            width: 120px;
        }

        th.col-time {
            width: 70px;
            text-align: center;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        td.col-no {
            text-align: center;
            width: 40px;
        }

        td.col-time {
            text-align: center;
            width: 70px;
        }

        tr {
            page-break-inside: avoid;
        }

        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .signature-item {
            text-align: center;
        }

        .signature-item p {
            margin: 50px 0 10px 0;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">KEGIATAN HARIAN BIMBINGAN DAN KONSELING</div>
        <div class="header-subtitle"><?= htmlspecialchars($school_name) ?></div>
    </div>

    <?php if ($guru_bk_id > 0): ?>
    <div class="info-section">
        <div class="info-item">
            <strong>NAMA / GURU BK:</strong>
            <p><?= htmlspecialchars($guru['nama']) ?></p>
        </div>
        <div class="info-item">
            <strong>NIP:</strong>
            <p><?= $guru['nip'] ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($month): ?>
    <div class="info-section">
        <div class="info-item">
            <strong>PERIODE:</strong>
            <p><?php 
                echo htmlspecialchars(formatDateIndonesian($month));
            ?></p>
        </div>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-date">Hari/Tgl</th>
                <th class="col-time">Waktu Mulai</th>
                <th class="col-time">Waktu Selesai</th>
                <th>Uraian Kegiatan</th>
                <th>Jenis Layanan</th>
                <th>Sasaran Layanan</th>
                <th>Bidang/Kode Layanan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (empty($kegiatan_data)):
            ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #999; padding: 20px;">Belum ada data kegiatan</td>
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
                <tr>
                    <td class="col-no"><?= $no++ ?></td>
                    <td class="col-date"><?= $hari_tgl ?></td>
                    <td class="col-time"><?= $kegiatan['waktu_mulai'] ?: '-' ?></td>
                    <td class="col-time"><?= $kegiatan['waktu_selesai'] ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['uraian_kegiatan'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['jenis_layanan'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['sasaran_layanan'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($kegiatan['bidang_kode_layanan'] ?? '') ?: '-' ?></td>
                </tr>
            <?php 
                endforeach;
            endif;
            ?>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-item">
            <p>Mengetahui,</p>
            <p>Kepala Sekolah</p>
        </div>
        <div class="signature-item">
            <p>Solok Selatan, <?php 
                $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $day = date('d');
                $month_num = intval(date('m'));
                $year = date('Y');
                echo $day . ' ' . $months_id[$month_num] . ' ' . $year;
            ?></p>
            <?php if ($guru_bk_id > 0): ?>
            <p><?= htmlspecialchars($guru['nama']) ?> (<?= $guru['nip'] ?>)</p>
            <?php else: ?>
            <p>Guru Bimbingan Konseling</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
