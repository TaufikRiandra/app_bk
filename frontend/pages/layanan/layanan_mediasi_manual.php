<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../../auth/login.php");
    exit;
}

include '../../layouts/header.php';
include '../../layouts/sidebar.php';
include '../../../backend/config/database.php';
include '../../../backend/config/MediasiSetupHelper.php';

$setupHelper = checkMediasiSetup($conn);
$setupStatus = $setupHelper->getStatus();

$user_role = $_SESSION['role'] ?? 'guru_bk';
$session_id_gbk = intval($_SESSION['id_guru_bk'] ?? 0);
$today_date = date('Y-m-d');

// Get current guru BK info
$current_guru_bk = [];
if ($session_id_gbk) {
    $mg = mysqli_prepare($conn, 'SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = ?');
    mysqli_stmt_bind_param($mg, 'i', $session_id_gbk);
    mysqli_stmt_execute($mg);
    $current_guru_bk = mysqli_fetch_assoc(mysqli_stmt_get_result($mg)) ?: [];
}

// Get school info
$sr = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school_name = ($sr && $s = mysqli_fetch_assoc($sr)) ? $s['nama_sekolah'] : '';

$tempo_data = $_SESSION['mediasi_data'] ?? [];
unset($_SESSION['mediasi_data']);
?>

<div class="content">

    <!-- Header -->
    <div style="background:var(--brand,#4472C4);color:white;padding:1.25rem 1.5rem;border-radius:8px;margin-bottom:1.25rem">
        <h2 style="margin:0 0 4px;font-size:1.1rem;font-weight:700">
            <i class="fas fa-handshake" style="margin-right:8px"></i>LAYANAN MEDIASI
        </h2>
        <p style="margin:0;font-size:.85rem;opacity:.9">Bimbingan dan Konseling</p>
        <p style="margin:0;font-size:.82rem;opacity:.8"><?= htmlspecialchars($school_name) ?></p>
    </div>

    <!-- Info Guru -->
    <div style="background:#f8f9fa;padding:1rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;border-left:4px solid var(--brand,#4472C4)">
        <div style="font-size:.78rem;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px">Anda Login Sebagai</div>
        <div style="font-weight:600;font-size:.95rem;color:#333">
            <i class="fas fa-user-tie" style="margin-right:6px;color:var(--brand,#4472C4)"></i>
            <?= htmlspecialchars($current_guru_bk['nama'] ?? ($_SESSION['username'] ?? '-')) ?>
            <span style="color:#888;font-weight:400;font-size:.85rem">(<?= htmlspecialchars($current_guru_bk['nip'] ?? $user_role) ?>)</span>
        </div>
    </div>

    <!-- Pilih Tanggal -->
    <div style="background:white;padding:1rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;border:1px solid #ddd">
        <label style="display:block;margin-bottom:.4rem;font-weight:600;font-size:.88rem;color:#555">
            <i class="fas fa-calendar-alt" style="margin-right:6px;color:var(--brand,#4472C4)"></i>Pilih Tanggal
        </label>
        <input type="date" id="inputDate" value="<?= $today_date ?>"
            style="padding:.6rem .8rem;border:1px solid #ddd;border-radius:5px;font-size:.9rem;width:auto">
    </div>

    <!-- Tabel Mediasi -->
    <div style="background:white;border-radius:8px;border:1px solid #ddd;overflow:hidden">

        <!-- Toolbar -->
        <div style="background:#f5f5f5;padding:.85rem 1.25rem;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
            <h3 style="margin:0;font-size:.95rem;color:#333;font-weight:600">
                <i class="fas fa-list" style="margin-right:6px;color:var(--brand,#4472C4)"></i>Daftar Mediasi
            </h3>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                <button onclick="tambahMediasi()" id="btnTambah"
                    style="padding:.5rem 1rem;background:var(--brand,#4472C4);color:white;border:none;border-radius:5px;cursor:pointer;font-size:.85rem;font-weight:600">
                    <i class="fas fa-plus" style="margin-right:5px"></i>Tambah
                </button>
                <a href="../../../frontend/pages/rekap/rekap.php"
                    style="padding:.5rem 1rem;background:#17a2b8;color:white;text-decoration:none;border-radius:5px;font-size:.85rem;font-weight:600;display:inline-flex;align-items:center">
                    <i class="fas fa-chart-pie" style="margin-right:5px"></i>Rekap
                </a>
            </div>
        </div>

        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:.83rem">
            <thead>
                <tr style="background:#FFC000;color:#000;font-weight:700">
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:36px">No</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;min-width:110px">Tanggal</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:130px">Nama Pihak 1</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:75px">Kelas</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:150px">Masalah Pihak 1</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:130px">Nama Pihak 2</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:75px">Kelas</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:150px">Masalah Pihak 2</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:140px">Hasil Mediasi</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:170px">Dokumentasi</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:60px">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelMediasiBody">
                <tr><td colspan="11" style="padding:1.5rem;text-align:center;color:#999">Memuat data...</td></tr>
            </tbody>
        </table>
        </div>

        <!-- Footer Buttons -->
        <div style="padding:1rem 1.25rem;border-top:1px solid #ddd;display:flex;gap:.75rem;flex-wrap:wrap">
            <button onclick="simpanMediasi()" id="btnSimpan"
                style="padding:.6rem 1.5rem;background:#28a745;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600">
                <i class="fas fa-save" style="margin-right:6px"></i>Simpan Mediasi
            </button>
            <button onclick="resetData()"
                style="padding:.6rem 1.5rem;background:#6c757d;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600">
                <i class="fas fa-undo" style="margin-right:6px"></i>Reset
            </button>
        </div>

    </div><!-- /tabel -->

</div><!-- /content -->

<style>
.content { padding: 1.25rem; max-width: 1280px; margin: 0 auto; }
.row-input {
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: .82rem;
    width: 100%;
    box-sizing: border-box;
    font-family: inherit;
    background: #fafafa;
}
.row-input:focus {
    outline: none;
    border-color: #4472C4;
    box-shadow: 0 0 0 2px rgba(68,114,196,.15);
    background: #fff;
}
textarea.row-input { resize: vertical; min-height: 56px; }
</style>

<script>
const USER_ROLE       = '<?= $user_role ?>';
const TODAY_DATE      = '<?= $today_date ?>';
const CURRENT_GURU_BK = <?= json_encode($current_guru_bk) ?>;

let mediasi_data        = [];
let current_guru_bk_id  = CURRENT_GURU_BK.id_guru_bk || null;
let file_uploads        = {};
let deleted_mediasi_ids = [];

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('inputDate').addEventListener('change', loadMediasi);

    // Admin tanpa guru_bk tetap bisa load (lihat semua)
    loadMediasi();
});

/* ─────────────────────────────────────────────────
   Load Mediasi
   ───────────────────────────────────────────────── */
function loadMediasi() {
    const tanggal = document.getElementById('inputDate').value;
    const tbody   = document.getElementById('tabelMediasiBody');
    const colspan = USER_ROLE === 'admin' ? '12' : '11';

    if (!tanggal) {
        tbody.innerHTML = `<tr><td colspan="${colspan}" style="padding:1.5rem;text-align:center;color:#999">Pilih tanggal terlebih dahulu</td></tr>`;
        return;
    }

    tbody.innerHTML = `<tr><td colspan="${colspan}" style="padding:1.5rem;text-align:center;color:#999"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>`;

    file_uploads        = {};
    deleted_mediasi_ids = [];

    // Admin: load semua (tanpa filter id_guru_bk), guru_bk: filter miliknya
    let url = '../../../backend/pages/get_mediasi.php?tanggal=' + encodeURIComponent(tanggal)
            + '&role=' + encodeURIComponent(USER_ROLE);
    if (USER_ROLE === 'guru_bk' && current_guru_bk_id) {
        url += '&id_guru_bk=' + current_guru_bk_id;
    }
    fetch(url)
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            mediasi_data = Array.isArray(data) ? data : [];
            renderTable();
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="${colspan}" style="padding:1.5rem;text-align:center;color:#dc3545"><i class="fas fa-exclamation-circle" style="margin-right:5px"></i>Gagal memuat data: ${err.message}</td></tr>`;
        });
}

/* ─────────────────────────────────────────────────
   Render Tabel
   ───────────────────────────────────────────────── */
function renderTable() {
    const tbody   = document.getElementById('tabelMediasiBody');
    const isAdmin = USER_ROLE === 'admin';
    const colspan = isAdmin ? '12' : '11';

    // Tambah kolom Guru BK di header jika admin
    const thead = document.querySelector('thead tr');
    if (isAdmin && !document.getElementById('thGuruBK')) {
        const th = document.createElement('th');
        th.id = 'thGuruBK';
        th.style.cssText = 'padding:.75rem .6rem;border:1px solid #ddd;text-align:left;min-width:120px';
        th.textContent = 'Guru BK';
        thead.insertBefore(th, thead.children[1]); // setelah No
    }

    if (!mediasi_data.length) {
        tbody.innerHTML = `<tr><td colspan="${colspan}" style="padding:1.5rem;text-align:center;color:#999"><i class="fas fa-inbox" style="margin-right:5px"></i>Belum ada data mediasi. ${!isAdmin || current_guru_bk_id ? 'Klik Tambah untuk mulai.' : ''}</td></tr>`;
        return;
    }

    let html = '';
    mediasi_data.forEach((row, index) => {
        const isNew      = !row.id_mediasi;
        const isReadOnly = false;

        html += `
        <tr id="row_${index}" data-index="${index}" data-id="${row.id_mediasi || ''}"
            style="border-bottom:1px solid #eee;${isNew ? 'background:#f0f8ff;' : ''}">
            <td style="padding:.6rem;border:1px solid #eee;text-align:center">${index + 1}</td>
            ${isAdmin ? `<td style="padding:.5rem;border:1px solid #eee;font-size:.8rem;color:#555">${esc(row.guru_bk_nama || '-')}</td>` : ''}
            <td style="padding:.5rem;border:1px solid #eee">
                <input type="date" class="row-input" value="${esc(row.tanggal || '')}"
                    data-index="${index}" data-field="tanggal" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <input type="text" class="row-input" value="${esc(row.nama_pihak_1 || '')}"
                    placeholder="Nama pihak 1" data-index="${index}" data-field="nama_pihak_1" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <input type="text" class="row-input" value="${esc(row.kelas_pihak_1 || '')}"
                    placeholder="Kelas" data-index="${index}" data-field="kelas_pihak_1" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <textarea class="row-input" placeholder="Uraian masalah..."
                    data-index="${index}" data-field="masalah_pihak_1" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>${esc(row.masalah_pihak_1 || '')}</textarea>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <input type="text" class="row-input" value="${esc(row.nama_pihak_2 || '')}"
                    placeholder="Nama pihak 2" data-index="${index}" data-field="nama_pihak_2" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <input type="text" class="row-input" value="${esc(row.kelas_pihak_2 || '')}"
                    placeholder="Kelas" data-index="${index}" data-field="kelas_pihak_2" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <textarea class="row-input" placeholder="Uraian masalah..."
                    data-index="${index}" data-field="masalah_pihak_2" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>${esc(row.masalah_pihak_2 || '')}</textarea>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <input type="text" class="row-input" value="${esc(row.hasil_mediasi || '')}"
                    placeholder="Hasil..." data-index="${index}" data-field="hasil_mediasi" oninput="updateField(this)" ${isReadOnly ? 'readonly' : ''}>
            </td>
            <td style="padding:.5rem;border:1px solid #eee">
                <div style="display:flex;flex-direction:column;gap:4px">
                    <span id="fileinfo_${index}" style="font-size:.75rem;color:${row.foto ? '#28a745' : '#aaa'}">
                        ${row.foto
                            ? `<i class="fas fa-check-circle" style="margin-right:3px"></i>${esc(row.foto)}`
                            : `<i class="fas fa-image" style="margin-right:3px"></i>Belum ada file`
                        }
                    </span>
                    ${!isReadOnly ? `<input type="file" accept="image/*" data-index="${index}" onchange="handleFileUpload(event)"
                        style="font-size:.75rem;padding:3px 0;border:none;background:transparent;cursor:pointer">` : ''}
                </div>
            </td>
            <td style="padding:.5rem;border:1px solid #eee;text-align:center">
                ${!isReadOnly ? `<button onclick="hapusMediasi(${index})" title="Hapus"
                    style="background:#dc3545;color:white;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:.8rem">
                    <i class="fas fa-trash"></i>
                </button>` : '-'}
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

/* ─────────────────────────────────────────────────
   Update field
   ───────────────────────────────────────────────── */
function updateField(input) {
    const index = parseInt(input.dataset.index);
    const field = input.dataset.field;
    if (!isNaN(index) && mediasi_data[index] !== undefined) {
        mediasi_data[index][field] = input.value;
    }
}

/* ─────────────────────────────────────────────────
   Tambah Baris
   ───────────────────────────────────────────────── */
function tambahMediasi() {
    const tanggal = document.getElementById('inputDate').value;
    if (!tanggal) { showToast('error', 'Pilih tanggal terlebih dahulu'); return; }

    mediasi_data.push({
        id_mediasi:      null,
        tanggal,
        id_guru_bk:      current_guru_bk_id || null, // null jika admin murni
        nama_pihak_1:    '',
        kelas_pihak_1:   '',
        masalah_pihak_1: '',
        nama_pihak_2:    '',
        kelas_pihak_2:   '',
        masalah_pihak_2: '',
        hasil_mediasi:   '',
        foto:            ''
    });
    renderTable();

    const tbody   = document.getElementById('tabelMediasiBody');
    const lastRow = tbody.lastElementChild;
    if (lastRow) lastRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/* ─────────────────────────────────────────────────
   Hapus Baris
   ───────────────────────────────────────────────── */
function hapusMediasi(index) {
    if (!confirm('Hapus data mediasi ini?')) return;

    const row = mediasi_data[index];
    if (row && row.id_mediasi) {
        deleted_mediasi_ids.push(row.id_mediasi);
    }

    mediasi_data.splice(index, 1);
    delete file_uploads[index];
    renderTable();
}

/* ─────────────────────────────────────────────────
   File Upload
   ───────────────────────────────────────────────── */
function handleFileUpload(event) {
    const input = event.target;
    const file  = input.files[0];
    if (!file) return;

    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
        showToast('error', 'Hanya file gambar (JPG, PNG, GIF, WebP) yang diizinkan');
        input.value = '';
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        showToast('error', 'Ukuran file terlalu besar (maksimal 5MB)');
        input.value = '';
        return;
    }

    const index = parseInt(input.dataset.index);
    file_uploads[index] = file;

    const fileSpan = document.getElementById('fileinfo_' + index);
    if (fileSpan) {
        fileSpan.innerHTML = `<i class="fas fa-check-circle" style="margin-right:3px"></i>${file.name} (${(file.size/1024).toFixed(1)} KB)`;
        fileSpan.style.color = '#28a745';
    }
}

/* ─────────────────────────────────────────────────
   Simpan
   ───────────────────────────────────────────────── */
async function simpanMediasi() {
    if (!mediasi_data.length) { showToast('error', 'Tidak ada data mediasi untuk disimpan'); return; }

    const isValid = mediasi_data.every(item => item.tanggal && item.nama_pihak_1 && item.nama_pihak_2);
    if (!isValid) { showToast('error', 'Tanggal, Nama Pihak 1, dan Nama Pihak 2 harus diisi untuk setiap baris'); return; }

    const btnSimpan = document.getElementById('btnSimpan');
    btnSimpan.disabled = true;
    btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px"></i>Menyimpan...';

    if (Object.keys(file_uploads).length > 0) {
        try {
            for (const [idx, file] of Object.entries(file_uploads)) {
                const fd = new FormData();
                fd.append('file', file);
                fd.append('upload_dir', 'mediasi');

                const res    = await fetch('../../../backend/pages/upload_file.php', { method: 'POST', body: fd });
                const result = await res.json();
                if (!result.success) throw new Error(result.error || 'Upload gagal');

                const i = parseInt(idx);
                if (mediasi_data[i]) mediasi_data[i].foto = result.filename;
            }
        } catch (err) {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = '<i class="fas fa-save" style="margin-right:6px"></i>Simpan Mediasi';
            showToast('error', 'Gagal mengupload file: ' + err.message);
            return;
        }
    }

    const fd = new FormData();
    fd.append('action', 'save_batch');
    fd.append('data', JSON.stringify(mediasi_data));
    fd.append('deleted_ids', JSON.stringify(deleted_mediasi_ids));

    fetch('../../../backend/pages/save_mediasi.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = '<i class="fas fa-save" style="margin-right:6px"></i>Simpan Mediasi';
            if (data.success) {
                showToast('success', 'Data mediasi berhasil disimpan');
                file_uploads        = {};
                deleted_mediasi_ids = [];
                loadMediasi();
            } else {
                showToast('error', 'Gagal menyimpan: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(() => {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = '<i class="fas fa-save" style="margin-right:6px"></i>Simpan Mediasi';
            showToast('error', 'Gagal terhubung ke server');
        });
}

/* ─────────────────────────────────────────────────
   Reset
   ───────────────────────────────────────────────── */
function resetData() {
    if (confirm('Reset semua data yang belum disimpan?')) {
        file_uploads        = {};
        deleted_mediasi_ids = [];
        loadMediasi();
    }
}

/* ─────────────────────────────────────────────────
   Helpers
   ───────────────────────────────────────────────── */
function esc(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showMsg(el, type, msg) {
    el.innerHTML = (type === 'success'
        ? '<i class="fas fa-check-circle" style="margin-right:5px"></i>'
        : '<i class="fas fa-exclamation-circle" style="margin-right:5px"></i>') + msg;
    el.style.cssText = type === 'success'
        ? 'display:block;background:#d4edda;color:#155724;margin-top:.6rem;font-size:.85rem;padding:.5rem .75rem;border-radius:5px'
        : 'display:block;background:#f8d7da;color:#721c24;margin-top:.6rem;font-size:.85rem;padding:.5rem .75rem;border-radius:5px';
    setTimeout(() => el.style.display = 'none', 4000);
}

function showToast(type, msg) {
    const t = document.createElement('div');
    t.innerHTML = (type === 'success'
        ? '<i class="fas fa-check-circle" style="margin-right:6px"></i>'
        : '<i class="fas fa-exclamation-circle" style="margin-right:6px"></i>') + msg;
    Object.assign(t.style, {
        position:'fixed', bottom:'1.5rem', right:'1.5rem', zIndex:'9999',
        padding:'.85rem 1.25rem', borderRadius:'8px', fontSize:'.88rem', fontWeight:'600',
        background: type === 'success' ? '#28a745' : '#dc3545', color:'white',
        boxShadow:'0 4px 16px rgba(0,0,0,.2)', transition:'opacity .4s'
    });
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 3500);
}
</script>

<?php include "../../layouts/footer.php"; ?>