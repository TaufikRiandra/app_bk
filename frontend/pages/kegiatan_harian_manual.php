<?php
session_start();

// Check if user is logged in (match with dashboard.php pattern)
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

// Get current guru BK info (if guru_bk role) - get first guru_bk record
$current_guru_bk = [];
if ($user_role === 'guru_bk') {
    $query = "SELECT id_guru_bk, nama, nip FROM guru_bk LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $current_guru_bk = $row;
    }
}

// Get today's kegiatan if available
$today_kegiatan = [];
$today_date = date('Y-m-d');
if ($user_role === 'guru_bk' && !empty($current_guru_bk)) {
    $query = "SELECT * FROM kegiatan_harian WHERE tanggal = '$today_date' AND id_guru_bk = " . $current_guru_bk['id_guru_bk'] . " ORDER BY waktu_mulai";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $today_kegiatan[] = $row;
        }
    }
}
?>

<div class="content">
    <!-- Header Section -->
    <div style="background-color: #4472C4; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; font-size: 18px;">KEGIATAN HARIAN</h2>
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
    </div>

    <!-- Date Picker Section -->
    <div style="background-color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
            Pilih Tanggal
        </label>
        <input type="date" id="inputDate" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" value="<?= $today_date ?>">
    </div>

    <!-- Kegiatan Table Section -->
    <div style="background-color: white; border-radius: 8px; border: 1px solid #ddd; overflow: hidden;">
        <div style="background-color: #f5f5f5; padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">Daftar Kegiatan</h3>
            <div>
                <?php if ($user_role === 'guru_bk'): ?>
                    <button onclick="tambahKegiatan()" style="background-color: #4472C4; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; margin-right: 5px;">
                        + Tambah Kegiatan
                    </button>
                    <a href="../../frontend/rekap/kegiatan_harian.php" style="background-color: #17a2b8; color: white; text-decoration: none; display: inline-block; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px;">
                        📊 Lihat Rekap
                    </a>
                <?php elseif ($user_role === 'admin'): ?>
                    <div id="adminButtonContainerTableHeader" style="display: none;">
                        <button onclick="tambahKegiatan()" style="background-color: #4472C4; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; margin-right: 5px;">
                            + Tambah Kegiatan
                        </button>
                        <a href="../../frontend/rekap/kegiatan_harian.php" style="background-color: #17a2b8; color: white; text-decoration: none; display: inline-block; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px;">
                            📊 Lihat Rekap
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <table id="tabelKegiatan" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background-color: #FFC000; color: black; font-weight: 600;">
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 40px;">No</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 100px;">Hari/Tgl</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 80px;">Waktu Mulai</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 80px;">Waktu Selesai</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Uraian Kegiatan</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 120px;">Jenis Layanan</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 120px;">Sasaran Layanan</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd; width: 120px;">Bidang/Kode Layanan</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd; width: 60px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelKegiatanBody">
                <!-- Dynamic rows will be inserted here -->
            </tbody>
        </table>

        <div style="padding: 15px; text-align: center; border-top: 1px solid #ddd;">
            <?php if ($user_role === 'guru_bk'): ?>
                <button onclick="simpanKegiatan()" style="background-color: #28a745; color: white; border: none; padding: 10px 30px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; margin-right: 10px;">
                    💾 Simpan Kegiatan
                </button>
                <button onclick="resetForm()" style="background-color: #6c757d; color: white; border: none; padding: 10px 30px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    🔄 Reset
                </button>
            <?php elseif ($user_role === 'admin'): ?>
                <div id="adminButtonContainerBottom" style="display: none;">
                    <button onclick="simpanKegiatan()" style="background-color: #28a745; color: white; border: none; padding: 10px 30px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; margin-right: 10px;">
                        💾 Simpan Kegiatan
                    </button>
                    <button onclick="resetForm()" style="background-color: #6c757d; color: white; border: none; padding: 10px 30px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                        🔄 Reset
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS untuk responsive -->
<style>
    .content {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    input[type="time"], input[type="date"], select {
        font-family: inherit;
    }

    input[type="time"]:focus, input[type="date"]:focus, select:focus {
        outline: none;
        border-color: #4472C4 !important;
        box-shadow: 0 0 0 2px rgba(68, 114, 196, 0.1);
    }

    textarea {
        font-family: inherit;
        font-size: 13px;
        resize: vertical;
    }

    textarea:focus {
        outline: none;
        border-color: #4472C4 !important;
        box-shadow: 0 0 0 2px rgba(68, 114, 196, 0.1);
    }

    .row-input {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
        width: 100%;
        box-sizing: border-box;
    }

    @media print {
        button, .content {
            display: none;
        }
        
        #tabelKegiatan {
            font-size: 12px;
        }
    }
</style>
<script>
    const userRole = '<?= $user_role ?>';
    const currentDate = '<?= $today_date ?>';
    let kegiatanCount = 0;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('inputDate');
        const selectGuruBK = document.getElementById('selectGuruBK');
        
        dateInput.addEventListener('change', function() {
            loadAssignedGuruBK();
            loadKegiatan();
        });
        if (selectGuruBK) {
            selectGuruBK.addEventListener('change', loadKegiatan);
        }
        
        // Load assigned guru BK for current date on page load
        loadAssignedGuruBK();
    });

    function tetapkanGuruBK() {
        const selectGuruBK = document.getElementById('selectGuruBK');
        const guruBKId = selectGuruBK.value;
        const guruBKText = selectGuruBK.options[selectGuruBK.selectedIndex].text;

        if (!guruBKId) {
            alert('Pilih Guru BK terlebih dahulu');
            return;
        }

        const tanggal = document.getElementById('inputDate').value;

        if (confirm('Tetapkan ' + guruBKText + ' sebagai Guru BK yang bertugas pada tanggal ' + tanggal + '?')) {
            const formData = new FormData();
            formData.append('mode', 'set_guru_bk');
            formData.append('tanggal', tanggal);
            formData.append('id_guru_bk', guruBKId);

            fetch('../../backend/pages/save_kegiatan_harian.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert('✓ ' + data.guru_bk_name + ' ditetapkan bertugas');
                            loadKegiatan();
                        } else {
                            alert('Error: ' + data.message);
                            console.error('Response:', data);
                        }
                    } catch (e) {
                        console.error('Parse error:', e, 'Response:', text);
                        alert('Error menyimpan data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error menyimpan data');
                });
        }
    }

    function loadAssignedGuruBK() {
        const tanggal = document.getElementById('inputDate').value;
        const selectGuruBK = document.getElementById('selectGuruBK');
        const displayElement = document.getElementById('assignedGuruBKDisplay');
        
        if (!tanggal) return;
        
        // Fetch assigned guru BK untuk tanggal ini
        fetch('../../backend/pages/get_assigned_guru_bk.php?tanggal=' + tanggal)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.assigned_guru_bk) {
                    if (userRole === 'admin' && selectGuruBK) {
                        // Set dropdown value ke guru BK yang sudah ditetapkan
                        selectGuruBK.value = data.assigned_guru_bk.id_guru_bk;
                        // Trigger change event untuk load kegiatan
                        selectGuruBK.dispatchEvent(new Event('change'));
                    } else if (userRole === 'guru_bk' && displayElement) {
                        // Display guru BK yang ditugaskan
                        displayElement.textContent = data.assigned_guru_bk.nama + ' (' + data.assigned_guru_bk.nip + ')';
                        displayElement.style.color = '#333';
                        // For guru_bk, immediately load kegiatan
                        loadKegiatan();
                    }
                } else if (displayElement && userRole === 'guru_bk') {
                    displayElement.textContent = 'Belum ada guru BK yang ditugaskan untuk tanggal ini';
                    displayElement.style.color = '#999';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (displayElement && userRole === 'guru_bk') {
                    displayElement.textContent = 'Error memuat data guru BK';
                    displayElement.style.color = '#c00';
                }
            });
    }

    function loadKegiatan() {
        const tanggal = document.getElementById('inputDate').value;
        const selectedGuruBK = document.getElementById('selectGuruBK')?.value;

        // Validasi
        if (!tanggal) {
            alert('Pilih tanggal terlebih dahulu');
            return;
        }

        if (userRole === 'admin') {
            if (!selectedGuruBK) {
                document.getElementById('tabelKegiatanBody').innerHTML = '<tr><td colspan="9" style="padding: 20px; text-align: center; color: #999;">Pilih Guru BK terlebih dahulu</td></tr>';
                document.getElementById('adminButtonContainerTableHeader').style.display = 'none';
                document.getElementById('adminButtonContainerBottom').style.display = 'none';
                return;
            }
            // Show buttons when guru_bk is selected
            document.getElementById('adminButtonContainerTableHeader').style.display = 'inline-block';
            document.getElementById('adminButtonContainerBottom').style.display = 'block';
        }

        // Fetch kegiatan dari backend
        const params = new URLSearchParams({
            tanggal: tanggal,
            id_guru_bk: selectedGuruBK || 'current'
        });

        fetch('../../backend/pages/get_kegiatan_harian.php?' + params)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderKegiatan(data.kegiatan);
                    kegiatanCount = data.kegiatan.length;
                } else {
                    console.error('Error:', data.message);
                    document.getElementById('tabelKegiatanBody').innerHTML = '<tr><td colspan="9" style="padding: 20px; text-align: center; color: #999;">Belum ada kegiatan untuk tanggal ini</td></tr>';
                    kegiatanCount = 0;
                }
            })
            .catch(error => {
                console.error('Error loading kegiatan:', error);
                document.getElementById('tabelKegiatanBody').innerHTML = '<tr><td colspan="7" style="padding: 20px; text-align: center; color: #c00;">Gagal memuat kegiatan</td></tr>';
            });
    }

    function renderKegiatan(kegiatan) {
        const tbody = document.getElementById('tabelKegiatanBody');
        tbody.innerHTML = '';

        if (kegiatan.length === 0 && userRole === 'guru_bk') {
            tbody.innerHTML = '<tr><td colspan="9" style="padding: 20px; text-align: center; color: #999;">Belum ada kegiatan untuk tanggal ini</td></tr>';
            return;
        } else if (kegiatan.length === 0 && userRole === 'admin') {
            tbody.innerHTML = '<tr><td colspan="9" style="padding: 20px; text-align: center; color: #999;">Belum ada kegiatan untuk guru BK ini pada tanggal ini</td></tr>';
            return;
        }

        kegiatan.forEach((k, index) => {
            const row = document.createElement('tr');
            row.dataset.id = k.id_kegiatan || 'new';
            row.style.borderBottom = '1px solid #ddd';

            // Format tanggal
            const tglObj = new Date(k.tanggal + 'T00:00:00');
            const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][tglObj.getDay()];
            const tglFormat = tglObj.toLocaleDateString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit' });
            const hariTgl = hari + ', ' + tglFormat;

            let rowHTML = `
                <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">${index + 1}</td>
                <td style="padding: 12px; border: 1px solid #ddd; text-align: center; font-size: 12px;">${hariTgl}</td>
                <td style="padding: 12px; border: 1px solid #ddd;">
                    ${userRole === 'guru_bk' ? `<input type="time" class="row-input" value="${k.waktu_mulai || ''}">` : (k.waktu_mulai || '-')}
                </td>
                <td style="padding: 12px; border: 1px solid #ddd;">
                    ${userRole === 'guru_bk' ? `<input type="time" class="row-input" value="${k.waktu_selesai || ''}">` : (k.waktu_selesai || '-')}
                </td>
                <td style="padding: 12px; border: 1px solid #ddd;">
                    ${userRole === 'guru_bk' ? `<textarea class="row-input" style="min-height: 50px;">${k.uraian_kegiatan || ''}</textarea>` : (k.uraian_kegiatan || '-')}
                </td>
                <td style="padding: 12px; border: 1px solid #ddd;">
                    ${userRole === 'guru_bk' ? `<input type="text" class="row-input" value="${k.jenis_layanan || ''}">` : (k.jenis_layanan || '-')}
                </td>
                <td style="padding: 12px; border: 1px solid #ddd;">
                    ${userRole === 'guru_bk' ? `<input type="text" class="row-input" value="${k.sasaran_layanan || ''}">` : (k.sasaran_layanan || '-')}
                </td>
                <td style="padding: 12px; border: 1px solid #ddd;">
                    ${userRole === 'guru_bk' ? `<input type="text" class="row-input" value="${k.bidang_kode_layanan || ''}">` : (k.bidang_kode_layanan || '-')}
                </td>
                <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                    ${userRole === 'guru_bk' ? `<button onclick="hapusKegiatan(this)" style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px;">Hapus</button>` : '-'}
                </td>
            `;
            
            row.innerHTML = rowHTML;
            tbody.appendChild(row);
        });
    }

    function tambahKegiatan() {
        const tbody = document.getElementById('tabelKegiatanBody');
        
        // Jika kosong, replace dengan row baru
        if (tbody.innerHTML.includes('Belum ada kegiatan')) {
            tbody.innerHTML = '';
        }

        const tanggal = document.getElementById('inputDate').value;
        const tglObj = new Date(tanggal + 'T00:00:00');
        const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][tglObj.getDay()];
        const tglFormat = tglObj.toLocaleDateString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit' });
        const hariTgl = hari + ', ' + tglFormat;

        const row = document.createElement('tr');
        row.dataset.id = 'new';
        row.style.borderBottom = '1px solid #ddd';
        
        row.innerHTML = `
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">${tbody.children.length + 1}</td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center; font-size: 12px;">${hariTgl}</td>
            <td style="padding: 12px; border: 1px solid #ddd;">
                <input type="time" class="row-input" value="">
            </td>
            <td style="padding: 12px; border: 1px solid #ddd;">
                <input type="time" class="row-input" value="">
            </td>
            <td style="padding: 12px; border: 1px solid #ddd;">
                <textarea class="row-input" style="min-height: 50px;"></textarea>
            </td>
            <td style="padding: 12px; border: 1px solid #ddd;">
                <input type="text" class="row-input" value="">
            </td>
            <td style="padding: 12px; border: 1px solid #ddd;">
                <input type="text" class="row-input" value="">
            </td>
            <td style="padding: 12px; border: 1px solid #ddd;">
                <input type="text" class="row-input" value="">
            </td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                <button onclick="hapusKegiatan(this)" style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px;">
                    Hapus
                </button>
            </td>
        `;

        tbody.appendChild(row);
    }

    function hapusKegiatan(btn) {
        btn.closest('tr').remove();
        // Reindex nomor
        const tbody = document.getElementById('tabelKegiatanBody');
        Array.from(tbody.querySelectorAll('tr')).forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    function simpanKegiatan() {
        const tanggal = document.getElementById('inputDate').value;
        const selectedGuruBK = document.getElementById('selectGuruBK')?.value;
        const tbody = document.getElementById('tabelKegiatanBody');

        // Validasi tanggal
        if (!tanggal) {
            alert('Pilih tanggal terlebih dahulu');
            return;
        }

        // Both Admin and Guru BK: Save kegiatan data
        let guruBKId = selectedGuruBK;
        
        if (userRole === 'admin') {
            if (!selectedGuruBK) {
                alert('Pilih Guru BK terlebih dahulu');
                return;
            }
        }

        // Kumpulkan data kegiatan
        const kegiatan = [];
        tbody.querySelectorAll('tr').forEach(row => {
            const inputs = row.querySelectorAll('input, textarea');
            const data = {
                id_kegiatan: row.dataset.id,
                tanggal: tanggal,
                waktu_mulai: inputs[0].value,
                waktu_selesai: inputs[1].value,
                uraian_kegiatan: inputs[2].value,
                jenis_layanan: inputs[3].value,
                sasaran_layanan: inputs[4].value,
                bidang_kode_layanan: inputs[5].value
            };

            // Validasi minimal ada waktu mulai atau uraian
            if (data.waktu_mulai || data.uraian_kegiatan) {
                kegiatan.push(data);
            }
        });

        if (kegiatan.length === 0) {
            alert('Tambahkan minimal satu kegiatan');
            return;
        }

        // Send to backend
        const formData = new FormData();
        formData.append('mode', 'save_kegiatan');
        formData.append('tanggal', tanggal);
        formData.append('id_guru_bk', guruBKId);
        formData.append('kegiatan_json', JSON.stringify(kegiatan));

        fetch('../../backend/pages/save_kegiatan_harian.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('✓ Kegiatan berhasil disimpan');
                        loadKegiatan();
                    } else {
                        alert('Error: ' + data.message);
                        console.error('Response:', data);
                    }
                } catch (e) {
                    console.error('Parse error:', e, 'Response:', text);
                    alert('Error menyimpan kegiatan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error menyimpan kegiatan');
            });
    }

    function resetForm() {
        if (confirm('Reset form? Perubahan belum disimpan akan hilang')) {
            loadKegiatan();
        }
    }
</script>


<?php
include '../layouts/footer.php';
?>
