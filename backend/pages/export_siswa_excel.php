<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../../frontend/auth/login.php");
    exit;
}

$kelas = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : '';

// Get school info
$school_result = mysqli_query($conn, "SELECT * FROM sekolah LIMIT 1");
$school        = mysqli_fetch_assoc($school_result);
$school_name   = $school['nama_sekolah'] ?? '';

// Build query
if ($kelas) {
    $kelas_esc = mysqli_real_escape_string($conn, $kelas);
    $query     = "SELECT * FROM siswa WHERE kelas = '$kelas_esc' ORDER BY nama_siswa ASC";
    $filename  = 'Data_Siswa_Kelas_' . $kelas . '.xls';
} else {
    $query    = "SELECT * FROM siswa ORDER BY kelas ASC, nama_siswa ASC";
    $filename = 'Data_Siswa_Semua_Kelas.xls';
}

$result     = mysqli_query($conn, $query);
$siswa_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $siswa_data[] = $row;
}

// Set headers for Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body  { font-family: Arial, sans-serif; font-size: 11px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #FFC000; font-weight: bold; color: black;
             print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        .header-title    { font-size: 14px; font-weight: bold; margin: 5px 0; }
        .header-subtitle { font-size: 11px; margin: 3px 0; }
        .info-block      { margin: 12px 0; font-size: 11px; }
    </style>
</head>
<body>
    <div style="text-align:center">
        <div class="header-title">DATA PRIBADI SISWA</div>
        <div class="header-subtitle">Bimbingan dan Konseling</div>
        <div class="header-subtitle"><?= htmlspecialchars($school_name) ?></div>
        <?php if ($kelas): ?>
        <div class="header-subtitle" style="margin-top:4px"><strong>Kelas: <?= htmlspecialchars($kelas) ?></strong></div>
        <?php endif; ?>
    </div>

    <div class="info-block">
        <strong>Total Siswa:</strong> <?= count($siswa_data) ?> siswa
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px;text-align:center">No</th>
                <?php if (!$kelas): ?><th>Kelas</th><?php endif; ?>
                <th>Nama Siswa</th>
                <th style="width:35px;text-align:center">L/P</th>
                <th>Tempat Lahir</th>
                <th>Tgl Lahir</th>
                <th>Alamat</th>
                <th>Agama</th>
                <th>Sekolah Asal</th>
                <th>No. HP Siswa</th>
                <th>Nama Ortu/Wali</th>
                <th>No. HP Ortu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($siswa_data)): ?>
                <tr>
                    <td colspan="<?= $kelas ? '11' : '12' ?>" style="text-align:center;color:#999">
                        Belum ada data siswa
                    </td>
                </tr>
            <?php else:
                $no = 1;
                foreach ($siswa_data as $s):
                    $tgl = $s['tgl_lahir'] ? date('d/m/Y', strtotime($s['tgl_lahir'])) : '-';
            ?>
                <tr>
                    <td style="text-align:center"><?= $no++ ?></td>
                    <?php if (!$kelas): ?>
                    <td style="text-align:center;font-weight:bold"><?= htmlspecialchars($s['kelas'] ?? '-') ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($s['nama_siswa'] ?? '-') ?></td>
                    <td style="text-align:center"><?= htmlspecialchars($s['jk'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['tempat_lahir'] ?? '-') ?></td>
                    <td><?= $tgl ?></td>
                    <td><?= htmlspecialchars($s['alamat'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['agama'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['sekolah_asal'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['no_hp'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['nama_ortu'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['no_hp_ortu'] ?? '-') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div style="margin-top:30px;text-align:right;font-size:11px">
        <?php
            $months_id = ['','Januari','Februari','Maret','April','Mei','Juni',
                          'Juli','Agustus','September','Oktober','November','Desember'];
            echo htmlspecialchars($school['jalan'] ?? '') . ', ';
            echo date('d') . ' ' . $months_id[intval(date('m'))] . ' ' . date('Y');
        ?>
        <div style="margin-top:60px">
            <?= htmlspecialchars($school['kepala_sekolah'] ?? 'Kepala Sekolah') ?><br>
            NIP. <?= htmlspecialchars($school['nip_kepala_sekolah'] ?? '') ?>
        </div>
    </div>
</body>
</html>