<?php
session_start();
if(!isset($_SESSION['login'])){
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

include "../config/database.php";

$kelas = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : '';

if (empty($kelas)) {
    echo '<div style="background:#fee;padding:1.5rem;border-radius:0.5rem;color:#c33"><p>❌ Kelas tidak valid</p></div>';
    exit;
}

// Get school data by class
$kelas_escaped = mysqli_real_escape_string($conn, $kelas);
$sekolah_query = mysqli_query($conn, "SELECT * FROM sekolah WHERE kelas IS NULL OR kelas = '' LIMIT 1");

$sekolah = mysqli_fetch_assoc($sekolah_query);

if (!$sekolah) {
    echo '<div style="background:#fee;padding:1.5rem;border-radius:0.5rem;color:#c33"><p>❌ Data sekolah tidak ditemukan</p></div>';
    exit;
}

// Get guru BK untuk kelas ini
$guru_bk_query = mysqli_query($conn, "
    SELECT gb.* FROM guru_bk gb
    INNER JOIN guru_bk_kelas gk ON gb.id_guru_bk = gk.id_guru_bk
    WHERE gk.kelas = '$kelas_escaped' LIMIT 1
");
$guru_bk_data = mysqli_fetch_assoc($guru_bk_query);

// Prepare HTML for display
$html = '';
$html .= '<div style="background:var(--bg-light);padding:1.5rem;border-radius:0.75rem;border-left:5px solid var(--brand)">';
$html .= '<h2 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1.5rem">Data Kelas ' . htmlspecialchars($kelas) . '</h2>';

// Buat table untuk data sekolah
$html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:1.5rem">';
$html .= '<tbody>';

// Data dari tabel sekolah
$fields = [
    'pemerintah' => ['🏛️ Pemerintah', htmlspecialchars(($sekolah['pemerintah'] ?? null) ?: '-')],
    'dinas' => ['📋 Dinas', htmlspecialchars(($sekolah['dinas'] ?? null) ?: '-')],
    'nama_sekolah' => ['🏫 Nama Sekolah', htmlspecialchars(($sekolah['nama_sekolah'] ?? null) ?: '-')],
    'alamat' => ['📍 Alamat', htmlspecialchars(($sekolah['alamat'] ?? null) ?: '-')],
    'jalan' => ['🛣️ Jalan', htmlspecialchars(($sekolah['jalan'] ?? null) ?: '-')],
    'tahun_pelajaran' => ['📅 Tahun Pelajaran', htmlspecialchars(($sekolah['tahun_pelajaran'] ?? null) ?: '-')],
    'kepala_sekolah' => ['👨‍💼 Kepala Sekolah', htmlspecialchars(($sekolah['kepala_sekolah'] ?? null) ?: '-')],
    'nip_kepala_sekolah' => ['🔖 NIP Kepala Sekolah', htmlspecialchars(($sekolah['nip_kepala_sekolah'] ?? null) ?: '-')],
    'guru_bk' => ['👨‍🏫 Guru BK Kelas ' . htmlspecialchars($kelas), htmlspecialchars(($guru_bk_data['nama'] ?? null) ?: '-')],
    'nip_guru_bk' => ['🔖 NIP Guru BK', htmlspecialchars(($guru_bk_data['nip'] ?? null) ?: '-')],
    'no_telp' => ['📱 No. Telepon Guru BK', htmlspecialchars(($guru_bk_data['no_telp'] ?? null) ?: '-')],
];

$row_count = 0;
foreach ($fields as $field => $data) {
    list($label, $value) = $data;
    $bg_color = ($row_count % 2 === 0) ? 'white' : 'var(--bg-light)';
    $html .= '<tr style="background:' . $bg_color . '">';
    $html .= '<td style="padding:0.75rem;border-bottom:1px solid var(--border);font-weight:600;width:35%;color:var(--brand)">' . $label . '</td>';
    $html .= '<td style="padding:0.75rem;border-bottom:1px solid var(--border);color:var(--text-dark)">' . $value . '</td>';
    $html .= '</tr>';
    $row_count++;
}

$html .= '</tbody>';
$html .= '</table>';

// Tombol Aksi
$html .= '<div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1.5rem">';
$html .= '<a href="/frontend/siswa/index.php?kelas=' . urlencode($kelas) . '" class="btn" style="text-decoration:none;font-size:0.9rem">👥 Data Siswa Kelas ' . htmlspecialchars($kelas) . '</a>';
$html .= '<a href="/frontend/sekolah/index.php" class="btn secondary" style="text-decoration:none;font-size:0.9rem">← Kembali</a>';
$html .= '</div>';

$html .= '</div>';

echo $html;
?>
