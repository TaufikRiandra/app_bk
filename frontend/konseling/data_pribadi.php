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

<div class="content" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    <!-- Header Section -->
    <div style="background-color: #4472C4; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">DATA PRIBADI SISWA</h2>
        <p style="margin: 0 0 5px 0; font-size: 14px;">Kelola data pribadi dan orangtua siswa</p>
        <p style="margin: 0; font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($school_name) ?></p>
    </div>

    <!-- Class Selector Section -->
    <div style="background-color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #333;">
            Pilih Kelas
        </label>
        <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            <select id="kelasSelect" style="flex: 1; min-width: 250px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas_list as $kls): ?>
                    <option value="<?= htmlspecialchars($kls) ?>"><?= htmlspecialchars($kls) ?></option>
                <?php endforeach; ?>
            </select>
            <button onclick="loadSiswaByKelas()" style="background-color: #4472C4; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; white-space: nowrap;">
                🔍 Load Siswa
            </button>
        </div>
    </div>

    <!-- Students Table Section -->
    <div id="siswaContainer" style="display: none; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto;">
        <div style="margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">
                Daftar Siswa <span id="kelasLabel"></span> - Total: <span id="siswaCount">0</span> siswa
            </h3>
        </div>

        <table id="tabelDataPribadi" style="width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed;">
            <thead>
                <tr style="background-color: #FFC000; color: black; font-weight: 600;">
                    <th style="padding: 14px; text-align: center; border: 1px solid #ddd; width: 50px;">No</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 140px;">Nama</th>
                    <th style="padding: 14px; text-align: center; border: 1px solid #ddd; width: 80px;">L/P</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 140px;">Tempat/Tgl Lahir</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 180px;">Alamat</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 100px;">Agama</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 140px;">Sekolah Asal</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 120px;">No. Hp</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 150px;">Nama Ortu</th>
                    <th style="padding: 14px; text-align: left; border: 1px solid #ddd; min-width: 120px;">No. Hp Ortu</th>
                </tr>
            </thead>
            <tbody id="tabelSiswaBody">
                <!-- Rows akan di-load di sini -->
            </tbody>
        </table>

        <!-- Buttons Section -->
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button onclick="simpanSemuaData()" style="background-color: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                💾 Simpan Semua Data
            </button>
            <button onclick="resetForm()" style="background-color: #6c757d; color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
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
</div>

<style>
    .content {
        padding: 20px;
    }

    input[type="text"], input[type="date"], select, textarea {
        font-family: inherit;
        font-size: 13px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        line-height: 1.5;
    }

    input[type="text"]:focus, input[type="date"]:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #4472C4;
        box-shadow: 0 0 0 2px rgba(68, 114, 196, 0.1);
    }

    .tabel-data-pribadi {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .tabel-data-pribadi td {
        padding: 12px;
        border: 1px solid #ddd;
    }

    .tabel-data-pribadi input, .tabel-data-pribadi select {
        width: 100%;
        box-sizing: border-box;
    }
</style>

<script>
    let siswaData = [];

    function loadSiswaByKelas() {
        const kelasSelect = document.getElementById('kelasSelect');
        const kelas = kelasSelect.value;

        if (!kelas) {
            showEmpty('Pilih kelas terlebih dahulu');
            return;
        }

        // Show loading state
        document.getElementById('siswaContainer').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('errorState').style.display = 'none';

        // Fetch siswa by kelas
        fetch('../../backend/konseling/get_siswa_by_kelas.php?kelas=' + encodeURIComponent(kelas))
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
                        renderSiswaTable(data.siswa, kelas);
                        document.getElementById('siswaContainer').style.display = 'block';
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

    function renderSiswaTable(siswa, kelas) {
        const container = document.getElementById('tabelSiswaBody');
        const kelasLabel = document.getElementById('kelasLabel');
        const siswaCount = document.getElementById('siswaCount');

        kelasLabel.textContent = '(' + kelas + ')';
        siswaCount.textContent = siswa.length;

        let html = '';
        siswa.forEach((row, index) => {
            html += `
                <tr data-id="${row.id_siswa}">
                    <td style="text-align: center; padding: 14px; border: 1px solid #ddd; vertical-align: middle; width: 50px;">${index + 1}</td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: middle;">
                        <strong>${htmlEscape(row.nama_siswa)}</strong>
                    </td>
                    <td style="text-align: center; padding: 14px; border: 1px solid #ddd; vertical-align: middle; width: 80px;">
                        <select class="col-jk" style="width: 100%; font-size: 14px; font-weight: 600;">
                            <option value="L" ${row.jk === 'L' ? 'selected' : ''}>L</option>
                            <option value="P" ${row.jk === 'P' ? 'selected' : ''}>P</option>
                        </select>
                    </td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 12px; margin-bottom: 8px;">Tempat: <input type="text" class="col-tempat_lahir" value="${htmlEscape(row.tempat_lahir || '')}" placeholder="Kota" style="width: 90%; margin-top: 3px;"></div>
                        <div style="font-size: 12px;">Tgl: <input type="date" class="col-tgl_lahir" value="${row.tgl_lahir || ''}" style="width: 90%; margin-top: 3px;"></div>
                    </td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: top;">
                        <textarea class="col-alamat" placeholder="Alamat lengkap" style="width: 100%; min-height: 70px;">${htmlEscape(row.alamat || '')}</textarea>
                    </td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: top;">
                        <select class="col-agama" style="width: 100%;">
                            <option value="">Pilih</option>
                            <option value="Islam" ${row.agama === 'Islam' ? 'selected' : ''}>Islam</option>
                            <option value="Kristen" ${row.agama === 'Kristen' ? 'selected' : ''}>Kristen</option>
                            <option value="Katolik" ${row.agama === 'Katolik' ? 'selected' : ''}>Katolik</option>
                            <option value="Hindu" ${row.agama === 'Hindu' ? 'selected' : ''}>Hindu</option>
                            <option value="Buddha" ${row.agama === 'Buddha' ? 'selected' : ''}>Buddha</option>
                            <option value="Konghucu" ${row.agama === 'Konghucu' ? 'selected' : ''}>Konghucu</option>
                        </select>
                    </td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: top;">
                        <input type="text" class="col-sekolah_asal" value="${htmlEscape(row.sekolah_asal || '')}" placeholder="Nama sekolah asal">
                    </td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: top;">
                        <input type="text" class="col-no_hp" value="${htmlEscape(row.no_hp || '')}" placeholder="08xx">
                    </td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: top;">
                        <input type="text" class="col-nama_ortu" value="${htmlEscape(row.nama_ortu || '')}" placeholder="Nama orangtua">
                    </td>
                    <td style="padding: 14px; border: 1px solid #ddd; vertical-align: top;">
                        <input type="text" class="col-no_hp_ortu" value="${htmlEscape(row.no_hp_ortu || '')}" placeholder="08xx">
                    </td>
                </tr>
            `;
        });

        container.innerHTML = html;
    }

    function simpanSemuaData() {
        if (siswaData.length === 0) {
            showStatusMessage('error', 'Tidak ada data yang disimpan');
            return;
        }

        const tbody = document.getElementById('tabelSiswaBody');
        const rows = tbody.querySelectorAll('tr');
        let savedCount = 0;
        let errorCount = 0;

        // Disable button during save
        const saveBtn = event.target;
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.5';

        rows.forEach(row => {
            const id_siswa = parseInt(row.dataset.id);
            const jk = row.querySelector('.col-jk').value;
            const tempat_lahir = row.querySelector('.col-tempat_lahir').value;
            const tgl_lahir = row.querySelector('.col-tgl_lahir').value;
            const alamat = row.querySelector('.col-alamat').value;
            const agama = row.querySelector('.col-agama').value;
            const sekolah_asal = row.querySelector('.col-sekolah_asal').value;
            const no_hp = row.querySelector('.col-no_hp').value;
            const nama_ortu = row.querySelector('.col-nama_ortu').value;
            const no_hp_ortu = row.querySelector('.col-no_hp_ortu').value;

            const formData = new FormData();
            formData.append('id_siswa', id_siswa);
            formData.append('jk', jk);
            formData.append('tempat_lahir', tempat_lahir);
            formData.append('tgl_lahir', tgl_lahir);
            formData.append('alamat', alamat);
            formData.append('agama', agama);
            formData.append('sekolah_asal', sekolah_asal);
            formData.append('no_hp', no_hp);
            formData.append('nama_ortu', nama_ortu);
            formData.append('no_hp_ortu', no_hp_ortu);

            fetch('../../backend/konseling/save_data_pribadi_inline.php', {
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
                showStatusMessage('success', `✓ ${savedCount} data pribadi siswa berhasil disimpan`);
            } else {
                showStatusMessage('error', `⚠ ${savedCount} disimpan, ${errorCount} gagal`);
            }
        }, 1000);
    }

    function resetForm() {
        if (confirm('Reset form? Data akan dikembalikan ke state awal.')) {
            loadSiswaByKelas();
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
        document.getElementById('siswaContainer').style.display = 'none';
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('emptyMessage').textContent = message;
    }

    function showError(message) {
        document.getElementById('siswaContainer').style.display = 'none';
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

    // Auto-load on page load if kelas parameter exists
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const kelas = params.get('kelas');
        if (kelas) {
            document.getElementById('kelasSelect').value = kelas;
            loadSiswaByKelas();
        }
    });
</script>

<?php
include '../layouts/footer.php';
?>
