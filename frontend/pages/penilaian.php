<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

include '../../backend/config/database.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

// Get all unique kelas from siswa table
$kelas_query = "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas";
$kelas_result = mysqli_query($conn, $kelas_query);
$kelas_list = [];
while ($row = mysqli_fetch_assoc($kelas_result)) {
    $kelas_list[] = $row['kelas'];
}

// Get school info
$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';
?>

<div class="content" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <!-- Header Section -->
    <div style="background-color: #4472C4; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">PENILAIAN SISWA</h2>
        <p style="margin: 0 0 5px 0; font-size: 14px;">Masukkan nilai tugas siswa</p>
        <p style="margin: 0; font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($school_name) ?></p>
    </div>

    <!-- Tab Navigation -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #ddd;">
        <button onclick="switchTab('generate')" id="tab-generate" style="background-color: #4472C4; color: white; border: none; padding: 12px 20px; border-radius: 0; cursor: pointer; font-weight: 600; font-size: 14px;">
            🆕 Generate Baru
        </button>
        <button onclick="switchTab('tersimpan')" id="tab-tersimpan" style="background-color: transparent; color: #666; border: none; padding: 12px 20px; border-radius: 0; cursor: pointer; font-weight: 600; font-size: 14px;">
            💾 Penilaian Tersimpan
        </button>
    </div>

    <!-- GENERATE TAB -->
    <div id="generate-tab" style="display: block;">
    <div style="background-color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: 1fr 1fr 150px; gap: 15px; align-items: flex-end;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Pilih Kelas
                </label>
                <select id="kelasSelect" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas_list as $kls): ?>
                        <option value="<?= htmlspecialchars($kls) ?>"><?= htmlspecialchars($kls) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Jumlah Tugas <span style="color: red;">*</span>
                </label>
                <input type="number" id="jumlahTugas" min="1" max="50" value="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>
            <button onclick="generateTable()" style="background-color: #4472C4; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                🔍 Generate
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div id="tableContainer" style="display: none; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto;">
        <div style="margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">
                Penilaian <span id="kelasLabel"></span> - Total: <span id="siswaCount">0</span> siswa
            </h3>
        </div>

        <!-- Task Info -->
        <div style="margin-bottom: 15px; padding: 10px; background-color: #f0f0f0; border-radius: 4px; font-size: 13px;">
            <strong>Jumlah Tugas:</strong> <span id="infoJumlahTugas">5</span>
        </div>

        <!-- Export Buttons -->
        <div id="exportButtons" style="margin-bottom: 15px; display: flex; gap: 10px;">
            <!-- Will be populated by renderTable() -->
        </div>

        <hr style="margin: 15px 0; border: 1px solid #ddd;">

        <table id="tabelPenilaian" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: auto;">
            <thead id="tabelHead">
                <!-- Header akan di-generate di sini -->
            </thead>
            <tbody id="tabelBody">
                <!-- Siswa dan input scores akan di-load di sini -->
            </tbody>
        </table>

        <!-- Buttons Section -->
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button onclick="simpanSemualNilai()" style="background-color: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                💾 Simpan Semua Nilai
            </button>
            <button onclick="resetTable()" style="background-color: #6c757d; color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                🔄 Reset
            </button>
        </div>

        <!-- Status Message -->
        <div id="statusMessage" style="margin-top: 15px; display: none; padding: 12px; border-radius: 4px; border-left: 4px solid;"></div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" style="display: none; background-color: #f5f5f5; padding: 40px; border-radius: 8px; text-align: center;">
        <p style="color: #999; font-size: 16px; margin: 0;">
            <span id="emptyMessage">Pilih kelas untuk menampilkan daftar siswa</span>
        </p>
    </div>

    <!-- Error State -->
    <div id="errorState" style="display: none; background-color: #fee; padding: 15px; border-radius: 8px; border-left: 4px solid #f00;">
        <p id="errorMessage" style="color: #c00; margin: 0;"></p>
    </div>
    </div><!-- END GENERATE TAB -->

    <!-- TERSIMPAN TAB -->
    <div id="tersimpan-tab" style="display: none;">
        <!-- List View -->
        <div id="tersimpan-list-view" style="display: block;">
            <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 16px; color: #333;">📋 Daftar Penilaian Tersimpan</h3>
                </div>

                <div id="tersimpanList" style="min-height: 200px;">
                    <p style="color: #999; text-align: center;">Memuat data...</p>
                </div>
            </div>
        </div>

        <!-- Detail View -->
        <div id="tersimpan-detail-view" style="display: none;">
            <button onclick="backToTersimpanList()" style="background-color: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px; margin-bottom: 15px;">
                ← Kembali
            </button>

            <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto;">
                <div style="margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 16px; color: #333;">
                        Penilaian <span id="detailKelasLabel"></span> - Total: <span id="detailSiswaCount">0</span> siswa
                    </h3>
                </div>

                <!-- Task Info -->
                <div style="margin-bottom: 15px; padding: 10px; background-color: #f0f0f0; border-radius: 4px; font-size: 13px;">
                    <strong>Jumlah Tugas:</strong> <span id="detailJumlahTugas">5</span>
                </div>

                <!-- Export Buttons -->
                <div id="detailExportButtons" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <!-- Will be populated -->
                </div>

                <hr style="margin: 15px 0; border: 1px solid #ddd;">

                <table id="detailTablePenilaian" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: auto;">
                    <thead id="detailTabelHead">
                        <!-- Header akan di-generate -->
                    </thead>
                    <tbody id="detailTabelBody">
                        <!-- Siswa dan scores akan di-load -->
                    </tbody>
                </table>

                <!-- Buttons Section -->
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button onclick="simpanPenilaianTersimpan()" style="background-color: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                        💾 Simpan Perubahan
                    </button>
                    <button onclick="backToTersimpanList()" style="background-color: #6c757d; color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                        Batal
                    </button>
                </div>

                <!-- Status Message -->
                <div id="detailStatusMessage" style="margin-top: 15px; display: none; padding: 12px; border-radius: 4px; border-left: 4px solid;"></div>
            </div>
        </div>
    </div><!-- END TERSIMPAN TAB -->

</div><!-- END CONTENT -->

<style>
    input[type="text"], input[type="number"], select, textarea {
        font-family: inherit;
        font-size: 13px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        line-height: 1.5;
    }

    input[type="text"]:focus, input[type="number"]:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #4472C4;
        box-shadow: 0 0 0 2px rgba(68, 114, 196, 0.1);
    }

    table {
        background-color: white;
    }

    th {
        background-color: #FFC000;
        color: black;
        font-weight: 600;
        border: 1px solid #ddd;
    }

    td {
        border: 1px solid #ddd;
    }

    input[type="checkbox"].task-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        margin: 0;
    }

    input[type="checkbox"].task-checkbox:focus {
        outline: 2px solid #4472C4;
    }
</style>

<script>
    let siswaData = [];
    let jumlahTugas = 5;
    let currentTab = 'generate';

    function switchTab(tab) {
        currentTab = tab;
        
        // Update UI
        document.getElementById('generate-tab').style.display = tab === 'generate' ? 'block' : 'none';
        document.getElementById('tersimpan-tab').style.display = tab === 'tersimpan' ? 'block' : 'none';
        
        // Update tab buttons
        document.getElementById('tab-generate').style.backgroundColor = tab === 'generate' ? '#4472C4' : 'transparent';
        document.getElementById('tab-generate').style.color = tab === 'generate' ? 'white' : '#666';
        
        document.getElementById('tab-tersimpan').style.backgroundColor = tab === 'tersimpan' ? '#4472C4' : 'transparent';
        document.getElementById('tab-tersimpan').style.color = tab === 'tersimpan' ? 'white' : '#666';
        
        if (tab === 'tersimpan') {
            loadTersimpan();
        }
    }

    function loadTersimpan() {
        fetch('../../backend/pages/get_penilaian_tersimpan.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.penilaian.length > 0) {
                    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">';
                    
                    data.penilaian.forEach(item => {
                        html += '<div onclick="viewPenilaianDetail(\'' + item.kelas.replace(/'/g, "\\'") + '\')" style="background-color: #f8f9fa; border: 2px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"';
                        html += ' onmouseover="this.style.boxShadow=\'0 4px 8px rgba(0,0,0,0.15)\'; this.style.borderColor=\'#4472C4\';"';
                        html += ' onmouseout="this.style.boxShadow=\'0 1px 3px rgba(0,0,0,0.1)\'; this.style.borderColor=\'#ddd\';">';
                        html += '<div style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 10px;">📚 ' + item.kelas + '</div>';
                        html += '<div style="font-size: 13px; color: #666; margin-bottom: 8px;"><strong>Tugas:</strong> ' + item.jumlah_tugas + '</div>';
                        html += '<div style="font-size: 13px; color: #666; margin-bottom: 8px;"><strong>Siswa Dinilai:</strong> ' + item.jumlah_siswa + '</div>';
                        html += '<div style="font-size: 12px; color: #999;"><strong>Update:</strong> ' + new Date(item.updated_at).toLocaleDateString('id-ID', {year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'}) + '</div>';
                        html += '<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd; font-size: 12px; color: #4472C4; font-weight: 600;">Klik untuk detail & edit →</div>';
                        html += '</div>';
                    });
                    
                    html += '</div>';
                    document.getElementById('tersimpanList').innerHTML = html;
                } else {
                    document.getElementById('tersimpanList').innerHTML = '<p style="color: #999; text-align: center; padding: 40px;">Belum ada penilaian yang tersimpan.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('tersimpanList').innerHTML = '<p style="color: #c00; text-align: center;">Gagal memuat data penilaian.</p>';
            });
    }

    function viewPenilaianDetail(kelas) {
        // Show detail view, hide list view
        document.getElementById('tersimpan-list-view').style.display = 'none';
        document.getElementById('tersimpan-detail-view').style.display = 'block';

        // Load data
        fetch('../../backend/pages/get_penilaian_detail.php?kelas=' + encodeURIComponent(kelas))
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderDetailTable(data.siswa, data.kelas, data.jumlah_tugas);
                } else {
                    showDetailStatusMessage('error', data.message || 'Gagal memuat data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showDetailStatusMessage('error', 'Terjadi kesalahan saat memuat data');
            });
    }

    function renderDetailTable(siswa, kelas, jumlahTugas) {
        const kelasLabel = document.getElementById('detailKelasLabel');
        const siswaCount = document.getElementById('detailSiswaCount');
        const headContainer = document.getElementById('detailTabelHead');
        const bodyContainer = document.getElementById('detailTabelBody');
        const exportButtonsContainer = document.getElementById('detailExportButtons');

        kelasLabel.textContent = '(' + kelas + ')';
        siswaCount.textContent = siswa.length;
        document.getElementById('detailJumlahTugas').textContent = jumlahTugas;

        // Generate export buttons
        const klasEncoded = encodeURIComponent(kelas);
        const exportHtml = `
            <a href="../../backend/pages/export_penilaian_excel.php?mode=saved&kelas=${klasEncoded}" 
               style="background-color: #28a745; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px; display: inline-block;"
               target="_blank">
                📊 Export Excel
            </a>
            <a href="../../backend/pages/export_penilaian_pdf.php?mode=saved&kelas=${klasEncoded}" 
               style="background-color: #dc3545; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px; display: inline-block;"
               target="_blank">
                📄 Export PDF
            </a>
        `;
        exportButtonsContainer.innerHTML = exportHtml;

        // Generate header rows
        let headerHtml = `
            <tr>
                <th rowspan="2" style="background-color: #FFC000; color: black; font-weight: 600; padding: 14px; border: 1px solid #ddd; text-align: center; vertical-align: middle; width: 50px;">No</th>
                <th rowspan="2" style="background-color: #FFC000; color: black; font-weight: 600; padding: 14px; border: 1px solid #ddd; text-align: center; vertical-align: middle; min-width: 150px;">Nama</th>
                <th colspan="${jumlahTugas}" style="background-color: #FFC000; color: black; font-weight: 600; padding: 14px; border: 1px solid #ddd; text-align: center;">Tugas Ke</th>
            </tr>
            <tr>
        `;

        // Add task number headers
        for (let i = 1; i <= jumlahTugas; i++) {
            headerHtml += `<th style="padding: 14px; text-align: center; border: 1px solid #ddd; width: 60px;">${i}</th>`;
        }

        headerHtml += `</tr>`;
        headContainer.innerHTML = headerHtml;

        // Generate body rows with editable checkboxes
        let bodyHtml = '';
        siswa.forEach((row, index) => {
            bodyHtml += `
                <tr data-id="${row.id_siswa}">
                    <td style="text-align: left; padding: 14px; border: 1px solid #ddd; vertical-align: middle; width: 50px;">${index + 1}</td>
                    <td style="text-align: left; padding: 14px; border: 1px solid #ddd; vertical-align: middle; min-width: 150px;">
                        <strong>${htmlEscape(row.nama_siswa)}</strong>
                    </td>
            `;

            // Add checkbox fields for each task
            for (let i = 1; i <= jumlahTugas; i++) {
                const isChecked = row.scores && row.scores[i - 1] == 1 ? 'checked' : '';
                bodyHtml += `
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: center; width: 60px;">
                        <input type="checkbox" class="task-checkbox task-${i}" ${isChecked}>
                    </td>
                `;
            }

            bodyHtml += `</tr>`;
        });

        bodyContainer.innerHTML = bodyHtml;
    }

    function backToTersimpanList() {
        document.getElementById('tersimpan-list-view').style.display = 'block';
        document.getElementById('tersimpan-detail-view').style.display = 'none';
    }

    function simpanPenilaianTersimpan() {
        const tbody = document.getElementById('detailTabelBody');
        const rows = tbody.querySelectorAll('tr');
        let savedCount = 0;
        let errorCount = 0;

        if (rows.length === 0) {
            showDetailStatusMessage('error', 'Tidak ada data yang disimpan');
            return;
        }

        // Disable button during save
        const saveBtn = event.target;
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.5';

        // Get jumlah_tugas from document
        const jumlahTugas = parseInt(document.getElementById('detailJumlahTugas').textContent);

        rows.forEach(row => {
            const id_siswa = parseInt(row.dataset.id);
            const scores = [];

            // Collect checkbox states for all tasks
            for (let i = 1; i <= jumlahTugas; i++) {
                const isChecked = row.querySelector(`.task-${i}`).checked ? 1 : 0;
                scores.push(isChecked);
            }

            const formData = new FormData();
            formData.append('id_siswa', id_siswa);
            formData.append('jumlah_tugas', jumlahTugas);
            formData.append('scores', JSON.stringify(scores));

            fetch('../../backend/pages/save_penilaian.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    savedCount++;
                } else {
                    errorCount++;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorCount++;
            });
        });

        // Show result after all requests
        setTimeout(() => {
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
            if (errorCount === 0) {
                showDetailStatusMessage('success', `✓ ${savedCount} nilai siswa berhasil disimpan`);
            } else {
                showDetailStatusMessage('error', `⚠ ${savedCount} disimpan, ${errorCount} gagal`);
            }
        }, 1000);
    }

    function showDetailStatusMessage(type, message) {
        const statusDiv = document.getElementById('detailStatusMessage');
        statusDiv.style.display = 'block';
        statusDiv.textContent = message;
        if (type === 'success') {
            statusDiv.style.backgroundColor = '#d4edda';
            statusDiv.style.color = '#155724';
            statusDiv.style.borderLeftColor = '#28a745';
        } else {
            statusDiv.style.backgroundColor = '#f8d7da';
            statusDiv.style.color = '#721c24';
            statusDiv.style.borderLeftColor = '#dc3545';
        }
        setTimeout(() => {
            statusDiv.style.display = 'none';
        }, 3000);
    }

    function generateTable() {
        const kelasSelect = document.getElementById('kelasSelect');
        const kelas = kelasSelect.value;
        const jumlah = parseInt(document.getElementById('jumlahTugas').value) || 5;

        if (!kelas) {
            showEmpty('Pilih kelas terlebih dahulu');
            return;
        }

        if (jumlah < 1 || jumlah > 50) {
            showError('Jumlah tugas harus antara 1-50');
            return;
        }

        jumlahTugas = jumlah;

        // Show loading state
        document.getElementById('tableContainer').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('errorState').style.display = 'none';

        // Fetch siswa by kelas
        fetch('../../backend/pages/get_siswa_for_penilaian.php?kelas=' + encodeURIComponent(kelas))
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (data.count === 0) {
                        showEmpty('Belum ada siswa di kelas ' + kelas + '. Tambahkan siswa terlebih dahulu di menu Data Siswa.');
                    } else {
                        siswaData = data.siswa;
                        renderTable(data.siswa, kelas, jumlahTugas);
                        document.getElementById('tableContainer').style.display = 'block';
                        document.getElementById('emptyState').style.display = 'none';
                        document.getElementById('errorState').style.display = 'none';
                    }
                } else {
                    showError(data.message || 'Gagal memuat data siswa');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Terjadi kesalahan saat memuat data siswa');
            });
    }

    function renderTable(siswa, kelas, jumlahTugas) {
        const kelasLabel = document.getElementById('kelasLabel');
        const siswaCount = document.getElementById('siswaCount');
        const headContainer = document.getElementById('tabelHead');
        const bodyContainer = document.getElementById('tabelBody');
        const exportButtonsContainer = document.getElementById('exportButtons');

        kelasLabel.textContent = '(' + kelas + ')';
        siswaCount.textContent = siswa.length;
        document.getElementById('infoJumlahTugas').textContent = jumlahTugas;

        // Generate export buttons with proper URL encoding
        const klasEncoded = encodeURIComponent(kelas);
        const exportHtml = `
            <a href="../../backend/pages/export_penilaian_excel.php?kelas=${klasEncoded}&jumlah_tugas=${jumlahTugas}" 
               style="background-color: #28a745; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px; display: inline-block;"
               target="_blank">
                📊 Export Excel
            </a>
            <a href="../../backend/pages/export_penilaian_pdf.php?kelas=${klasEncoded}&jumlah_tugas=${jumlahTugas}" 
               style="background-color: #dc3545; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px; display: inline-block;"
               target="_blank">
                📄 Export PDF
            </a>
        `;
        exportButtonsContainer.innerHTML = exportHtml;

        // Generate header rows
        let headerHtml = `
            <tr>
                <th rowspan="2" style="background-color: #FFC000; color: black; font-weight: 600; padding: 14px; border: 1px solid #ddd; text-align: center; vertical-align: middle; width: 50px;">No</th>
                <th rowspan="2" style="background-color: #FFC000; color: black; font-weight: 600; padding: 14px; border: 1px solid #ddd; text-align: center; vertical-align: middle; min-width: 150px;">Nama</th>
                <th colspan="${jumlahTugas}" style="background-color: #FFC000; color: black; font-weight: 600; padding: 14px; border: 1px solid #ddd; text-align: center;">Tugas Ke</th>
            </tr>
            <tr>
        `;

        // Add task number headers
        for (let i = 1; i <= jumlahTugas; i++) {
            headerHtml += `<th style="padding: 14px; text-align: center; border: 1px solid #ddd; width: 60px;">${i}</th>`;
        }

        headerHtml += `</tr>`;
        headContainer.innerHTML = headerHtml;

        // Generate body rows
        let bodyHtml = '';
        siswa.forEach((row, index) => {
            bodyHtml += `
                <tr data-id="${row.id_siswa}">
                    <td style="text-align: left; padding: 14px; border: 1px solid #ddd; vertical-align: middle; width: 50px;">${index + 1}</td>
                    <td style="text-align: left; padding: 14px; border: 1px solid #ddd; vertical-align: middle; min-width: 150px;">
                        <strong>${htmlEscape(row.nama_siswa)}</strong>
                    </td>
            `;

            // Add checkbox fields for each task
            for (let i = 1; i <= jumlahTugas; i++) {
                bodyHtml += `
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: center; width: 60px;">
                        <input type="checkbox" class="task-checkbox task-${i}">
                    </td>
                `;
            }

            bodyHtml += `</tr>`;
        });

        bodyContainer.innerHTML = bodyHtml;
    }

    function simpanSemualNilai() {
        if (siswaData.length === 0) {
            showStatusMessage('error', 'Tidak ada data yang disimpan');
            return;
        }

        const tbody = document.getElementById('tabelBody');
        const rows = tbody.querySelectorAll('tr');
        let savedCount = 0;
        let errorCount = 0;

        // Disable button during save
        const saveBtn = event.target;
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.5';

        rows.forEach(row => {
            const id_siswa = parseInt(row.dataset.id);
            const scores = [];

            // Collect checkbox states for all tasks
            for (let i = 1; i <= jumlahTugas; i++) {
                const isChecked = row.querySelector(`.task-${i}`).checked ? 1 : 0;
                scores.push(isChecked);
            }

            const formData = new FormData();
            formData.append('id_siswa', id_siswa);
            formData.append('jumlah_tugas', jumlahTugas);
            formData.append('scores', JSON.stringify(scores));

            fetch('../../backend/pages/save_penilaian.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    savedCount++;
                } else {
                    errorCount++;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorCount++;
            });
        });

        // Show result after all requests
        setTimeout(() => {
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
            if (errorCount === 0) {
                showStatusMessage('success', `✓ ${savedCount} nilai siswa berhasil disimpan`);
            } else {
                showStatusMessage('error', `⚠ ${savedCount} disimpan, ${errorCount} gagal`);
            }
        }, 1000);
    }

    function resetTable() {
        if (confirm('Reset tabel? Semua input nilai akan dikosongkan.')) {
            generateTable();
        }
    }

    function showStatusMessage(type, message) {
        const statusDiv = document.getElementById('statusMessage');
        statusDiv.style.display = 'block';
        statusDiv.textContent = message;
        if (type === 'success') {
            statusDiv.style.backgroundColor = '#d4edda';
            statusDiv.style.color = '#155724';
            statusDiv.style.borderLeftColor = '#28a745';
        } else {
            statusDiv.style.backgroundColor = '#f8d7da';
            statusDiv.style.color = '#721c24';
            statusDiv.style.borderLeftColor = '#dc3545';
        }
        setTimeout(() => {
            statusDiv.style.display = 'none';
        }, 3000);
    }

    function showEmpty(message) {
        document.getElementById('tableContainer').style.display = 'none';
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('emptyMessage').textContent = message;
    }

    function showError(message) {
        document.getElementById('tableContainer').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('errorState').style.display = 'block';
        document.getElementById('errorMessage').textContent = message;
    }

    function htmlEscape(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<?php
include '../layouts/footer.php';
?>
