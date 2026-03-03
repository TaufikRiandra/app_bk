<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
include "../../backend/config/database.php";

$user_role = $_SESSION['role'] ?? 'guru_bk';
$today_date = date('Y-m-d');
$current_month = date('Y-m');

// Get selected month from URL or use current month
$selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month;

// Get list of guru BK (for filter)
$guru_bk_list = [];
$selected_guru_bk_id = isset($_GET['guru_bk']) ? intval($_GET['guru_bk']) : null;

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

$result = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $guru_bk_list[] = $row;
    }
}

// Get mediasi data for selected month
$mediasi_data = [];
$selected_guru_bk = null;

if ($selected_guru_bk_id) {
    // Get specific guru BK info
    $guru_query = "SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = $selected_guru_bk_id";
    $guru_result = mysqli_query($conn, $guru_query);
    if ($guru_result && $row = mysqli_fetch_assoc($guru_result)) {
        $selected_guru_bk = $row;
    }

    // Get mediasi for this guru_bk and month
    $query = "SELECT * FROM layanan_mediasi 
              WHERE id_guru_bk = $selected_guru_bk_id 
              AND DATE_FORMAT(tanggal, '%Y-%m') = '$selected_month'
              ORDER BY tanggal DESC";
} else {
    // Get mediasi for all guru_bk in this month
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

// Function to convert month to Indonesian
function formatDateIndonesian($dateString, $format = 'F Y') {
    $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    list($year, $month) = explode('-', $dateString);
    $month = intval($month);
    return $months_id[$month] . ' ' . $year;
}

?>

<div class="content">
    <!-- Header Section -->
    <div style="background-color: #4472C4; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">REKAP LAYANAN MEDIASI</h2>
        <p style="margin: 0 0 5px 0; font-size: 14px;">Bimbingan dan Konseling</p>
        <p style="margin: 0; font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($school_name) ?></p>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1 style="margin: 0; font-size: 1.75rem;">📋 Laporan Layanan Mediasi</h1>
            <p style="color: var(--text-light); margin: 0.5rem 0 1rem 0;">Rekap layanan mediasi bimbingan konseling</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="../../backend/rekap/export_mediasi_excel.php?month=<?= urlencode($selected_month) ?><?= $selected_guru_bk_id ? '&guru_bk=' . $selected_guru_bk_id : '' ?>" class="btn" style="background-color: #28a745; text-decoration: none;">
                📊 Export Excel
            </a>
            <a href="../../backend/rekap/export_mediasi_pdf.php?month=<?= urlencode($selected_month) ?><?= $selected_guru_bk_id ? '&guru_bk=' . $selected_guru_bk_id : '' ?>" class="btn" style="background-color: #dc3545; text-decoration: none;" target="_blank">
                📄 Export PDF
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card" style="margin-top: 1rem;">
        <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Saring:</label>
        <div style="display: flex; gap: 0.5rem; align-items: flex-start; flex-wrap: wrap;">
            <input type="month" id="filterMonth" value="<?= $selected_month ?>" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            <select id="filterGuruBK" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; min-width: 300px;">
                <option value="">-- Semua Guru BK --</option>
                <?php foreach ($guru_bk_list as $guru): ?>
                    <option value="<?= $guru['id_guru_bk'] ?>" <?= $selected_guru_bk_id === $guru['id_guru_bk'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($guru['nama']) ?> (<?= $guru['nip'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button onclick="applyFilter()" style="background-color: #4472C4; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                🔍 Terapkan
            </button>
        </div>
    </div>

    <!-- Guru BK Info Section (if selected) -->
    <?php if ($selected_guru_bk): ?>
    <div class="card" style="margin-top: 1rem; background-color: #f5f5f5; padding: 15px; border-left: 4px solid #4472C4;">
        <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;"><strong>NAMA / GURU BK & NIP:</strong></p>
        <p style="margin: 0; font-size: 14px;"><?= htmlspecialchars($selected_guru_bk['nama']) ?> (<?= $selected_guru_bk['nip'] ?>)</p>
    </div>
    <?php endif; ?>

    <!-- Data Table Section -->
    <div class="card" style="margin-top: 1rem; overflow-x: auto;">
        <div style="margin-bottom: 15px; color: #666;">
            <strong>Periode:</strong> <?php
                echo htmlspecialchars(formatDateIndonesian($selected_month));
            ?>
        </div>
        <table id="tabelMediasi" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background-color: #FFC000; color: black; font-weight: 600;">
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 40px;">No</th>
                    <?php if (!$selected_guru_bk_id): ?>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Guru BK</th>
                    <?php endif; ?>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 90px;">Tanggal</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 120px;">Nama Pihak 1</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 60px;">Kelas</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Masalah Pihak 1</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 120px;">Nama Pihak 2</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 60px;">Kelas</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Masalah Pihak 2</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Hasil Mediasi</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 150px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (empty($mediasi_data)): 
                ?>
                    <tr>
                        <td colspan="<?= $selected_guru_bk_id ? '10' : '11' ?>" style="padding: 20px; text-align: center; color: #999;">Belum ada data mediasi untuk periode ini</td>
                    </tr>
                <?php 
                else:
                    $no = 1;
                    foreach ($mediasi_data as $mediasi):
                        $tanggal = new DateTime($mediasi['tanggal']);
                        $tgl = $tanggal->format('d/m/Y');
                ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 12px; text-align: center; border: 1px solid #ddd;"><?= $no++ ?></td>
                        <?php if (!$selected_guru_bk_id): ?>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['guru_bk_nama'] ?? '-') ?></td>
                        <?php endif; ?>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= $tgl ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['nama_pihak_1'] ?? '') ?: '-' ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['kelas_pihak_1'] ?? '') ?: '-' ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['masalah_pihak_1'] ?? '') ?: '-' ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['nama_pihak_2'] ?? '') ?: '-' ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['kelas_pihak_2'] ?? '') ?: '-' ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['masalah_pihak_2'] ?? '') ?: '-' ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['hasil_mediasi'] ?? '') ?: '-' ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?= htmlspecialchars($mediasi['keterangan'] ?? '') ?: '-' ?></td>
                    </tr>
                <?php 
                    endforeach;
                endif;
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .content {
        padding: 20px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 20px;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #4472C4;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        border: none;
        transition: background-color 0.2s;
    }

    .btn:hover {
        opacity: 0.9;
    }
</style>

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

    // Allow Enter key on month filter
    document.getElementById('filterMonth')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilter();
        }
    });
</script>

<?php
include "../layouts/footer.php";
?>
