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
} else if ($guru_bk_id > 0) {
    // Export for specific guru_bk (legacy)
    $query = "SELECT * FROM layanan_mediasi 
              WHERE id_guru_bk = $guru_bk_id 
              ORDER BY tanggal DESC";
} else {
    die('Invalid parameters');
}

$result = mysqli_query($conn, $query);
$mediasi_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $mediasi_data[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Layanan Mediasi</title>
    <style>
        @page {
            size: A4 landscape;
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
            font-size: 11px;
            line-height: 1.3;
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
            font-size: 11px;
            margin-bottom: 3px;
        }

        .info-section {
            margin: 10px 0;
            padding: 8px;
            background-color: #F5F5F5;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .info-section strong {
            font-size: 10px;
            color: #666;
        }

        .info-section p {
            margin: 2px 0 0 0;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
        }

        th {
            background-color: #FFC000;
            color: black;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            border: 1px solid #333;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        td {
            padding: 6px;
            border: 1px solid #ddd;
            text-align: left;
        }

        td.col-no {
            text-align: center;
            width: 30px;
        }

        td.col-tgl {
            text-align: center;
            width: 70px;
        }

        td.col-kelas {
            text-align: center;
            width: 45px;
        }

        .signature-section {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .signature-item {
            text-align: center;
        }

        .signature-item p {
            margin: 40px 0 10px 0;
            font-weight: bold;
            font-size: 10px;
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
        <div class="header-title">REKAP LAYANAN MEDIASI</div>
        <div class="header-subtitle">Bimbingan dan Konseling</div>
        <div class="header-subtitle"><?= htmlspecialchars($school_name) ?></div>
    </div>

    <?php if ($guru_bk_id > 0 && $guru): ?>
    <div class="info-section">
        <strong>NAMA / GURU BK:</strong>
        <p><?= htmlspecialchars($guru['nama']) ?> (<?= $guru['nip'] ?>)</p>
    </div>
    <?php endif; ?>

    <?php if ($month): ?>
    <div class="info-section">
        <strong>PERIODE:</strong>
        <p><?php 
            $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            list($year, $m) = explode('-', $month);
            echo htmlspecialchars($months_id[intval($m)] . ' ' . $year);
        ?></p>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tgl">Tanggal</th>
                <th>Nama Pihak 1</th>
                <th class="col-kelas">Kelas</th>
                <th>Masalah Pihak 1</th>
                <th>Nama Pihak 2</th>
                <th class="col-kelas">Kelas</th>
                <th>Masalah Pihak 2</th>
                <th>Hasil Mediasi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (empty($mediasi_data)):
            ?>
                <tr>
                    <td colspan="10" style="text-align: center; color: #999; padding: 15px;">Belum ada data mediasi</td>
                </tr>
            <?php 
            else:
                $no = 1;
                foreach ($mediasi_data as $mediasi):
                    $tanggal = new DateTime($mediasi['tanggal']);
                    $tgl = $tanggal->format('d/m/Y');
            ?>
                <tr>
                    <td class="col-no"><?= $no++ ?></td>
                    <td class="col-tgl"><?= $tgl ?></td>
                    <td><?= htmlspecialchars($mediasi['nama_pihak_1'] ?? '') ?: '-' ?></td>
                    <td class="col-kelas"><?= htmlspecialchars($mediasi['kelas_pihak_1'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['masalah_pihak_1'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['nama_pihak_2'] ?? '') ?: '-' ?></td>
                    <td class="col-kelas"><?= htmlspecialchars($mediasi['kelas_pihak_2'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['masalah_pihak_2'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['hasil_mediasi'] ?? '') ?: '-' ?></td>
                    <td><?= htmlspecialchars($mediasi['keterangan'] ?? '') ?: '-' ?></td>
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
            <?php if ($guru_bk_id > 0 && $guru): ?>
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
