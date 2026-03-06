<?php
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
$school_name = $school['nama_sekolah'] ?? '';

// Kelas terpilih dari GET parameter
$kelas_terpilih_pribadi = isset($_GET['kelas_pribadi']) ? htmlspecialchars($_GET['kelas_pribadi']) : null;
?>

<!-- Header -->


<!-- Selector + Action Bar -->
<div style="background:#f8f9fa;padding:1rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;border:1px solid #ddd;display:flex;align-items:center;flex-wrap:wrap;gap:.75rem">
    <label style="font-weight:600;font-size:.9rem;color:#333;white-space:nowrap">Pilih Kelas:</label>
    <select id="kelasSelectPribadi"
        style="padding:.55rem .8rem;border:1px solid #ddd;border-radius:5px;font-size:.9rem;min-width:180px">
        <option value="">-- Pilih Kelas --</option>
        <?php foreach ($kelas_list as $kls): ?>
            <option value="<?= htmlspecialchars($kls) ?>"
                <?= ($kelas_terpilih_pribadi === $kls) ? 'selected' : '' ?>>
                <?= htmlspecialchars($kls) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button onclick="pilihKelasPribadi()"
        style="padding:.55rem 1.1rem;background:#4472C4;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600;white-space:nowrap">
        <i class="fas fa-search" style="margin-right:5px"></i>Tampilkan
    </button>
</div>

<!-- Konten -->
<?php if ($kelas_terpilih_pribadi): ?>

    <!-- Info bar kelas terpilih -->
    <div style="background:#f8f9fa;padding:.85rem 1.25rem;border-radius:8px;margin-bottom:1rem;border-left:4px solid #4472C4">
        <span style="font-weight:600;color:#333;font-size:.95rem">
            Kelas <strong><?= htmlspecialchars($kelas_terpilih_pribadi) ?></strong>
            &nbsp;&mdash;&nbsp;
            Total: <span id="siswaCount" style="color:#4472C4">...</span> siswa
        </span>
    </div>

    <!-- Status Message -->
    <div id="statusMessage" style="display:none;margin-bottom:1rem;padding:.75rem 1rem;border-radius:5px;border-left:4px solid;font-size:.88rem"></div>

    <!-- Tabel -->
    <div style="background:white;border-radius:8px;border:1px solid #ddd;overflow:hidden">
        <div style="overflow-x:auto;width:100%">
            <table id="tabelDataPribadi" style="border-collapse:collapse;font-size:.83rem;width:max-content;min-width:100%">
                <thead>
                    <tr style="background:#FFC000;color:#000;font-weight:700">
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;min-width:44px;white-space:nowrap">No</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:190px;white-space:nowrap">Nama</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;min-width:80px;white-space:nowrap">L/P</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:220px;white-space:nowrap">Tempat/Tgl Lahir</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:250px;white-space:nowrap">Alamat</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:140px;white-space:nowrap">Agama</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:190px;white-space:nowrap">Sekolah Asal</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:150px;white-space:nowrap">No. HP</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:190px;white-space:nowrap">Nama Ortu</th>
                        <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:150px;white-space:nowrap">No. HP Ortu</th>
                    </tr>
                </thead>
                <tbody id="tabelSiswaBody">
                    <tr><td colspan="10" style="padding:1.5rem;text-align:center;color:#999">
                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                    </td></tr>
                </tbody>
            </table>
        </div>

        <!-- Tombol Aksi — di bawah tabel, tidak perlu scroll samping -->
        <div style="padding:1rem 1.25rem;border-top:1px solid #ddd;display:flex;gap:.75rem;flex-wrap:wrap">
            <button onclick="simpanSemuaData()" id="btnSimpanPribadi"
                style="padding:.6rem 1.5rem;background:#28a745;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600">
                <i class="fas fa-save" style="margin-right:6px"></i>Simpan Semua Data
            </button>
            <button onclick="resetFormPribadi()"
                style="padding:.6rem 1.5rem;background:#6c757d;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600">
                <i class="fas fa-undo" style="margin-right:6px"></i>Reset
            </button>
            <a href="../rekap/data_siswa_content.php"
                style="padding:.6rem 1.5rem;background:#17a2b8;color:white;text-decoration:none;border-radius:5px;font-size:.88rem;font-weight:600;display:inline-flex;align-items:center">
                <i class="fas fa-chart-bar" style="margin-right:6px"></i>Rekap
            </a>
        </div>
    </div>

<?php else: ?>

    <!-- Empty state -->
    <div style="background:white;border:2px dashed #ddd;border-radius:12px;padding:3rem;text-align:center">
        <div style="font-size:2.5rem;margin-bottom:1rem;color:#ccc"><i class="fas fa-id-card"></i></div>
        <p style="color:#999;margin:0;font-size:1rem">Pilih kelas untuk menampilkan data pribadi siswa</p>
    </div>

<?php endif; ?>

<style>
#tabelDataPribadi input[type="text"],
#tabelDataPribadi input[type="date"],
#tabelDataPribadi select,
#tabelDataPribadi textarea {
    font-family: inherit;
    font-size: .82rem;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    background: #fafafa;
    width: 100%;
    display: block;
}
#tabelDataPribadi input[type="text"]:focus,
#tabelDataPribadi input[type="date"]:focus,
#tabelDataPribadi select:focus,
#tabelDataPribadi textarea:focus {
    outline: none;
    border-color: #4472C4;
    background: #fff;
    box-shadow: 0 0 0 2px rgba(68,114,196,.15);
}
#tabelDataPribadi textarea { resize: vertical; min-height: 70px; }
#tabelDataPribadi td { padding: 10px 8px; border: 1px solid #ddd; vertical-align: top; }
#tabelDataPribadi tr:nth-child(even) { background: #f9f9f9; }
#tabelDataPribadi tr:hover { background: #f0f5ff; }
.label-input-pribadi { font-size: 11px; color: #888; margin-bottom: 3px; display: block; }
</style>

<script>
let siswaDataPribadi = [];
const kelasTerpilihPribadi = '<?= $kelas_terpilih_pribadi ?? '' ?>';

document.addEventListener('DOMContentLoaded', function () {
    if (kelasTerpilihPribadi) {
        loadDataPribadi(kelasTerpilihPribadi);
    }
});

function pilihKelasPribadi() {
    const sel   = document.getElementById('kelasSelectPribadi');
    const kelas = sel.value;
    if (!kelas) { alert('Pilih kelas terlebih dahulu'); return; }

    const url = new URL(window.location.href);
    url.searchParams.set('kelas_pribadi', kelas);
    window.location.href = url.toString();
}

// Tekan Enter di dropdown juga trigger tampilkan
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('kelasSelectPribadi');
    if (sel) {
        sel.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') pilihKelasPribadi();
        });
    }
});

function loadDataPribadi(kelas) {
    const tbody = document.getElementById('tabelSiswaBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="10" style="padding:1.5rem;text-align:center;color:#999"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';

    fetch('../../../backend/pages/get_siswa_by_kelas.php?kelas=' + encodeURIComponent(kelas))
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            if (data.success) {
                const countEl = document.getElementById('siswaCount');
                if (data.count === 0) {
                    tbody.innerHTML = '<tr><td colspan="10" style="padding:1.5rem;text-align:center;color:#999"><i class="fas fa-inbox" style="margin-right:5px"></i>Belum ada siswa di kelas ' + kelas + '</td></tr>';
                    if (countEl) countEl.textContent = '0';
                } else {
                    siswaDataPribadi = data.siswa;
                    if (countEl) countEl.textContent = data.count;
                    renderTabelPribadi(data.siswa);
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="10" style="padding:1.5rem;text-align:center;color:#dc3545">' + (data.message || 'Gagal memuat data') + '</td></tr>';
            }
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="10" style="padding:1.5rem;text-align:center;color:#dc3545"><i class="fas fa-exclamation-circle" style="margin-right:5px"></i>Gagal memuat: ' + err.message + '</td></tr>';
        });
}

function renderTabelPribadi(siswa) {
    const tbody = document.getElementById('tabelSiswaBody');
    let html = '';

    siswa.forEach((row, index) => {
        html += `
        <tr data-id="${row.id_siswa}">
            <td style="text-align:center;vertical-align:middle">${index + 1}</td>
            <td style="vertical-align:middle"><strong>${escPribadi(row.nama_siswa)}</strong></td>
            <td style="text-align:center;vertical-align:middle">
                <select class="col-jk">
                    <option value="L" ${row.jk === 'L' ? 'selected' : ''}>L</option>
                    <option value="P" ${row.jk === 'P' ? 'selected' : ''}>P</option>
                </select>
            </td>
            <td>
                <span class="label-input-pribadi">Tempat Lahir</span>
                <input type="text" class="col-tempat_lahir" value="${escPribadi(row.tempat_lahir || '')}" placeholder="Kota" style="margin-bottom:6px">
                <span class="label-input-pribadi">Tanggal Lahir</span>
                <input type="date" class="col-tgl_lahir" value="${row.tgl_lahir || ''}">
            </td>
            <td>
                <span class="label-input-pribadi">Alamat Lengkap</span>
                <textarea class="col-alamat" placeholder="Alamat lengkap...">${escPribadi(row.alamat || '')}</textarea>
            </td>
            <td>
                <span class="label-input-pribadi">Agama</span>
                <select class="col-agama">
                    <option value="">-- Pilih --</option>
                    <option value="Islam"    ${row.agama === 'Islam'    ? 'selected' : ''}>Islam</option>
                    <option value="Kristen"  ${row.agama === 'Kristen'  ? 'selected' : ''}>Kristen</option>
                    <option value="Katolik"  ${row.agama === 'Katolik'  ? 'selected' : ''}>Katolik</option>
                    <option value="Hindu"    ${row.agama === 'Hindu'    ? 'selected' : ''}>Hindu</option>
                    <option value="Buddha"   ${row.agama === 'Buddha'   ? 'selected' : ''}>Buddha</option>
                    <option value="Konghucu" ${row.agama === 'Konghucu' ? 'selected' : ''}>Konghucu</option>
                </select>
            </td>
            <td>
                <span class="label-input-pribadi">Sekolah Asal</span>
                <input type="text" class="col-sekolah_asal" value="${escPribadi(row.sekolah_asal || '')}" placeholder="Nama sekolah asal">
            </td>
            <td>
                <span class="label-input-pribadi">No. HP Siswa</span>
                <input type="text" class="col-no_hp" value="${escPribadi(row.no_hp || '')}" placeholder="08xx-xxxx-xxxx">
            </td>
            <td>
                <span class="label-input-pribadi">Nama Orangtua / Wali</span>
                <input type="text" class="col-nama_ortu" value="${escPribadi(row.nama_ortu || '')}" placeholder="Nama orangtua/wali">
            </td>
            <td>
                <span class="label-input-pribadi">No. HP Orangtua</span>
                <input type="text" class="col-no_hp_ortu" value="${escPribadi(row.no_hp_ortu || '')}" placeholder="08xx-xxxx-xxxx">
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function simpanSemuaData() {
    if (!siswaDataPribadi.length) {
        showStatusPribadi('error', 'Tidak ada data untuk disimpan');
        return;
    }

    const tbody = document.getElementById('tabelSiswaBody');
    const rows  = tbody.querySelectorAll('tr[data-id]');
    const btn   = document.getElementById('btnSimpanPribadi');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px"></i>Menyimpan...';

    let savedCount = 0, errorCount = 0;
    const promises = [];

    rows.forEach(row => {
        const fd = new FormData();
        fd.append('id_siswa',     parseInt(row.dataset.id));
        fd.append('jk',           row.querySelector('.col-jk').value);
        fd.append('tempat_lahir', row.querySelector('.col-tempat_lahir').value);
        fd.append('tgl_lahir',    row.querySelector('.col-tgl_lahir').value);
        fd.append('alamat',       row.querySelector('.col-alamat').value);
        fd.append('agama',        row.querySelector('.col-agama').value);
        fd.append('sekolah_asal', row.querySelector('.col-sekolah_asal').value);
        fd.append('no_hp',        row.querySelector('.col-no_hp').value);
        fd.append('nama_ortu',    row.querySelector('.col-nama_ortu').value);
        fd.append('no_hp_ortu',   row.querySelector('.col-no_hp_ortu').value);

        const p = fetch('../../../backend/pages/save_data_pribadi_inline.php', {
            method: 'POST', body: fd
        })
        .then(r => r.json())
        .then(data => { data.success ? savedCount++ : errorCount++; })
        .catch(() => errorCount++);

        promises.push(p);
    });

    Promise.all(promises).then(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save" style="margin-right:6px"></i>Simpan Semua Data';
        if (errorCount === 0) {
            showStatusPribadi('success', '✓ ' + savedCount + ' data pribadi siswa berhasil disimpan');
        } else {
            showStatusPribadi('error', '⚠ ' + savedCount + ' disimpan, ' + errorCount + ' gagal');
        }
    });
}

function resetFormPribadi() {
    if (confirm('Reset form? Data akan dikembalikan ke state awal.')) {
        loadDataPribadi(kelasTerpilihPribadi);
    }
}

function showStatusPribadi(type, message) {
    const div = document.getElementById('statusMessage');
    div.textContent = message;
    div.style.cssText = type === 'success'
        ? 'display:block;background:#d4edda;color:#155724;border-left:4px solid #28a745;margin-bottom:1rem;padding:.75rem 1rem;border-radius:5px;font-size:.88rem'
        : 'display:block;background:#f8d7da;color:#721c24;border-left:4px solid #dc3545;margin-bottom:1rem;padding:.75rem 1rem;border-radius:5px;font-size:.88rem';
    setTimeout(() => div.style.display = 'none', 4000);
}

function escPribadi(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>