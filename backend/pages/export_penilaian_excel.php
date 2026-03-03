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

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="penilaian_' . $kelas . '_' . date('Y-m-d') . '.xls"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

echo "<html>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<style>";
echo "table { border-collapse: collapse; width: 100%; }";
echo "th, td { border: 1px solid #000; padding: 8px; text-align: center; font-family: Arial; font-size: 11pt; }";
echo "th { background-color: #FFC000; font-weight: bold; }";
echo ".title { font-size: 14pt; font-weight: bold; }";
echo ".school { font-size: 11pt; }";
echo ".info { font-size: 10pt; margin: 5px 0; }";
echo "td.left { text-align: left; }";
echo "</style>";
echo "</head>";
echo "<body>";

// Title
echo "<p class='title' style='text-align: center;'>PENILAIAN SISWA - " . htmlspecialchars($kelas) . "</p>";
echo "<p class='school' style='text-align: center;'>" . htmlspecialchars($school_name) . "</p>";
echo "<p class='info' style='text-align: center;'>Jumlah Tugas: " . $jumlah_tugas . "</p>";
echo "<br>";

// Table
echo "<table>";
echo "<tr style='background-color: #FFC000;'>";
echo "<th rowspan='2' style='background-color: #FFC000; font-weight: bold; text-align: center; vertical-align: middle; width: 30px;'>No</th>";
echo "<th rowspan='2' style='background-color: #FFC000; font-weight: bold; text-align: center; vertical-align: middle; width: 120px;'>Nama</th>";
echo "<th colspan='" . $jumlah_tugas . "' style='background-color: #FFC000; font-weight: bold;'>Tugas Ke</th>";
echo "</tr>";

echo "<tr style='background-color: #FFC000;'>";

for ($i = 1; $i <= $jumlah_tugas; $i++) {
    echo "<th>" . $i . "</th>";
}
echo "</tr>";

// Data rows
$no = 1;
while ($rowData = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td style='text-align: left;'>" . $no . "</td>";
    echo "<td style='text-align: left;'>" . htmlspecialchars($rowData['nama_siswa']) . "</td>";

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

echo "</table>";
echo "</body>";
echo "</html>";

mysqli_close($conn);
?>
