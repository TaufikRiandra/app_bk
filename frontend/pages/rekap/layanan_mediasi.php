<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../../auth/login.php");
    exit;
}

include "../../layouts/header.php";
include "../../layouts/sidebar.php";
include "../../../backend/config/database.php";

$user_role = $_SESSION['role'] ?? 'guru_bk';
$today_date = date('Y-m-d');
$current_month = date('Y-m');

$selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month;
$guru_bk_list = [];
$selected_guru_bk_id = isset($_GET['guru_bk']) ? intval($_GET['guru_bk']) : null;

$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

$result = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $guru_bk_list[] = $row;
    }
}

$mediasi_data = [];
$selected_guru_bk = null;

if ($selected_guru_bk_id) {
    $guru_query = "SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = $selected_guru_bk_id";
    $guru_result = mysqli_query($conn, $guru_query);
    if ($guru_result && $row = mysqli_fetch_assoc($guru_result)) {
        $selected_guru_bk = $row;
    }

    $query = "SELECT * FROM layanan_mediasi 
              WHERE id_guru_bk = $selected_guru_bk_id 
              AND DATE_FORMAT(tanggal, '%Y-%m') = '$selected_month'
              ORDER BY tanggal DESC";
} else {
    $query = "SELECT lm.*, gb.nama as guru_bk_nama, gb.nip as guru_bk_nip 
              FROM layanan_mediasi lm
              LEFT JOIN guru_bk gb ON lm.id_guru_bk = gb.id_guru_bk
              WHERE DATE_FORMAT(lm.tanggal, '%Y-%m') = '$selected_month'
              ORDER BY lm.tanggal DESC";
}

$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $mediasi_data[] = $row;
    }
}

function formatDateIndonesian($dateString, $format = 'F Y') {
    $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    list($year, $month) = explode('-', $dateString);
    $month = intval($month);
    return $months_id[$month] . ' ' . $year;
}
?>

<div class="mediasi-container">
    <!-- Header Section -->
    <div class="mediasi-header">
        <h2>REKAP LAYANAN MEDIASI</h2>
        <p>Bimbingan dan Konseling</p>
        <p><?= htmlspecialchars($school_name) ?></p>
    </div>

    <div class="report-header">
        <div>
            <h1 class="report-title"><i class="fas fa-file-alt"></i> Laporan Layanan Mediasi</h1>
            <p class="report-subtitle">Rekap layanan mediasi bimbingan konseling</p>
        </div>
        <div class="report-buttons">
            <a href="../../../backend/rekap/export_mediasi_excel.php?month=<?= urlencode($selected_month) ?><?= $selected_guru_bk_id ? '&guru_bk=' . $selected_guru_bk_id : '' ?>" class="filter-btn" style="background-color: #28a745; text-decoration: none;">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="../../../backend/rekap/export_mediasi_pdf.php?month=<?= urlencode($selected_month) ?><?= $selected_guru_bk_id ? '&guru_bk=' . $selected_guru_bk_id : '' ?>" class="filter-btn" style="background-color: #dc3545; text-decoration: none;" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <label class="filter-label">Saring:</label>
        <div class="filter-controls">
            <input type="month" id="filterMonth" class="filter-input" value="<?= $selected_month ?>">
            <select id="filterGuruBK" class="filter-input">
                <option value="">-- Semua Guru BK --</option>
                <?php foreach ($guru_bk_list as $guru): ?>
                    <option value="<?= $guru['id_guru_bk'] ?>" <?= $selected_guru_bk_id === $guru['id_guru_bk'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($guru['nama']) ?> (<?= $guru['nip'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button onclick="applyFilter()" class="filter-btn">
                🔍 Terapkan
            </button>
        </div>
    </div>

    <!-- Guru BK Info Section (if selected) -->
    <?php if ($selected_guru_bk): ?>
    <div class="guru-info-card">
        <p>NAMA / GURU BK & NIP:</p>
        <strong><?= htmlspecialchars($selected_guru_bk['nama']) ?> (<?= $selected_guru_bk['nip'] ?>)</strong>
    </div>
    <?php endif; ?>

    <!-- Data Table Section -->
    <div class="table-section">
        <div style="margin-bottom: 15px; color: #666;">
            <strong>Periode:</strong> <?= htmlspecialchars(formatDateIndonesian($selected_month)) ?>
        </div>
        
        <table id="tabelMediasi" class="mediasi-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <?php if (!$selected_guru_bk_id): ?>
                    <th style="min-width: 100px;">Guru BK</th>
                    <?php endif; ?>
                    <th style="min-width: 90px;">Tanggal</th>
                    <th style="min-width: 120px;">Nama Pihak 1</th>
                    <th style="width: 60px;">Kelas</th>
                    <th style="min-width: 100px;">Masalah Pihak 1</th>
                    <th style="min-width: 120px;">Nama Pihak 2</th>
                    <th style="width: 60px;">Kelas</th>
                    <th style="min-width: 100px;">Masalah Pihak 2</th>
                    <th style="min-width: 100px;">Hasil Mediasi</th>
                    <th style="min-width: 120px;">Dokumentasi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (empty($mediasi_data)): 
                ?>
                    <tr>
                        <td class="empty-state" colspan="<?= $selected_guru_bk_id ? '10' : '11' ?>">
                            Belum ada data mediasi untuk periode ini
                        </td>
                    </tr>
                <?php 
                else:
                    $no = 1;
                    foreach ($mediasi_data as $mediasi):
                        $tanggal = new DateTime($mediasi['tanggal']);
                        $tgl = $tanggal->format('d/m/Y');
                ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <?php if (!$selected_guru_bk_id): ?>
                        <td><?= htmlspecialchars($mediasi['guru_bk_nama'] ?? '-') ?></td>
                        <?php endif; ?>
                        <td><?= $tgl ?></td>
                        <td><?= htmlspecialchars($mediasi['nama_pihak_1'] ?? '') ?: '-' ?></td>
                        <td><?= htmlspecialchars($mediasi['kelas_pihak_1'] ?? '') ?: '-' ?></td>
                        <td><?= htmlspecialchars($mediasi['masalah_pihak_1'] ?? '') ?: '-' ?></td>
                        <td><?= htmlspecialchars($mediasi['nama_pihak_2'] ?? '') ?: '-' ?></td>
                        <td><?= htmlspecialchars($mediasi['kelas_pihak_2'] ?? '') ?: '-' ?></td>
                        <td><?= htmlspecialchars($mediasi['masalah_pihak_2'] ?? '') ?: '-' ?></td>
                        <td><?= htmlspecialchars($mediasi['hasil_mediasi'] ?? '') ?: '-' ?></td>
                        <td style="text-align: center;">
                            <?php if (!empty($mediasi['foto'])): ?>
                                <a href="../../assets/uploads/mediasi/<?= htmlspecialchars($mediasi['foto']) ?>" target="_blank" style="color: #4472C4; text-decoration: none; font-weight: 600;">
                                    🖼️ Lihat
                                </a>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                endif;
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function applyFilter() {
        const month = document.getElementById('filterMonth').value;
        const guruBK = document.getElementById('filterGuruBK').value;
        
        let url = '?month=' + encodeURIComponent(month);
        if (guruBK) {
            url += '&guru_bk=' + guruBK;
        }
        
        window.location.href = url;
    }

    document.getElementById('filterMonth')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilter();
        }
    });
</script>

<?php
include "../../layouts/footer.php";
?>

