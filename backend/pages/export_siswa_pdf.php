<?php
session_start();
include '../config/database.php';
include '../config/auth_helper.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../../frontend/auth/login.php");
    exit;
}

$kelas = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : '';
if(!isAdmin()&&!canAccessKelas($conn,$kelas)){die("Akses ditolak: Anda tidak memiliki izin untuk kelas ini.");}

// Get school info
$school_result = mysqli_query($conn, "SELECT * FROM sekolah LIMIT 1");
$school        = mysqli_fetch_assoc($school_result);
$school_name   = $school['nama_sekolah'] ?? '';

// Build query
if ($kelas) {
    $kelas_esc = mysqli_real_escape_string($conn, $kelas);
    $query     = "SELECT * FROM siswa WHERE kelas = '$kelas_esc' ORDER BY nama_siswa ASC";
} else {
    $query     = "SELECT * FROM siswa ORDER BY kelas ASC, nama_siswa ASC";
}

$result     = mysqli_query($conn, $query);
$siswa_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $siswa_data[] = $row;
}

$total_l = count(array_filter($siswa_data, fn($s) => $s['jk'] === 'L'));
$total_p = count(array_filter($siswa_data, fn($s) => $s['jk'] === 'P'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Pribadi Siswa<?= $kelas ? ' Kelas ' . htmlspecialchars($kelas) : '' ?></title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { margin: 0; padding: 0;
            print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.4; }

        .header { text-align: center; margin-bottom: 15px; padding-bottom: 10px;
                  border-bottom: 2px solid #333; }
        .header-title    { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .header-subtitle { font-size: 11px; margin-bottom: 2px; }

        .info-section { display: flex; gap: 30px; margin: 12px 0;
                        padding: 8px 12px; background: #f5f5f5; border-radius: 4px; }
        .info-item strong { font-size: 10px; color: #555; }
        .info-item p { margin: 2px 0 0 0; font-size: 11px; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #FFC000; color: black; font-weight: bold;
             padding: 8px 6px; text-align: left; border: 1px solid #333; }
        th.center { text-align: center; }
        td { padding: 6px; border: 1px solid #ccc; vertical-align: top; }
        td.center { text-align: center; }
        tr { page-break-inside: avoid; }
        tr:nth-child(even) { background-color: #fafafa; }

        .badge-l { background: #cfe2ff; color: #0a58ca; padding: 2px 6px;
                   border-radius: 10px; font-weight: 600; font-size: 9px; }
        .badge-p { background: #f8d7e3; color: #c0144e; padding: 2px 6px;
                   border-radius: 10px; font-weight: 600; font-size: 9px; }

        .signature-section { margin-top: 30px; display: flex;
                              justify-content: flex-end; }
        .signature-item { text-align: center; min-width: 180px; }
        .signature-item p { margin-bottom: 50px; }

        @media print { body { margin: 0; padding: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">DATA PRIBADI SISWA</div>
        <div class="header-subtitle">Bimbingan dan Konseling</div>
        <div class="header-subtitle"><?= htmlspecialchars($school_name) ?></div>
    </div>

    <div class="info-section">
        <?php if ($kelas): ?>
        <div class="info-item">
            <strong>KELAS</strong>
            <p><?= htmlspecialchars($kelas) ?></p>
        </div>
        <?php endif; ?>
        <div class="info-item">
            <strong>TOTAL SISWA</strong>
            <p><?= count($siswa_data) ?> siswa</p>
        </div>
        <div class="info-item">
            <strong>LAKI-LAKI</strong>
            <p><?= $total_l ?> siswa</p>
        </div>
        <div class="info-item">
            <strong>PEREMPUAN</strong>
            <p><?= $total_p ?> siswi</p>
        </div>
        <div class="info-item">
            <strong>TAHUN AJARAN</strong>
            <p><?= htmlspecialchars($school['tahun_ajaran'] ?? '-') ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width:28px">No</th>
                <?php if (!$kelas): ?>
                <th class="center" style="width:50px">Kelas</th>
                <?php endif; ?>
                <th style="min-width:120px">Nama Siswa</th>
                <th class="center" style="width:35px">L/P</th>
                <th style="min-width:90px">Tempat Lahir</th>
                <th style="min-width:75px">Tgl Lahir</th>
                <th style="min-width:130px">Alamat</th>
                <th style="min-width:70px">Agama</th>
                <th style="min-width:110px">Sekolah Asal</th>
                <th style="min-width:90px">No. HP</th>
                <th style="min-width:110px">Nama Ortu/Wali</th>
                <th style="min-width:90px">No. HP Ortu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($siswa_data)): ?>
                <tr>
                    <td colspan="<?= $kelas ? '11' : '12' ?>" style="text-align:center;color:#999;padding:20px">
                        Belum ada data siswa
                    </td>
                </tr>
            <?php else:
                $no = 1;
                foreach ($siswa_data as $s):
                    $tgl = $s['tgl_lahir'] ? date('d/m/Y', strtotime($s['tgl_lahir'])) : '-';
            ?>
                <tr>
                    <td class="center"><?= $no++ ?></td>
                    <?php if (!$kelas): ?>
                    <td class="center" style="font-weight:600;color:#4472C4"><?= htmlspecialchars($s['kelas'] ?? '-') ?></td>
                    <?php endif; ?>
                    <td style="font-weight:600"><?= htmlspecialchars($s['nama_siswa'] ?? '-') ?></td>
                    <td class="center">
                        <?php if (($s['jk'] ?? '') === 'L'): ?>
                            <span class="badge-l">L</span>
                        <?php elseif (($s['jk'] ?? '') === 'P'): ?>
                            <span class="badge-p">P</span>
                        <?php else: ?>-<?php endif; ?>
                    </td>
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

    <div class="signature-section">
        <div class="signature-item">
            <?php
                $months_id = ['','Januari','Februari','Maret','April','Mei','Juni',
                              'Juli','Agustus','September','Oktober','November','Desember'];
                $tgl_ttd = date('d') . ' ' . $months_id[intval(date('m'))] . ' ' . date('Y');
            ?>
            <p><?= htmlspecialchars($school['jalan'] ?? 'Solok Selatan') ?>, <?= $tgl_ttd ?></p>
            <p><?= htmlspecialchars($school['kepala_sekolah'] ?? 'Kepala Sekolah') ?></p>
            <div>NIP. <?= htmlspecialchars($school['nip_kepala_sekolah'] ?? '') ?></div>
        </div>
    </div>

    <script>window.print();</script>
</body>
</html>