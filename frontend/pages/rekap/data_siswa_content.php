<?php
// Get database connection from global (injected by rekap.php)
$conn = $GLOBALS['conn'] ?? null;
if (!$conn) {
    include "../../../backend/config/database.php";
}

$user_role = $_SESSION['role'] ?? 'guru_bk';

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school        = mysqli_fetch_assoc($school_result);
$school_name   = $school['nama_sekolah'] ?? '';

// Get all unique kelas
$kelas_query  = "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas";
$kelas_result = mysqli_query($conn, $kelas_query);
$kelas_list   = [];
while ($row = mysqli_fetch_assoc($kelas_result)) {
    $kelas_list[] = $row['kelas'];
}

// Kelas terpilih dari GET parameter
$selected_kelas = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : null;

// Query data siswa hanya jika kelas sudah dipilih
$siswa_data = [];
if ($selected_kelas) {
    $kelas_esc = mysqli_real_escape_string($conn, $selected_kelas);
    $result = mysqli_query($conn, "SELECT * FROM siswa WHERE kelas = '$kelas_esc' ORDER BY nama_siswa ASC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $siswa_data[] = $row;
        }
    }
}

$total_siswa = count($siswa_data);
$total_l     = count(array_filter($siswa_data, fn($s) => $s['jk'] === 'L'));
$total_p     = count(array_filter($siswa_data, fn($s) => $s['jk'] === 'P'));
?>

<div style="margin-bottom:1.5rem">
    <h2 style="margin-bottom:.35rem;color:var(--brand,#4472C4)">Rekap Data Pribadi Siswa</h2>
    <p style="color:var(--text-light,#666);margin:0">Data pribadi dan orangtua siswa</p>
</div>

<!-- Selector -->
<div style="background:#f8f9fa;padding:1rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;border:1px solid #ddd;display:flex;align-items:center;flex-wrap:wrap;gap:.75rem">
    <label style="font-weight:600;font-size:.9rem;color:#333;white-space:nowrap">Pilih Kelas:</label>
    <select id="kelasSelect"
        style="padding:.55rem .8rem;border:1px solid #ddd;border-radius:5px;font-size:.9rem;min-width:180px">
        <option value="">-- Pilih Kelas --</option>
        <?php foreach ($kelas_list as $k): ?>
            <option value="<?= htmlspecialchars($k) ?>"
                <?= ($selected_kelas === $k) ? 'selected' : '' ?>>
                Kelas <?= htmlspecialchars($k) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button onclick="pilihKelas()"
        style="padding:.55rem 1.1rem;background:#4472C4;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600;white-space:nowrap">
        <i class="fas fa-search" style="margin-right:5px"></i>Tampilkan
    </button>
    <?php if ($selected_kelas): ?>
    <a href="?tab=data_siswa_content"
        style="padding:.55rem 1rem;background:#6c757d;color:white;text-decoration:none;border-radius:5px;font-size:.88rem;font-weight:600">
        <i class="fas fa-times" style="margin-right:5px"></i>Reset
    </a>
    <?php endif; ?>
</div>

<?php if ($selected_kelas): ?>

    <!-- Info Ringkasan -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
        <div style="background:#f8f9fa;border:1px solid #ddd;border-left:4px solid #4472C4;border-radius:8px;padding:1rem 1.25rem">
            <div style="font-size:.78rem;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Total Siswa</div>
            <div style="font-size:1.6rem;font-weight:700;color:#4472C4"><?= $total_siswa ?></div>
            <div style="font-size:.8rem;color:#888;margin-top:2px">Kelas <?= htmlspecialchars($selected_kelas) ?></div>
        </div>
        <div style="background:#f8f9fa;border:1px solid #ddd;border-left:4px solid #17a2b8;border-radius:8px;padding:1rem 1.25rem">
            <div style="font-size:.78rem;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Laki-laki</div>
            <div style="font-size:1.6rem;font-weight:700;color:#17a2b8"><?= $total_l ?></div>
            <div style="font-size:.8rem;color:#888;margin-top:2px">Siswa</div>
        </div>
        <div style="background:#f8f9fa;border:1px solid #ddd;border-left:4px solid #e83e8c;border-radius:8px;padding:1rem 1.25rem">
            <div style="font-size:.78rem;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Perempuan</div>
            <div style="font-size:1.6rem;font-weight:700;color:#e83e8c"><?= $total_p ?></div>
            <div style="font-size:.8rem;color:#888;margin-top:2px">Siswi</div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:.88rem">
            <thead style="background:#FFC000;color:black;font-weight:600">
                <tr>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:40px">No</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:160px">Nama Siswa</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:50px">L/P</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:160px">Tempat/Tgl Lahir</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:200px">Alamat</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:90px">Agama</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:160px">Sekolah Asal</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:130px">No. HP</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:160px">Nama Ortu/Wali</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:130px">No. HP Ortu</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswa_data)): ?>
                    <tr>
                        <td colspan="10" style="padding:1.5rem;text-align:center;color:#999">
                            <i class="fas fa-inbox" style="margin-right:6px"></i>
                            Belum ada data siswa untuk kelas <?= htmlspecialchars($selected_kelas) ?>
                        </td>
                    </tr>
                <?php else:
                    $no = 1;
                    foreach ($siswa_data as $siswa):
                        $ttl = trim(($siswa['tempat_lahir'] ?? '') . ($siswa['tgl_lahir'] ? ', ' . date('d/m/Y', strtotime($siswa['tgl_lahir'])) : ''));
                ?>
                    <tr style="border-bottom:1px solid #eee">
                        <td style="padding:.65rem .6rem;border:1px solid #eee;text-align:center"><?= $no++ ?></td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee;font-weight:600">
                            <?= htmlspecialchars($siswa['nama_siswa'] ?? '-') ?>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee;text-align:center">
                            <span style="padding:2px 8px;border-radius:12px;font-size:.78rem;font-weight:600;
                                background:<?= ($siswa['jk'] ?? '') === 'L' ? '#cfe2ff' : '#f8d7e3' ?>;
                                color:<?= ($siswa['jk'] ?? '') === 'L' ? '#0a58ca' : '#c0144e' ?>">
                                <?= htmlspecialchars($siswa['jk'] ?? '-') ?>
                            </span>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee;font-size:.83rem">
                            <?= htmlspecialchars($ttl ?: '-') ?>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee;font-size:.83rem">
                            <?= htmlspecialchars($siswa['alamat'] ?? '-') ?>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee">
                            <?= htmlspecialchars($siswa['agama'] ?? '-') ?>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee;font-size:.83rem">
                            <?= htmlspecialchars($siswa['sekolah_asal'] ?? '-') ?>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee">
                            <?= htmlspecialchars($siswa['no_hp'] ?? '-') ?>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee;font-size:.83rem">
                            <?= htmlspecialchars($siswa['nama_ortu'] ?? '-') ?>
                        </td>
                        <td style="padding:.65rem .6rem;border:1px solid #eee">
                            <?= htmlspecialchars($siswa['no_hp_ortu'] ?? '-') ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tombol Export -->
    <?php if (!empty($siswa_data)): ?>
    <div style="padding:1.5rem 0;border-top:1px solid #ddd;display:flex;gap:1rem;flex-wrap:wrap;margin-top:1.5rem">
        <button onclick="exportSiswaExcel()"
            style="padding:.75rem 1.5rem;background:linear-gradient(135deg,#27ae60 0%,#229954 100%);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:.5rem;box-shadow:0 4px 12px rgba(39,174,96,.3)">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
        <button onclick="exportSiswaPdf()"
            style="padding:.75rem 1.5rem;background:linear-gradient(135deg,#e74c3c 0%,#c0392b 100%);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:.5rem;box-shadow:0 4px 12px rgba(231,76,60,.3)">
            <i class="fas fa-file-pdf"></i> Export PDF
        </button>
    </div>
    <?php endif; ?>

<?php else: ?>

    <!-- Empty state — belum pilih kelas -->
    <div style="padding:3rem 1.5rem;text-align:center;background:#f8f9fa;border:2px dashed #ddd;border-radius:8px">
        <i class="fas fa-chalkboard-teacher" style="font-size:2.5rem;color:#ccc;display:block;margin-bottom:.75rem"></i>
        <p style="color:#999;font-size:.95rem;margin:0">Silakan pilih kelas untuk menampilkan data rekap siswa</p>
    </div>

<?php endif; ?>

<script>
function pilihKelas() {
    const kelas = document.getElementById('kelasSelect').value;
    if (!kelas) { alert('Pilih kelas terlebih dahulu'); return; }
    const url = new URL(window.location.href);
    url.searchParams.set('tab', 'data_siswa_content');
    url.searchParams.set('kelas', kelas);
    window.location.href = url.toString();
}

// Hanya Enter yang trigger, bukan change
document.getElementById('kelasSelect').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') pilihKelas();
});

function exportSiswaExcel() {
    window.location.href = '../../../backend/pages/export_siswa_excel.php?kelas=<?= urlencode($selected_kelas ?? '') ?>';
}

function exportSiswaPdf() {
    window.open('../../../backend/pages/export_siswa_pdf.php?kelas=<?= urlencode($selected_kelas ?? '') ?>', '_blank');
}
</script>