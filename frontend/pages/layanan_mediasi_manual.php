<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

include '../layouts/header.php';
include '../layouts/sidebar.php';
include '../../backend/config/database.php';

$user_role = $_SESSION['role'] ?? 'guru_bk';

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

// Get list of guru BK for dropdown (only if admin)
$guru_bk_list = [];
if ($user_role === 'admin') {
    $result = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $guru_bk_list[] = $row;
        }
    }
}

// Get current guru BK info (if guru_bk role)
$current_guru_bk = [];
if ($user_role === 'guru_bk') {
    $query = "SELECT id_guru_bk, nama, nip FROM guru_bk LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $current_guru_bk = $row;
    }
}

// Get today's mediasi if available
$today_mediasi = [];
$today_date = date('Y-m-d');
if ($user_role === 'guru_bk' && !empty($current_guru_bk)) {
    $query = "SELECT * FROM layanan_mediasi WHERE tanggal = '$today_date' AND id_guru_bk = " . $current_guru_bk['id_guru_bk'] . " ORDER BY tanggal DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $today_mediasi[] = $row;
        }
    }
}

$tempo_data = $_SESSION['mediasi_data'] ?? [];
unset($_SESSION['mediasi_data']);
?>

<div class="content">
    <!-- Header Section -->
    <div style="background-color: #4472C4; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">LAYANAN MEDIASI</h2>
        <p style="margin: 0 0 5px 0; font-size: 14px;">Bimbingan dan Konseling</p>
        <p style="margin: 0; font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($school_name) ?></p>
    </div>

    <!-- Guru BK Info Section -->
    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4472C4;">
        <?php if ($user_role === 'admin'): ?>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #333;">
                    Pilih Guru BK yang Bertugas
                </label>
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <select id="selectGuruBK" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">-- Pilih Guru BK --</option>
                        <?php foreach ($guru_bk_list as $guru): ?>
                            <option value="<?= $guru['id_guru_bk'] ?>"><?= htmlspecialchars($guru['nama']) ?> (<?= $guru['nip'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="tetapkanGuruBK()" style="background-color: #4472C4; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap;">
                        ✓ Tetapkan
                    </button>
                </div>
            </div>
        <?php elseif ($user_role === 'guru_bk'): ?>
            <div style="padding: 10px; background-color: white; border-radius: 4px;">
                <div style="margin-bottom: 8px;">
                    <strong style="color: #666; font-size: 12px;">GURU BK YANG DITUGASKAN:</strong>
                    <p id="assignedGuruBKDisplay" style="margin: 3px 0 0 0; font-size: 14px; font-weight: 500; color: #4472C4;">Loading...</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Date Picker Section -->
    <div style="background-color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
            Pilih Tanggal
        </label>
        <input type="date" id="inputDate" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" value="<?= $today_date ?>">
    </div>

    <!-- Mediasi Table Section -->
    <div style="background-color: white; border-radius: 8px; border: 1px solid #ddd; overflow: hidden;">
        <div style="background-color: #f5f5f5; padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">Daftar Mediasi</h3>
            <div>
                <?php if ($user_role === 'guru_bk'): ?>
                    <button onclick="tambahMediasi()" style="background-color: #4472C4; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; margin-right: 5px;">
                        + Tambah Mediasi
                    </button>
                    <a href="../../frontend/rekap/layanan_mediasi.php" style="background-color: #17a2b8; color: white; text-decoration: none; display: inline-block; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px;">
                        📊 Lihat Rekap
                    </a>
                <?php elseif ($user_role === 'admin'): ?>
                    <div id="adminButtonContainerTableHeader" style="display: none;">
                        <button onclick="tambahMediasi()" style="background-color: #4472C4; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; margin-right: 5px;">
                            + Tambah Mediasi
                        </button>
                        <a href="../../frontend/rekap/layanan_mediasi.php" style="background-color: #17a2b8; color: white; text-decoration: none; display: inline-block; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px;">
                            📊 Lihat Rekap
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <table id="tabelMediasi" style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background-color: #FFC000; color: black; font-weight: 600;">
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 40px;">No</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 100px;">Tanggal</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Nama Pihak 1</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 60px;">Kelas</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Masalah Pihak 1</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Nama Pihak 2</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 60px;">Kelas</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Masalah Pihak 2</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 120px;">Hasil Mediasi</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; min-width: 150px;">Dokumentasi</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 60px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelMediasiBody">
                <!-- Dynamic rows will be inserted here -->
            </tbody>
        </table>

        <div style="padding: 15px; text-align: center; border-top: 1px solid #ddd;">
            <?php if ($user_role === 'guru_bk'): ?>
                <button onclick="simpanMediasi()" style="background-color: #28a745; color: white; border: none; padding: 10px 30px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; margin-right: 10px;">
                    💾 Simpan Mediasi
                </button>
                <button onclick="resetData()" style="background-color: #6c757d; color: white; border: none; padding: 10px 30px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    ↻ Reset
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .content {
        padding: 20px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .editable-cell {
        cursor: pointer;
        padding: 8px !important;
    }

    .editable-cell:hover {
        background-color: #fffacd;
    }

    .cell-input {
        width: 100%;
        padding: 4px;
        border: 1px solid #4472C4;
        border-radius: 3px;
        font-size: 12px;
    }

    textarea.cell-input {
        min-height: 60px;
        resize: vertical;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        justify-content: center;
    }

    .action-btn {
        padding: 6px 10px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .btn-edit {
        background-color: #17a2b8;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-save {
        background-color: #28a745;
        display: none;
    }

    .btn-cancel {
        background-color: #6c757d;
        display: none;
    }

    .new-row {
        background-color: #fffacd;
    }
</style>

<script>
const USER_ROLE = '<?= $user_role ?>';
const TODAY_DATE = '<?= $today_date ?>';
const CURRENT_GURU_BK = <?= json_encode($current_guru_bk) ?>;
const GURU_BK_LIST = <?= json_encode($guru_bk_list) ?>;
const TEMPO_DATA = <?= json_encode($tempo_data) ?>;

let mediasi_data = TEMPO_DATA.length > 0 ? TEMPO_DATA : [];
let editing_row = null;
let current_guru_bk_id = CURRENT_GURU_BK.id_guru_bk || null;

document.addEventListener('DOMContentLoaded', function() {
    if (USER_ROLE === 'guru_bk') {
        document.getElementById('assignedGuruBKDisplay').textContent = 
            CURRENT_GURU_BK.nama + ' (' + CURRENT_GURU_BK.nip + ')';
        loadMediasi();
    }
});

function tetapkanGuruBK() {
    const selectedId = document.getElementById('selectGuruBK').value;
    if (!selectedId) {
        alert('Pilih guru BK terlebih dahulu');
        return;
    }
    
    const selectedGuru = GURU_BK_LIST.find(g => g.id_guru_bk == selectedId);
    if (selectedGuru) {
        current_guru_bk_id = selectedGuru.id_guru_bk;
        document.getElementById('assignedGuruBKDisplay').textContent = 
            selectedGuru.nama + ' (' + selectedGuru.nip + ')';
        document.getElementById('adminButtonContainerTableHeader').style.display = 'inline-flex';
        loadMediasi();
    }
}

function loadMediasi() {
    const tanggal = document.getElementById('inputDate').value;
    if (!tanggal || !current_guru_bk_id) {
        document.getElementById('tabelMediasiBody').innerHTML = 
            '<tr><td colspan="11" style="padding: 20px; text-align: center; color: #999;">Pilih tanggal dan guru BK terlebih dahulu</td></tr>';
        return;
    }

    // Fetch from server
    fetch('../../backend/pages/get_mediasi.php?tanggal=' + tanggal + '&id_guru_bk=' + current_guru_bk_id)
        .then(response => response.json())
        .then(data => {
            mediasi_data = data;
            renderTable();
        })
        .catch(error => {
            console.error('Error:', error);
            renderTable();
        });
}

function renderTable() {
    const tbody = document.getElementById('tabelMediasiBody');
    if (mediasi_data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" style="padding: 20px; text-align: center; color: #999;">Belum ada data mediasi untuk tanggal ini</td></tr>';
        return;
    }

    let html = '';
    mediasi_data.forEach((row, index) => {
        const rowId = row.id_mediasi || 'new_' + index;
        html += `<tr class="${row.id_mediasi ? '' : 'new-row'}" id="row_${rowId}">
                    <td style="padding: 12px; text-align: center; border: 1px solid #ddd;">${index + 1}</td>
                    <td class="editable-cell" data-field="tanggal" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.tanggal}</td>
                    <td class="editable-cell" data-field="nama_pihak_1" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.nama_pihak_1 || ''}</td>
                    <td class="editable-cell" data-field="kelas_pihak_1" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.kelas_pihak_1 || ''}</td>
                    <td class="editable-cell" data-field="masalah_pihak_1" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.masalah_pihak_1 || ''}</td>
                    <td class="editable-cell" data-field="nama_pihak_2" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.nama_pihak_2 || ''}</td>
                    <td class="editable-cell" data-field="kelas_pihak_2" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.kelas_pihak_2 || ''}</td>
                    <td class="editable-cell" data-field="masalah_pihak_2" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.masalah_pihak_2 || ''}</td>
                    <td class="editable-cell" data-field="hasil_mediasi" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.hasil_mediasi || ''}</td>
                    <td class="editable-cell" data-field="keterangan" onclick="editCell(this, '${rowId}')" style="border: 1px solid #ddd;">${row.keterangan || ''}</td>
                    <td style="padding: 12px; text-align: center; border: 1px solid #ddd;">
                        <div class="action-buttons">
                            <button class="action-btn btn-delete" onclick="hapusMediasi('${rowId}')">🗑️ Hapus</button>
                        </div>
                    </td>
                </tr>`;
    });
    tbody.innerHTML = html;
}

function editCell(cell, rowId) {
    if (editing_row === rowId) return;
    
    const field = cell.getAttribute('data-field');
    const value = cell.textContent;
    
    if (field === 'tanggal') {
        cell.innerHTML = `<input type="date" class="cell-input" value="${value}" onblur="saveCell(this, '${rowId}', '${field}')" onkeypress="handleKeypress(event, '${rowId}', '${field}')" autofocus>`;
    } else if (field === 'masalah_pihak_1' || field === 'masalah_pihak_2' || field === 'keterangan') {
        cell.innerHTML = `<textarea class="cell-input" onblur="saveCell(this, '${rowId}', '${field}')" onkeypress="handleKeypress(event, '${rowId}', '${field}')" autofocus>${value}</textarea>`;
    } else {
        cell.innerHTML = `<input type="text" class="cell-input" value="${value}" onblur="saveCell(this, '${rowId}', '${field}')" onkeypress="handleKeypress(event, '${rowId}', '${field}')" autofocus>`;
    }
    
    editing_row = rowId;
    cell.querySelector('input, textarea').focus();
}

function saveCell(input, rowId, field) {
    let rowIndex = mediasi_data.findIndex(r => (r.id_mediasi || -1) == rowId.split('_')[1]);
    if (rowIndex < 0) rowIndex = parseInt(rowId.split('_')[1]);
    
    if (rowIndex >= 0 && rowIndex < mediasi_data.length) {
        mediasi_data[rowIndex][field] = input.value;
    }
    
    editing_row = null;
    renderTable();
}

function handleKeypress(event, rowId, field) {
    if (event.key === 'Enter' && event.ctrlKey) {
        saveCell(event.target, rowId, field);
    }
}

function tambahMediasi() {
    const tanggal = document.getElementById('inputDate').value;
    if (!tanggal) {
        alert('Pilih tanggal terlebih dahulu');
        return;
    }
    
    const newMediasi = {
        tanggal: tanggal,
        id_guru_bk: current_guru_bk_id,
        nama_pihak_1: '',
        kelas_pihak_1: '',
        masalah_pihak_1: '',
        nama_pihak_2: '',
        kelas_pihak_2: '',
        masalah_pihak_2: '',
        hasil_mediasi: '',
        keterangan: ''
    };
    
    mediasi_data.unshift(newMediasi);
    renderTable();
}

function hapusMediasi(rowId) {
    if (!confirm('Hapus data mediasi ini?')) return;
    
    let rowIndex = mediasi_data.findIndex(r => (r.id_mediasi || -1) == rowId.split('_')[1]);
    if (rowIndex < 0) rowIndex = parseInt(rowId.split('_')[1]);
    
    if (rowIndex >= 0) {
        mediasi_data.splice(rowIndex, 1);
    }
    
    renderTable();
}

function simpanMediasi() {
    if (mediasi_data.length === 0) {
        alert('Tidak ada data mediasi untuk disimpan');
        return;
    }

    // Send to server
    const formData = new FormData();
    formData.append('action', 'save_batch');
    formData.append('data', JSON.stringify(mediasi_data));

    fetch('../../backend/pages/save_mediasi.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Data mediasi berhasil disimpan');
            loadMediasi();
        } else {
            alert('Gagal menyimpan data: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    });
}

function resetData() {
    if (confirm('Reset semua data yang belum disimpan?')) {
        mediasi_data = [];
        renderTable();
    }
}

document.getElementById('inputDate').addEventListener('change', loadMediasi);
</script>

<?php
include "../layouts/footer.php";
?>
