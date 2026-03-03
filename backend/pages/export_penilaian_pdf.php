<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

include '../config/database.php';

$kelas = $_GET['kelas'] ?? '';
$jumlah_tugas = intval($_GET['jumlah_tugas'] ?? 0);
$mode = $_GET['mode'] ?? 'generate';

if (!$kelas) {
    die('Kelas tidak valid');
}

$kelas_escaped = mysqli_real_escape_string($conn, $kelas);

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

// Determine mode and get data
if ($mode === 'saved') {
    // For saved penilaian, get from database WITHOUT requiring jumlah_tugas in URL
    // Get max jumlah_tugas from penilaian table
    $max_query = "SELECT MAX(CAST(jumlah_tugas AS UNSIGNED)) as max_tugas FROM penilaian p
                  INNER JOIN siswa s ON p.id_siswa = s.id_siswa
                  WHERE s.kelas = '$kelas_escaped'";
    $max_result = mysqli_query($conn, $max_query);
    $max_row = mysqli_fetch_assoc($max_result);
    $jumlah_tugas = intval($max_row['max_tugas'] ?? 5);
} else {
    // For generate mode, must use jumlah_tugas from URL
    if ($jumlah_tugas < 1) {
        die('Jumlah tugas tidak valid');
    }
}

// Get siswa data
if ($mode === 'saved') {
    // For saved mode: get scores from penilaian table
    $query = "SELECT s.id_siswa, s.nis, s.nama_siswa, s.jk, 
                     COALESCE(p.scores, '[]') as scores
              FROM siswa s
              LEFT JOIN penilaian p ON s.id_siswa = p.id_siswa
              WHERE s.kelas = '$kelas_escaped'
              ORDER BY s.nis";
} else {
    // For generate mode: get only siswa (no scores from database)
    $query = "SELECT s.id_siswa, s.nis, s.nama_siswa, s.jk
              FROM siswa s
              WHERE s.kelas = '$kelas_escaped'
              ORDER BY s.nis";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Database error: ' . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Penilaian Siswa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 15px;
            font-size: 11pt;
        }
        .container {
            max-width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .title {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .school {
            font-size: 10pt;
            margin-bottom: 2px;
        }
        .info {
            font-size: 9pt;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            background-color: #FFC000;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
        }
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            height: 25px;
            font-size: 9pt;
        }
        td.nama {
            text-align: left;
        }
        tr.header-row-1 th {
            vertical-align: bottom;
        }
        tr.header-row-2 th {
            vertical-align: top;
        }
        @media print {
            body {
                padding: 10px;
                margin: 0;
            }
            .container {
                max-width: 100%;
            }
            table {
                page-break-inside: auto;
                width: 100%;
            }
            tr {
                page-break-inside: avoid;
            }
            th {
                background-color: #FFC000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }
            td {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <div class="title">PENILAIAN SISWA - <?php echo htmlspecialchars($kelas); ?></div>
            <div class="school"><?php echo htmlspecialchars($school_name); ?></div>
            <div class="info">Jumlah Tugas: <?php echo $jumlah_tugas; ?></div>
        </div>

        <table>
            <tr class="header-row-1">
                <th rowspan="2" style="text-align: center; vertical-align: middle; width: 30px;">No</th>
                <th rowspan="2" style="text-align: center; vertical-align: middle; width: 120px;">Nama</th>
                <th colspan="<?php echo $jumlah_tugas; ?>">Tugas Ke</th>
            </tr>
            <tr class="header-row-2">
                <?php for ($i = 1; $i <= $jumlah_tugas; $i++) { ?>
                    <th><?php echo $i; ?></th>
                <?php } ?>
            </tr>
            <?php
            $no = 1;
            while ($rowData = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td style='text-align: left;'>" . $no . "</td>";
                echo "<td class='nama'>" . htmlspecialchars($rowData['nama_siswa']) . "</td>";

                // Parse scores only if in saved mode
                if ($mode === 'saved' && isset($rowData['scores'])) {
                    $scores = json_decode($rowData['scores'], true);
                    if (!is_array($scores)) {
                        $scores = array_fill(0, $jumlah_tugas, 0);
                    }
                } else {
                    // For generate mode, all scores are empty
                    $scores = array_fill(0, $jumlah_tugas, 0);
                }

                // Add checkmarks
                for ($i = 0; $i < $jumlah_tugas; $i++) {
                    $value = isset($scores[$i]) && $scores[$i] == 1 ? '✓' : '';
                    echo "<td>" . $value . "</td>";
                }
                echo "</tr>";
                $no++;
            }
            ?>
        </table>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
