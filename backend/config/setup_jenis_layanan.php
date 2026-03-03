<?php
/**
 * Script untuk menambahkan jenis layanan BK ke database
 * Jalankan: php setup_jenis_layanan.php
 * Atau akses via browser: http://localhost/Aplikasi_BK/backend/config/setup_jenis_layanan.php
 */

include "database.php";

// Data jenis layanan
$layanan_list = [
    ['id' => 1, 'nama' => 'Konseling Individu'],
    ['id' => 2, 'nama' => 'Konseling Kelompok'],
    ['id' => 3, 'nama' => 'Alih Tangan Kasus'],
    ['id' => 4, 'nama' => 'Layanan Konsultasi'],
    ['id' => 5, 'nama' => 'Layanan Mediasi'],
    ['id' => 6, 'nama' => 'Konferensi Kasus'],
    ['id' => 7, 'nama' => 'Layanan Home Visit'],
    ['id' => 8, 'nama' => 'Layanan Manual'],
];

echo "=== Setup Jenis Layanan BK ===" . PHP_EOL;
echo "Menambahkan jenis layanan ke database..." . PHP_EOL . PHP_EOL;

$inserted = 0;
$skipped = 0;
$error = 0;

foreach($layanan_list as $layanan) {
    $id = intval($layanan['id']);
    $nama = mysqli_real_escape_string($conn, $layanan['nama']);
    
    // Check if already exists
    $check = mysqli_query($conn, "SELECT id_layanan FROM jenis_layanan WHERE id_layanan = $id");
    
    if(mysqli_num_rows($check) > 0) {
        // Update if exists
        $query = "UPDATE jenis_layanan SET nama_layanan = '$nama' WHERE id_layanan = $id";
        $result = mysqli_query($conn, $query);
        if($result) {
            echo "[UPDATE] ✓ {$layanan['nama']}" . PHP_EOL;
            $inserted++;
        } else {
            echo "[UPDATE FAILED] ✗ {$layanan['nama']} - Error: " . mysqli_error($conn) . PHP_EOL;
            $error++;
        }
    } else {
        // Insert if not exists
        $query = "INSERT INTO jenis_layanan (id_layanan, nama_layanan) VALUES ($id, '$nama')";
        $result = mysqli_query($conn, $query);
        if($result) {
            echo "[INSERT] ✓ {$layanan['nama']}" . PHP_EOL;
            $inserted++;
        } else {
            echo "[INSERT FAILED] ✗ {$layanan['nama']} - Error: " . mysqli_error($conn) . PHP_EOL;
            $error++;
        }
    }
}

echo PHP_EOL . "=== Hasil ===" . PHP_EOL;
echo "Berhasil: $inserted" . PHP_EOL;
echo "Error: $error" . PHP_EOL;

// Verify
echo PHP_EOL . "=== Data di Database ===" . PHP_EOL;
$result = mysqli_query($conn, "SELECT * FROM jenis_layanan ORDER BY id_layanan");
while($row = mysqli_fetch_assoc($result)) {
    echo "ID {$row['id_layanan']}: {$row['nama_layanan']}" . PHP_EOL;
}

echo PHP_EOL . "✅ Setup selesai!" . PHP_EOL;
mysqli_close($conn);
?>
