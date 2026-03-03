<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

include '../layouts/header.php';
include '../layouts/sidebar.php';
include '../../backend/config/database.php';
include '../../backend/config/MediasiSetupHelper.php';

// Check database setup
$setupHelper = checkMediasiSetup($conn);
$setupStatus = $setupHelper->getStatus();

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

$today_date = date('Y-m-d');
$tempo_data = $_SESSION['mediasi_data'] ?? [];
unset($_SESSION['mediasi_data']);
?>

<div class="mediasi-container">
    <!-- Header Section -->
    <div class="mediasi-header">
        <h2>LAYANAN MEDIASI</h2>
        <p>Bimbingan dan Konseling</p>
        <p><?= htmlspecialchars($school_name) ?></p>
    </div>

    <!-- Info Section -->
    <div class="mediasi-info">
        <p>
            <strong><i class="fas fa-lightbulb" style="margin-right:0.5rem"></i>Panduan Penggunaan:</strong><br>
            1. Pilih Guru BK yang bertugas (untuk admin)<br>
            2. Pilih tanggal kegiatan mediasi<br>
            3. Klik pada cell untuk mengedit data<br>
            4. Untuk dokumentasi, pilih file gambar (JPG, PNG, GIF, max 5MB)<br>
            5. Klik tombol "Simpan Mediasi" untuk menyimpan semua data
        </p>
    </div>

    <!-- Guru BK Info Section -->
    <div class="guru-bk-section">
        <?php if ($user_role === 'admin'): ?>
            <label class="guru-bk-label">Pilih Guru BK yang Bertugas</label>
            <div class="flex-row">
                <select id="selectGuruBK" class="guru-bk-select">
                    <option value="">-- Pilih Guru BK --</option>
                    <?php foreach ($guru_bk_list as $guru): ?>
                        <option value="<?= htmlspecialchars($guru['id_guru_bk']) ?>">
                            <?= htmlspecialchars($guru['nama']) ?> (<?= htmlspecialchars($guru['nip']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="tetapkanGuruBK()" class="filter-btn">
                    <i class="fas fa-check"></i> Tetapkan
                </button>
            </div>
            <div id="adminSelectedGuru" class="guru-bk-display" style="display: none;">
                <strong>GURU BK YANG DIPILIH:</strong>
                <p id="adminAssignedGuruBKDisplay"></p>
            </div>
        <?php elseif ($user_role === 'guru_bk'): ?>
            <div class="guru-bk-display">
                <strong>GURU BK YANG DITUGASKAN:</strong>
                <p id="assignedGuruBKDisplay">
                    <?= htmlspecialchars($current_guru_bk['nama'] ?? 'Loading...') ?> 
                    (<?= htmlspecialchars($current_guru_bk['nip'] ?? '') ?>)
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Date Picker Section -->
    <div class="date-section">
        <label class="date-label">Pilih Tanggal</label>
        <input type="date" id="inputDate" class="date-input" value="<?= $today_date ?>">
    </div>

    <!-- Mediasi Table Section -->
    <div class="table-section">
        <div class="table-header">
            <h3>Daftar Mediasi</h3>
            <div>
                <?php if ($user_role === 'guru_bk'): ?>
                    <button onclick="tambahMediasi()" class="filter-btn" style="background-color: #4472C4; margin-right: 5px;">
                        <i class="fas fa-plus"></i> Tambah Mediasi
                    </button>
                    <a href="../../frontend/rekap/layanan_mediasi.php" class="filter-btn" style="background-color: #17a2b8; color: white; text-decoration: none;">
                        <i class="fas fa-chart-pie"></i> Lihat Rekap
                    </a>
                <?php elseif ($user_role === 'admin'): ?>
                    <div id="adminButtonContainerTableHeader" style="display: none;">
                        <button onclick="tambahMediasi()" class="filter-btn" style="background-color: #4472C4; margin-right: 5px;">
                            <i class="fas fa-plus"></i> Tambah Mediasi
                        </button>
                        <a href="../../frontend/rekap/layanan_mediasi.php" class="filter-btn" style="background-color: #17a2b8; color: white; text-decoration: none;">
                            <i class="fas fa-chart-pie"></i> Lihat Rekap
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <table id="tabelMediasi" class="mediasi-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 100px;">Tanggal</th>
                    <th style="min-width: 100px;">Nama Pihak 1</th>
                    <th style="width: 60px;">Kelas</th>
                    <th style="min-width: 100px;">Masalah Pihak 1</th>
                    <th style="min-width: 100px;">Nama Pihak 2</th>
                    <th style="width: 60px;">Kelas</th>
                    <th style="min-width: 100px;">Masalah Pihak 2</th>
                    <th style="width: 120px;">Hasil Mediasi</th>
                    <th style="min-width: 150px;">Dokumentasi</th>
                    <th style="width: 60px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelMediasiBody">
                <!-- Dynamic rows will be inserted here -->
            </tbody>
        </table>

        <div class="mediasi-footer">
            <?php if ($user_role === 'guru_bk' || $user_role === 'admin'): ?>
                <button onclick="simpanMediasi()" class="mediasi-btn">
                    <i class="fas fa-save"></i> Simpan Mediasi
                </button>
                <button onclick="resetData()" class="mediasi-btn mediasi-btn-reset">
                    <i class="fas fa-redo-alt"></i> Reset
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const USER_ROLE = '<?= $user_role ?>';
const TODAY_DATE = '<?= $today_date ?>';
const CURRENT_GURU_BK = <?= json_encode($current_guru_bk) ?>;
const GURU_BK_LIST = <?= json_encode($guru_bk_list) ?>;
const TEMPO_DATA = <?= json_encode($tempo_data) ?>;

let mediasi_data = TEMPO_DATA.length > 0 ? TEMPO_DATA : [];
let editing_row = null;
let current_guru_bk_id = CURRENT_GURU_BK.id_guru_bk || null;
let file_uploads = {};
let deleted_mediasi_ids = [];

document.addEventListener('DOMContentLoaded', function() {
    if (USER_ROLE === 'guru_bk') {
        document.getElementById('assignedGuruBKDisplay').textContent = 
            (CURRENT_GURU_BK.nama || 'Loading...') + ' (' + (CURRENT_GURU_BK.nip || '') + ')';
        loadMediasi();
    }
});

function tetapkanGuruBK() {
    const selectElement = document.getElementById('selectGuruBK');
    const selectedId = selectElement ? selectElement.value : '';
    
    if (!selectedId) {
        alert('Pilih guru BK terlebih dahulu');
        return;
    }
    
    const selectedGuru = GURU_BK_LIST.find(g => String(g.id_guru_bk) === String(selectedId));
    if (selectedGuru) {
        current_guru_bk_id = selectedGuru.id_guru_bk;
        
        const displayDiv = document.getElementById('adminSelectedGuru');
        if (displayDiv) {
            displayDiv.style.display = 'block';
            const displayText = document.getElementById('adminAssignedGuruBKDisplay');
            if (displayText) {
                displayText.textContent = selectedGuru.nama + ' (' + selectedGuru.nip + ')';
            }
        }
        
        const buttonContainer = document.getElementById('adminButtonContainerTableHeader');
        if (buttonContainer) {
            buttonContainer.style.display = 'inline-block';
        }
        
        console.log('Guru BK dipilih:', selectedGuru);
        loadMediasi();
    }
}

function loadMediasi() {
    const tanggal = document.getElementById('inputDate').value;
    
    if (!tanggal) {
        const tbody = document.getElementById('tabelMediasiBody');
        tbody.innerHTML = '<tr><td class="empty-state" colspan="11">Pilih tanggal terlebih dahulu</td></tr>';
        return;
    }
    
    if (!current_guru_bk_id) {
        const tbody = document.getElementById('tabelMediasiBody');
        tbody.innerHTML = '<tr><td class="empty-state" colspan="11">Pilih guru BK terlebih dahulu</td></tr>';
        return;
    }

    const url = '../../backend/pages/get_mediasi.php?tanggal=' + encodeURIComponent(tanggal) + '&id_guru_bk=' + current_guru_bk_id;
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            mediasi_data = Array.isArray(data) ? data : [];
            renderTable();
        })
        .catch(error => {
            console.error('Error loading mediasi:', error);
            mediasi_data = [];
            renderTable();
        });
}

function renderTable() {
    const tbody = document.getElementById('tabelMediasiBody');
    if (mediasi_data.length === 0) {
        tbody.innerHTML = '<tr><td class="empty-state" colspan="11">Belum ada data mediasi untuk tanggal ini</td></tr>';
        return;
    }

    let html = '';
    mediasi_data.forEach((row, index) => {
        const rowId = row.id_mediasi ? 'id_' + row.id_mediasi : 'new_' + index;
        html += `<tr class="${row.id_mediasi ? '' : 'new-row'}" id="row_${rowId}">
                    <td style="text-align: center;">${index + 1}</td>
                    <td class="editable-cell" data-field="tanggal" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.tanggal || ''}</td>
                    <td class="editable-cell" data-field="nama_pihak_1" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.nama_pihak_1 || ''}</td>
                    <td class="editable-cell" data-field="kelas_pihak_1" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.kelas_pihak_1 || ''}</td>
                    <td class="editable-cell" data-field="masalah_pihak_1" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.masalah_pihak_1 || ''}</td>
                    <td class="editable-cell" data-field="nama_pihak_2" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.nama_pihak_2 || ''}</td>
                    <td class="editable-cell" data-field="kelas_pihak_2" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.kelas_pihak_2 || ''}</td>
                    <td class="editable-cell" data-field="masalah_pihak_2" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.masalah_pihak_2 || ''}</td>
                    <td class="editable-cell" data-field="hasil_mediasi" onclick="editCell(this, '${rowId}')" title="Klik untuk edit">${row.hasil_mediasi || ''}</td>
                    <td>
                        <div class="doc-cell">
                            ${row.foto ? `<small class="doc-file-info">File: ${row.foto}</small>` : '<small style="color: #999;">Belum ada file</small>'}
                            <input type="file" class="file-input" accept="image/*" data-rowid="${rowId}" onchange="handleFileUpload(event)" title="Upload gambar (JPG, PNG, GIF)">
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <div class="action-buttons">
                            <button class="action-btn btn-delete" onclick="hapusMediasi('${rowId}')" title="Hapus baris"><i class="fas fa-trash"></i> Hapus</button>
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
    const rowIdNum = rowId.split('_')[1];
    
    let rowData = null;
    if (rowId.startsWith('id_')) {
        rowData = mediasi_data.find(r => r.id_mediasi == rowIdNum);
    } else {
        rowData = mediasi_data[parseInt(rowIdNum)];
    }
    
    if (!rowData) return;
    
    let inputHtml = '';
    if (field === 'tanggal') {
        inputHtml = `<input type="date" class="cell-input" value="${value}" data-field="${field}" data-rowid="${rowId}">`;
    } else if (field === 'masalah_pihak_1' || field === 'masalah_pihak_2') {
        inputHtml = `<textarea class="cell-input" data-field="${field}" data-rowid="${rowId}">${value}</textarea>`;
    } else {
        inputHtml = `<input type="text" class="cell-input" value="${value}" data-field="${field}" data-rowid="${rowId}">`;
    }
    
    cell.innerHTML = inputHtml;
    editing_row = rowId;
    
    const input = cell.querySelector('input, textarea');
    input.focus();
    
    input.addEventListener('blur', function() {
        saveCell(this, rowId, field);
    });
    
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            if (e.ctrlKey || field === 'tanggal' || field === 'kelas_pihak_1' || field === 'kelas_pihak_2') {
                saveCell(this, rowId, field);
            }
        } else if (e.key === 'Escape') {
            editing_row = null;
            renderTable();
        }
    });
}

function saveCell(input, rowId, field) {
    const value = input.value;
    const rowIdNum = rowId.split('_')[1];
    
    let rowData = null;
    if (rowId.startsWith('id_')) {
        rowData = mediasi_data.find(r => r.id_mediasi == rowIdNum);
    } else {
        rowData = mediasi_data[parseInt(rowIdNum)];
    }
    
    if (rowData) {
        rowData[field] = value;
    }
    
    editing_row = null;
    renderTable();
}

function handleFileUpload(event) {
    const input = event.target;
    const file = input.files[0];
    
    if (!file) {
        alert('Pilih file terlebih dahulu');
        return;
    }
    
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Hanya file gambar (JPG, PNG, GIF, WebP) yang diizinkan');
        input.value = '';
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar (maksimal 5MB)');
        input.value = '';
        return;
    }
    
    const rowId = input.getAttribute('data-rowid');
    file_uploads[rowId] = file;
    
    const docDiv = document.getElementById('doc_' + rowId);
    if (docDiv) {
        const fileInfo = docDiv.querySelector('small');
        if (fileInfo) {
            fileInfo.textContent = 'File: ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
            fileInfo.className = 'doc-file-info';
            fileInfo.style.color = '#28a745';
        }
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
        foto: ''
    };
    
    mediasi_data.unshift(newMediasi);
    renderTable();
}

function hapusMediasi(rowId) {
    if (!confirm('Hapus data mediasi ini?')) return;
    
    const rowIdNum = rowId.split('_')[1];
    let rowIndex = -1;
    
    if (rowId.startsWith('id_')) {
        rowIndex = mediasi_data.findIndex(r => String(r.id_mediasi) === String(rowIdNum));
        if (rowIndex >= 0) {
            const mediasi = mediasi_data[rowIndex];
            if (mediasi.id_mediasi) {
                deleted_mediasi_ids.push(mediasi.id_mediasi);
            }
        }
    } else {
        rowIndex = parseInt(rowIdNum);
    }
    
    if (rowIndex >= 0 && rowIndex < mediasi_data.length) {
        mediasi_data.splice(rowIndex, 1);
        delete file_uploads[rowId];
        renderTable();
    }
}

async function simpanMediasi() {
    if (mediasi_data.length === 0) {
        alert('Tidak ada data mediasi untuk disimpan');
        return;
    }

    let hasFilesToUpload = Object.keys(file_uploads).length > 0;
    
    if (hasFilesToUpload) {
        simpanDenganFile();
    } else {
        simpanTanpaFile();
    }
}

async function simpanDenganFile() {
    try {
        for (const [rowId, file] of Object.entries(file_uploads)) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('upload_dir', 'mediasi');
            
            const response = await fetch('../../backend/pages/upload_file.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                const rowIdNum = rowId.split('_')[1];
                let rowData = null;
                
                if (rowId.startsWith('id_')) {
                    rowData = mediasi_data.find(r => r.id_mediasi == rowIdNum);
                } else {
                    rowData = mediasi_data[parseInt(rowIdNum)];
                }
                
                if (rowData) {
                    rowData.foto = result.filename;
                }
            } else {
                throw new Error(result.error || 'Upload gagal');
            }
        }
        
        simpanTanpaFile();
    } catch (error) {
        console.error('Error uploading files:', error);
        alert('Gagal mengupload file: ' + error.message);
    }
}

function simpanTanpaFile() {
    const isValid = mediasi_data.every(item => {
        return item.tanggal && item.nama_pihak_1 && item.nama_pihak_2;
    });
    
    if (!isValid) {
        alert('Tanggal, Nama Pihak 1, dan Nama Pihak 2 harus diisi untuk setiap baris');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'save_batch');
    formData.append('data', JSON.stringify(mediasi_data));
    formData.append('deleted_ids', JSON.stringify(deleted_mediasi_ids));

    fetch('../../backend/pages/save_mediasi.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Data mediasi berhasil disimpan');
            file_uploads = {};
            deleted_mediasi_ids = [];
            loadMediasi();
        } else {
            alert('Gagal menyimpan data: ' + (data.error || data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data: ' + error.message);
    });
}

function resetData() {
    if (confirm('Reset semua data yang belum disimpan?')) {
        mediasi_data = [];
        file_uploads = {};
        deleted_mediasi_ids = [];
        renderTable();
    }
}

const dateInput = document.getElementById('inputDate');
if (dateInput) {
    dateInput.addEventListener('change', function() {
        loadMediasi();
    });
}
</script>

<?php
include "../layouts/footer.php";
?>

