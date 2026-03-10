<?php
$conn = $GLOBALS['conn'] ?? null;
if (!$conn) {
    include "../../../backend/config/database.php";
}

require_once "../../../backend/config/auth_helper.php";

if (!function_exists('formatDateIndonesianMediasi')) {
    function formatDateIndonesianMediasi($dateString) {
        $months_id = ['','Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];
        $parts = explode('-', $dateString);
        if (count($parts) < 2) return $dateString;
        return $months_id[intval($parts[1])] . ' ' . $parts[0];
    }
}

$current_month  = date('Y-m');
$selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month;

// === KONTROL AKSES GURU BK ===
if (isAdmin()) {
    $selected_guru_bk_id = isset($_GET['guru_bk']) ? intval($_GET['guru_bk']) : null;
} else {
    $selected_guru_bk_id = getSessionGuruBkId();
}

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school        = mysqli_fetch_assoc($school_result);
$school_name   = $school['nama_sekolah'] ?? '';

// Get guru BK list (hanya admin)
$guru_bk_list = [];
if (isAdmin()) {
    $gr = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");
    if ($gr) {
        while ($row = mysqli_fetch_assoc($gr)) {
            $guru_bk_list[] = $row;
        }
    }
}

// Get selected guru info
$selected_guru_bk = null;
if ($selected_guru_bk_id) {
    $gq = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = $selected_guru_bk_id");
    if ($gq && $row = mysqli_fetch_assoc($gq)) {
        $selected_guru_bk = $row;
    }
}

// Build query
if ($selected_guru_bk_id) {
    $query = "SELECT lm.*, gb.nama as guru_bk_nama, gb.nip as guru_bk_nip
              FROM layanan_mediasi lm
              LEFT JOIN guru_bk gb ON lm.id_guru_bk = gb.id_guru_bk
              WHERE lm.id_guru_bk = $selected_guru_bk_id
              AND DATE_FORMAT(lm.tanggal, '%Y-%m') = '$selected_month'
              ORDER BY lm.tanggal DESC";
} else {
    $query = "SELECT lm.*, gb.nama as guru_bk_nama, gb.nip as guru_bk_nip
              FROM layanan_mediasi lm
              LEFT JOIN guru_bk gb ON lm.id_guru_bk = gb.id_guru_bk
              WHERE DATE_FORMAT(lm.tanggal, '%Y-%m') = '$selected_month'
              ORDER BY lm.tanggal DESC";
}

$mediasi_data = [];
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $mediasi_data[] = $row;
    }
}
?>

<div style="margin-bottom:1.5rem">
    <h2 style="margin-bottom:0.5rem;color:var(--brand)">Laporan Layanan Mediasi Bulanan</h2>
    <p style="color:var(--text-light);margin:0">Rekap layanan mediasi bimbingan konseling</p>
</div>

<!-- Filter Section -->
<div style="margin-bottom:1.5rem;padding:1.5rem;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border)">
    <?php if (isAdmin()): ?>
    <label style="font-weight:600;display:block;margin-bottom:0.75rem">Filter:</label>
    <div style="display:flex;gap:0.75rem;align-items:flex-start;flex-wrap:wrap">
        <input type="month" id="filterMonthMediasi" value="<?= $selected_month ?>"
               style="padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem">
        <select id="filterGuruBKMediasi"
                style="padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;min-width:300px">
            <option value="">-- Semua Guru BK --</option>
            <?php foreach ($guru_bk_list as $guru): ?>
                <option value="<?= $guru['id_guru_bk'] ?>"
                    <?= $selected_guru_bk_id === $guru['id_guru_bk'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($guru['nama']) ?> (<?= $guru['nip'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button onclick="applyFilterMediasi()"
                style="padding:0.75rem 1.5rem;background:var(--brand);color:black;border:none;border-radius:6px;cursor:pointer;font-weight:600">
            <i class="fas fa-filter"></i> Filter
        </button>
    </div>
    <?php else: ?>
    <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap">
        <div>
            <label style="font-weight:600;display:block;margin-bottom:0.5rem;font-size:0.9rem">Filter Bulan:</label>
            <input type="month" id="filterMonthMediasi" value="<?= $selected_month ?>"
                   style="padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem">
        </div>
        <div style="align-self:flex-end">
            <button onclick="applyFilterMediasi()"
                    style="padding:0.75rem 1.5rem;background:var(--brand);color:black;border:none;border-radius:6px;cursor:pointer;font-weight:600">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div style="align-self:flex-end;padding:0.6rem 1rem;background:#e8f0fe;border-left:4px solid var(--brand);border-radius:6px;font-size:0.85rem;color:#1a56db">
            <i class="fas fa-lock"></i> Data Anda: <strong><?= htmlspecialchars($selected_guru_bk['nama'] ?? '-') ?></strong>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Guru BK Info (khusus admin) -->
<?php if ($selected_guru_bk && isAdmin()): ?>
<div style="margin-bottom:1.5rem;padding:1rem;background:var(--bg-light);border-left:4px solid var(--brand);border-radius:6px">
    <p style="margin:0;font-size:0.9rem">
        <strong>GURU BK:</strong>
        <?= htmlspecialchars($selected_guru_bk['nama']) ?> (<?= $selected_guru_bk['nip'] ?>)
    </p>
</div>
<?php endif; ?>

<!-- Data Table -->
<div style="overflow-x:auto">
    <p style="color:var(--text-light);margin-bottom:1rem">
        <strong>Periode:</strong> <?= htmlspecialchars(formatDateIndonesianMediasi($selected_month)) ?>
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:0.9rem">
        <thead style="background:#FFC000;color:black;font-weight:600">
            <tr>
                <th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:40px;color:black">No</th>
                <?php if (!$selected_guru_bk_id): ?>
                <th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Guru BK</th>
                <?php endif; ?>
                <th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:90px;color:black">Tanggal</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Nama Pihak 1</th>
                <th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:60px;color:black">Kelas</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Masalah Pihak 1</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Nama Pihak 2</th>
                <th style="padding:0.75rem;text-align:center;border:1px solid var(--border);width:60px;color:black">Kelas</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Masalah Pihak 2</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid var(--border);min-width:120px;color:black">Hasil Mediasi</th>
                <th style="padding:0.75rem;text-align:center;border:1px solid var(--border);min-width:100px;color:black">Dokumentasi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mediasi_data)): ?>
                <tr>
                    <td colspan="<?= $selected_guru_bk_id ? '10' : '11' ?>"
                        style="padding:1.5rem;text-align:center;color:var(--text-light)">
                        Belum ada data mediasi untuk periode ini
                    </td>
                </tr>
            <?php else:
                $no = 1;
                foreach ($mediasi_data as $mediasi):
                    $tgl = (new DateTime($mediasi['tanggal']))->format('d/m/Y');
            ?>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= $no++ ?></td>
                    <?php if (!$selected_guru_bk_id): ?>
                    <td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['guru_bk_nama'] ?? '-') ?></td>
                    <?php endif; ?>
                    <td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= $tgl ?></td>
                    <td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['nama_pihak_1'] ?? '') ?: '-' ?></td>
                    <td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['kelas_pihak_1'] ?? '') ?: '-' ?></td>
                    <td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['masalah_pihak_1'] ?? '') ?: '-' ?></td>
                    <td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['nama_pihak_2'] ?? '') ?: '-' ?></td>
                    <td style="padding:0.75rem;text-align:center;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['kelas_pihak_2'] ?? '') ?: '-' ?></td>
                    <td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['masalah_pihak_2'] ?? '') ?: '-' ?></td>
                    <td style="padding:0.75rem;border:1px solid var(--border);color:black !important"><?= htmlspecialchars($mediasi['hasil_mediasi'] ?? '') ?: '-' ?></td>
                    <td style="padding:0.75rem;text-align:center;border:1px solid var(--border)">
                        <?php if (!empty($mediasi['foto'])): ?>
                            <a href="/frontend/assets/uploads/mediasi/<?= htmlspecialchars($mediasi['foto']) ?>"
                               target="_blank" style="color:#4472C4;text-decoration:none;font-weight:600">
                                <i class="fas fa-image"></i> Lihat
                            </a>
                        <?php else: ?>
                            <span style="color:#999">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Tombol Export -->
<div style="padding:1.5rem;text-align:center;border-top:1px solid var(--border);display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
    <button onclick="exportMediasiExcel()"
            style="padding:0.75rem 1.5rem;background:linear-gradient(135deg,#27ae60 0%,#229954 100%);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 12px rgba(39,174,96,0.3)">
        <i class="fas fa-file-excel"></i> Export Excel
    </button>
    <button onclick="exportMediasiPdf()"
            style="padding:0.75rem 1.5rem;background:linear-gradient(135deg,#e74c3c 0%,#c0392b 100%);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 12px rgba(231,76,60,0.3)">
        <i class="fas fa-file-pdf"></i> Export PDF
    </button>
</div>

<script>
    function applyFilterMediasi() {
        const month  = document.getElementById('filterMonthMediasi').value;
        <?php if (isAdmin()): ?>
        const guruBK = document.getElementById('filterGuruBKMediasi').value;
        <?php else: ?>
        const guruBK = '<?= $selected_guru_bk_id ?>'; // terkunci
        <?php endif; ?>

        const url = new URL(window.location);
        url.searchParams.set('tab', 'layanan_mediasi');
        url.searchParams.set('month', month);
        if (guruBK) {
            url.searchParams.set('guru_bk', guruBK);
        } else {
            url.searchParams.delete('guru_bk');
        }
        window.location.href = url.toString();
    }

    function exportMediasiExcel() {
        const month  = '<?= urlencode($selected_month) ?>';
        const guruId = '<?= $selected_guru_bk_id ? intval($selected_guru_bk_id) : '' ?>';
        let url = '../../../backend/pages/export_mediasi_excel.php?month=' + month;
        if (guruId) url += '&guru_bk=' + guruId;
        window.location.href = url;
    }

    function exportMediasiPdf() {
        const month  = '<?= urlencode($selected_month) ?>';
        const guruId = '<?= $selected_guru_bk_id ? intval($selected_guru_bk_id) : '' ?>';
        let url = '../../../backend/pages/export_mediasi_pdf.php?month=' + month;
        if (guruId) url += '&guru_bk=' + guruId;
        window.open(url, '_blank');
    }

    document.getElementById('filterMonthMediasi')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') applyFilterMediasi();
    });
</script>
