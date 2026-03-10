<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: ../auth/login.php'); exit;
}
include '../layouts/header.php';
include '../layouts/sidebar.php';
include '../../backend/config/database.php';

$user_role      = $_SESSION['role'] ?? 'guru_bk';
$session_id_gbk = intval($_SESSION['id_guru_bk'] ?? 0);
$today_date     = date('Y-m-d');

// Info sekolah
$sr = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school_name = ($sr && $s = mysqli_fetch_assoc($sr)) ? $s['nama_sekolah'] : '';

// Daftar guru BK untuk admin
$guru_bk_list = [];
if ($user_role === 'admin') {
    $gr = mysqli_query($conn, "SELECT id_guru_bk, nama, nip FROM guru_bk ORDER BY nama");
    if ($gr) while ($row = mysqli_fetch_assoc($gr)) $guru_bk_list[] = $row;
}

// Info guru BK login saat ini
$my_guru_bk = null;
if ($user_role === 'guru_bk' && $session_id_gbk) {
    $mg = mysqli_prepare($conn, 'SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = ?');
    mysqli_stmt_bind_param($mg, 'i', $session_id_gbk);
    mysqli_stmt_execute($mg);
    $my_guru_bk = mysqli_fetch_assoc(mysqli_stmt_get_result($mg));
}
?>

<div class="content">

    <!-- Header -->
    <div style="background:var(--brand,#4472C4);color:white;padding:1.25rem 1.5rem;border-radius:8px;margin-bottom:1.25rem">
        <h2 style="margin:0 0 4px;font-size:1.1rem;font-weight:700">
            <i class="fas fa-calendar-alt" style="margin-right:8px"></i>KEGIATAN HARIAN
        </h2>
        <p style="margin:0;font-size:.85rem;opacity:.9">Bimbingan dan Konseling</p>
        <p><?= htmlspecialchars($school_name) ?></p>
    </div>

    <!-- Panel Guru BK -->
    <div style="background:#f8f9fa;padding:1rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;border-left:4px solid var(--brand,#4472C4)">
        <?php if ($user_role === 'admin'): ?>
        <label style="display:block;margin-bottom:.5rem;font-weight:600;font-size:.9rem;color:#333">
            <i class="fas fa-user-tie" style="margin-right:6px;color:var(--brand,#4472C4)"></i>Tetapkan Guru BK yang Bertugas
        </label>
        <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
            <select id="selectGuruBK" style="flex:1;min-width:220px;padding:.6rem .8rem;border:1px solid #ddd;border-radius:5px;font-size:.9rem">
                <option value="0">-- Tidak Ada --</option>
                <?php foreach ($guru_bk_list as $g): ?>
                    <option value="<?= $g['id_guru_bk'] ?>"><?= htmlspecialchars($g['nama']) ?> (<?= htmlspecialchars($g['nip']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <button onclick="tetapkanGuruBK()"
                style="padding:.6rem 1.25rem;background:var(--brand,#4472C4);color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600;white-space:nowrap">
                <i class="fas fa-check" style="margin-right:5px"></i>Tetapkan
            </button>
        </div>
        <div id="penetapanMsg" style="display:none;margin-top:.6rem;font-size:.85rem;padding:.5rem .75rem;border-radius:5px"></div>

        <?php else: ?>
        <!-- Info guru BK yang login -->
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <div>
                <div style="font-size:.78rem;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px">Anda Login Sebagai</div>
                <div style="font-weight:600;font-size:.95rem;color:#333">
                    <i class="fas fa-user-tie" style="margin-right:6px;color:var(--brand,#4472C4)"></i>
                    <?= htmlspecialchars($my_guru_bk['nama'] ?? '-') ?>
                    <span style="color:#888;font-weight:400;font-size:.85rem">(<?= htmlspecialchars($my_guru_bk['nip'] ?? '') ?>)</span>
                </div>
            </div>
            <div id="statusPenetapan" style="font-size:.82rem;padding:.35rem .8rem;border-radius:20px;background:#e9ecef;color:#555">
                <i class="fas fa-spinner fa-spin"></i> Memuat status...
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pilih Tanggal -->
    <div style="background:white;padding:1rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;border:1px solid #ddd">
        <label style="display:block;margin-bottom:.4rem;font-weight:600;font-size:.88rem;color:#555">
            <i class="fas fa-calendar-alt" style="margin-right:6px;color:var(--brand,#4472C4)"></i>Pilih Tanggal
        </label>
        <input type="date" id="inputDate" value="<?= $today_date ?>"
            style="padding:.6rem .8rem;border:1px solid #ddd;border-radius:5px;font-size:.9rem;width:auto">
    </div>

    <!-- Tabel Kegiatan -->
    <div style="background:white;border-radius:8px;border:1px solid #ddd;overflow:hidden">

        <!-- Toolbar -->
        <div style="background:#f5f5f5;padding:.85rem 1.25rem;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
            <h3 style="margin:0;font-size:.95rem;color:#333;font-weight:600">
                <i class="fas fa-list" style="margin-right:6px;color:var(--brand,#4472C4)"></i>Daftar Kegiatan
            </h3>
            <div id="toolbarButtons" style="display:none;display:flex;gap:.5rem;flex-wrap:wrap">
                <button onclick="tambahKegiatan()" id="btnTambah"
                    style="padding:.5rem 1rem;background:var(--brand,#4472C4);color:white;border:none;border-radius:5px;cursor:pointer;font-size:.85rem;font-weight:600;display:none">
                    <i class="fas fa-plus" style="margin-right:5px"></i>Tambah
                </button>
                <a href="../pages/rekap/rekap.php"
                    style="padding:.5rem 1rem;background:#17a2b8;color:white;text-decoration:none;border-radius:5px;font-size:.85rem;font-weight:600;display:none"
                    id="btnRekap">
                    <i class="fas fa-chart-bar" style="margin-right:5px"></i>Rekap
                </a>
            </div>
        </div>

        <!-- Info bar (status akses guru_bk) -->
        <div id="aksesBar" style="display:none;padding:.6rem 1.25rem;font-size:.84rem;border-bottom:1px solid #eee"></div>

        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:.83rem">
            <thead>
                <tr style="background:#FFC000;color:#000;font-weight:700">
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:36px">No</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:95px">Hari/Tgl</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:75px">Mulai</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:75px">Selesai</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left">Uraian Kegiatan</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;width:115px">Jenis Layanan</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;width:115px">Sasaran</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:left;width:115px">Bidang/Kode</th>
                    <th style="padding:.75rem .6rem;border:1px solid #ddd;text-align:center;width:55px">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelKegiatanBody">
                <tr><td colspan="9" style="padding:1.5rem;text-align:center;color:#999">Memuat data...</td></tr>
            </tbody>
        </table>
        </div>

        <!-- Footer Buttons -->
        <div id="footerButtons" style="display:none;padding:1rem 1.25rem;border-top:1px solid #ddd;display:flex;gap:.75rem;flex-wrap:wrap">
            <button onclick="simpanKegiatan()" id="btnSimpan"
                style="padding:.6rem 1.5rem;background:#28a745;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600;display:none">
                <i class="fas fa-save" style="margin-right:6px"></i>Simpan Kegiatan
            </button>
            <button onclick="resetForm()" id="btnReset"
                style="padding:.6rem 1.5rem;background:#6c757d;color:white;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;font-weight:600;display:none">
                <i class="fas fa-undo" style="margin-right:6px"></i>Reset
            </button>
        </div>

    </div><!-- /tabel -->

</div><!-- /content -->

<style>
.content { padding: 1.25rem; max-width: 1280px; margin: 0 auto; }
.row-input { padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: .82rem; width: 100%; box-sizing: border-box; font-family: inherit; }
.row-input:focus { outline: none; border-color: #4472C4; box-shadow: 0 0 0 2px rgba(68,114,196,.15); }
textarea.row-input { resize: vertical; min-height: 48px; }
</style>

<script>
const userRole      = '<?= $user_role ?>';
const myIdGuruBk    = <?= $session_id_gbk ?>; // 0 jika admin / tidak ada
let   canEdit       = false; // diupdate setiap loadKegiatan

document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('inputDate');
    dateInput.addEventListener('change', onDateChange);

    // Selalu tampilkan rekap button
    const btnRekap = document.getElementById('btnRekap');
    if (btnRekap) btnRekap.style.display = 'inline-flex';

    if (userRole === 'admin') {
        const sel = document.getElementById('selectGuruBK');
        sel.addEventListener('change', function () {
            loadKegiatan();
        });
    }

    loadAssignedGuruBK();
});

function onDateChange() {
    loadAssignedGuruBK();
}

/* ─────────────────────────────────────────────────
   Ambil penetapan untuk tanggal yang dipilih
   ───────────────────────────────────────────────── */
function loadAssignedGuruBK() {
    const tanggal = document.getElementById('inputDate').value;
    if (!tanggal) return;

    fetch('../../backend/pages/get_assigned_guru_bk.php?tanggal=' + tanggal)
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            const assigned = data.assigned_guru_bk; // null | 'none' | {id_guru_bk, nama, nip}

            if (userRole === 'admin') {
                const sel = document.getElementById('selectGuruBK');
                if (assigned && assigned !== 'none' && assigned.id_guru_bk) {
                    sel.value = assigned.id_guru_bk;
                } else if (assigned === 'none') {
                    sel.value = '0'; // "Tidak Ada"
                } else {
                    // Belum pernah ditetapkan — biarkan dropdown di posisi pertama
                    sel.value = sel.options[0].value;
                }
                loadKegiatan();

            } else {
                // Guru BK: cek apakah dia yang ditetapkan
                updateStatusBar(assigned);
                loadKegiatan(assigned);
            }
        })
        .catch(console.error);
}

function updateStatusBar(assigned) {
    const bar = document.getElementById('statusPenetapan');
    if (!bar) return;
    if (assigned && assigned !== 'none' && assigned.id_guru_bk == myIdGuruBk) {
        bar.innerHTML = '<i class="fas fa-check-circle" style="margin-right:4px"></i>Anda bertugas pada tanggal ini';
        bar.style.cssText = 'background:#d4edda;color:#155724;padding:.35rem .8rem;border-radius:20px;font-size:.82rem';
    } else if (assigned && assigned !== 'none') {
        bar.innerHTML = '<i class="fas fa-info-circle" style="margin-right:4px"></i>Yang bertugas: ' + assigned.nama;
        bar.style.cssText = 'background:#fff3cd;color:#856404;padding:.35rem .8rem;border-radius:20px;font-size:.82rem';
    } else if (assigned === 'none') {
        bar.innerHTML = '<i class="fas fa-minus-circle" style="margin-right:4px"></i>Tidak ada guru bertugas';
        bar.style.cssText = 'background:#f8d7da;color:#721c24;padding:.35rem .8rem;border-radius:20px;font-size:.82rem';
    } else {
        bar.innerHTML = '<i class="fas fa-question-circle" style="margin-right:4px"></i>Belum ditetapkan';
        bar.style.cssText = 'background:#e9ecef;color:#555;padding:.35rem .8rem;border-radius:20px;font-size:.82rem';
    }
}

/* ─────────────────────────────────────────────────
   Tetapkan Guru BK (admin)
   ───────────────────────────────────────────────── */
function tetapkanGuruBK() {
    const sel       = document.getElementById('selectGuruBK');
    const guruId    = sel.value;
    const guruText  = sel.options[sel.selectedIndex].text;
    const tanggal   = document.getElementById('inputDate').value;
    const msg       = document.getElementById('penetapanMsg');

    const konfirm = guruId === '0'
        ? confirm('Kosongkan tanggal ' + tanggal + ' (tidak ada guru bertugas)?')
        : confirm('Tetapkan ' + guruText + ' bertugas pada ' + tanggal + '?');
    if (!konfirm) return;

    const fd = new FormData();
    fd.append('mode', 'set_guru_bk');
    fd.append('tanggal', tanggal);
    fd.append('id_guru_bk', guruId);

    fetch('../../backend/pages/save_kegiatan_harian.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            msg.style.display = 'block';
            if (data.success) {
                msg.innerHTML  = '<i class="fas fa-check-circle" style="margin-right:5px"></i>' + data.message;
                msg.style.cssText = 'display:block;background:#d4edda;color:#155724;margin-top:.6rem;font-size:.85rem;padding:.5rem .75rem;border-radius:5px';
                loadKegiatan(); // refresh tabel
            } else {
                msg.innerHTML  = '<i class="fas fa-exclamation-circle" style="margin-right:5px"></i>' + data.message;
                msg.style.cssText = 'display:block;background:#f8d7da;color:#721c24;margin-top:.6rem;font-size:.85rem;padding:.5rem .75rem;border-radius:5px';
            }
            setTimeout(() => msg.style.display = 'none', 4000);
        })
        .catch(() => { msg.innerHTML = 'Gagal terhubung ke server'; msg.style.display = 'block'; });
}

/* ─────────────────────────────────────────────────
   Load Kegiatan
   ───────────────────────────────────────────────── */
function loadKegiatan(assignedInfo) {
    const tanggal = document.getElementById('inputDate').value;
    if (!tanggal) return;

    const tbody = document.getElementById('tabelKegiatanBody');
    tbody.innerHTML = '<tr><td colspan="9" style="padding:1.5rem;text-align:center;color:#999"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';

    let paramGuru = '';
    if (userRole === 'admin') {
        const sel = document.getElementById('selectGuruBK');
        const guruId = sel ? sel.value : '0';
        if (!guruId || guruId === '0') {
            setEditMode(false);
            tbody.innerHTML = '<tr><td colspan="9" style="padding:1.5rem;text-align:center;color:#999">Pilih atau tetapkan guru BK terlebih dahulu</td></tr>';
            return;
        }
        paramGuru = guruId;
    } else {
        if (!myIdGuruBk) {
            setEditMode(false);
            tbody.innerHTML = '<tr><td colspan="9" style="padding:1.5rem;text-align:center;color:#f39c12"><i class="fas fa-exclamation-triangle" style="margin-right:5px"></i>Akun belum terhubung ke profil guru BK</td></tr>';
            return;
        }
        paramGuru = myIdGuruBk;
    }

    fetch('../../backend/pages/get_kegiatan_harian.php?tanggal=' + tanggal + '&id_guru_bk=' + paramGuru)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="9" style="padding:1.5rem;text-align:center;color:#dc3545">' + data.message + '</td></tr>';
                setEditMode(false);
                return;
            }

            // Tentukan apakah user boleh edit
            let bolehEdit = false;
            if (userRole === 'admin') {
                bolehEdit = true;
            } else {
                // Guru BK: boleh edit hanya jika dia yang ditetapkan
                bolehEdit = data.is_assigned === true;
            }

            setEditMode(bolehEdit);

            // Tampilkan aksesBar untuk guru_bk
            updateAksesBar(bolehEdit);

            renderKegiatan(data.kegiatan, tanggal, bolehEdit);
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="9" style="padding:1.5rem;text-align:center;color:#dc3545">Gagal memuat data</td></tr>';
            setEditMode(false);
        });
}

function updateAksesBar(bolehEdit) {
    const bar = document.getElementById('aksesBar');
    if (!bar) return;
    if (userRole === 'admin') { bar.style.display = 'none'; return; }
    if (bolehEdit) {
        bar.innerHTML = '<i class="fas fa-unlock" style="margin-right:5px;color:#28a745"></i>Anda dapat menambah dan menyimpan kegiatan untuk tanggal ini';
        bar.style.cssText = 'display:block;background:#d4edda;color:#155724;padding:.6rem 1.25rem;font-size:.84rem;border-bottom:1px solid #c3e6cb';
    } else {
        bar.innerHTML = '<i class="fas fa-lock" style="margin-right:5px;color:#dc3545"></i>Anda tidak ditetapkan bertugas untuk tanggal ini. Kegiatan hanya dapat dilihat.';
        bar.style.cssText = 'display:block;background:#fff3cd;color:#856404;padding:.6rem 1.25rem;font-size:.84rem;border-bottom:1px solid #ffeeba';
    }
}

function setEditMode(canEditNow) {
    canEdit = canEditNow;
    const btnTambah = document.getElementById('btnTambah');
    const btnSimpan = document.getElementById('btnSimpan');
    const btnReset  = document.getElementById('btnReset');
    const footer    = document.getElementById('footerButtons');
    const toolbar   = document.getElementById('toolbarButtons');

    if (toolbar) toolbar.style.display = 'flex';
    if (btnTambah) btnTambah.style.display = canEditNow ? 'inline-flex' : 'none';
    if (footer)   footer.style.display    = canEditNow ? 'flex' : 'none';
    if (btnSimpan) btnSimpan.style.display = canEditNow ? 'inline-flex' : 'none';
    if (btnReset)  btnReset.style.display  = canEditNow ? 'inline-flex' : 'none';
}

/* ─────────────────────────────────────────────────
   Render Tabel
   ───────────────────────────────────────────────── */
function renderKegiatan(kegiatan, tanggal, bolehEdit) {
    const tbody = document.getElementById('tabelKegiatanBody');
    tbody.innerHTML = '';

    if (!kegiatan.length) {
        const msg = bolehEdit ? 'Belum ada kegiatan. Klik Tambah untuk mulai.' : 'Belum ada kegiatan untuk tanggal ini.';
        tbody.innerHTML = '<tr><td colspan="9" style="padding:1.5rem;text-align:center;color:#999"><i class="fas fa-inbox" style="margin-right:5px"></i>' + msg + '</td></tr>';
        return;
    }

    kegiatan.forEach((k, i) => {
        const tgl    = new Date(k.tanggal + 'T00:00:00');
        const hari   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][tgl.getDay()];
        const tglStr = tgl.toLocaleDateString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric'});
        const hariTgl = hari + ',<br>' + tglStr;

        const tr = document.createElement('tr');
        tr.dataset.id = k.id_kegiatan || 'new';
        tr.style.borderBottom = '1px solid #eee';

        if (bolehEdit) {
            tr.innerHTML = `
                <td style="padding:.6rem;border:1px solid #eee;text-align:center">${i+1}</td>
                <td style="padding:.6rem;border:1px solid #eee;text-align:center;font-size:.78rem">${hariTgl}</td>
                <td style="padding:.5rem;border:1px solid #eee"><input type="time" class="row-input" value="${k.waktu_mulai||''}"></td>
                <td style="padding:.5rem;border:1px solid #eee"><input type="time" class="row-input" value="${k.waktu_selesai||''}"></td>
                <td style="padding:.5rem;border:1px solid #eee"><textarea class="row-input">${esc(k.uraian_kegiatan||'')}</textarea></td>
                <td style="padding:.5rem;border:1px solid #eee"><input type="text" class="row-input" value="${esc(k.jenis_layanan||'')}"></td>
                <td style="padding:.5rem;border:1px solid #eee"><input type="text" class="row-input" value="${esc(k.sasaran_layanan||'')}"></td>
                <td style="padding:.5rem;border:1px solid #eee"><input type="text" class="row-input" value="${esc(k.bidang_kode_layanan||'')}"></td>
                <td style="padding:.5rem;border:1px solid #eee;text-align:center">
                    <button onclick="hapusRow(this)" title="Hapus"
                        style="background:#dc3545;color:white;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:.8rem">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>`;
        } else {
            tr.innerHTML = `
                <td style="padding:.6rem;border:1px solid #eee;text-align:center">${i+1}</td>
                <td style="padding:.6rem;border:1px solid #eee;text-align:center;font-size:.78rem">${hariTgl}</td>
                <td style="padding:.6rem;border:1px solid #eee;text-align:center">${k.waktu_mulai||'-'}</td>
                <td style="padding:.6rem;border:1px solid #eee;text-align:center">${k.waktu_selesai||'-'}</td>
                <td style="padding:.6rem;border:1px solid #eee">${esc(k.uraian_kegiatan||'-')}</td>
                <td style="padding:.6rem;border:1px solid #eee">${esc(k.jenis_layanan||'-')}</td>
                <td style="padding:.6rem;border:1px solid #eee">${esc(k.sasaran_layanan||'-')}</td>
                <td style="padding:.6rem;border:1px solid #eee">${esc(k.bidang_kode_layanan||'-')}</td>
                <td style="padding:.6rem;border:1px solid #eee;text-align:center;color:#ccc">-</td>`;
        }
        tbody.appendChild(tr);
    });
}

/* ─────────────────────────────────────────────────
   Tambah Baris
   ───────────────────────────────────────────────── */
function tambahKegiatan() {
    if (!canEdit) { alert('Anda tidak memiliki akses untuk menambah kegiatan.'); return; }

    const tbody  = document.getElementById('tabelKegiatanBody');
    const tanggal = document.getElementById('inputDate').value;
    const tgl    = new Date(tanggal + 'T00:00:00');
    const hari   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][tgl.getDay()];
    const tglStr = tgl.toLocaleDateString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric'});

    // Hapus pesan "belum ada kegiatan"
    if (tbody.querySelector('td[colspan]')) tbody.innerHTML = '';

    const rowNum = tbody.children.length + 1;
    const tr = document.createElement('tr');
    tr.dataset.id = 'new';
    tr.style.borderBottom = '1px solid #eee';
    tr.innerHTML = `
        <td style="padding:.6rem;border:1px solid #eee;text-align:center">${rowNum}</td>
        <td style="padding:.6rem;border:1px solid #eee;text-align:center;font-size:.78rem">${hari},<br>${tglStr}</td>
        <td style="padding:.5rem;border:1px solid #eee"><input type="time" class="row-input"></td>
        <td style="padding:.5rem;border:1px solid #eee"><input type="time" class="row-input"></td>
        <td style="padding:.5rem;border:1px solid #eee"><textarea class="row-input"></textarea></td>
        <td style="padding:.5rem;border:1px solid #eee"><input type="text" class="row-input"></td>
        <td style="padding:.5rem;border:1px solid #eee"><input type="text" class="row-input"></td>
        <td style="padding:.5rem;border:1px solid #eee"><input type="text" class="row-input"></td>
        <td style="padding:.5rem;border:1px solid #eee;text-align:center">
            <button onclick="hapusRow(this)" title="Hapus"
                style="background:#dc3545;color:white;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:.8rem">
                <i class="fas fa-trash"></i>
            </button>
        </td>`;
    tbody.appendChild(tr);
}

function hapusRow(btn) {
    btn.closest('tr').remove();
    document.querySelectorAll('#tabelKegiatanBody tr').forEach((r, i) => {
        const first = r.querySelector('td');
        if (first && !r.querySelector('td[colspan]')) first.textContent = i + 1;
    });
}

/* ─────────────────────────────────────────────────
   Simpan
   ───────────────────────────────────────────────── */
function simpanKegiatan() {
    if (!canEdit) { alert('Anda tidak memiliki akses menyimpan kegiatan.'); return; }

    const tanggal = document.getElementById('inputDate').value;
    const tbody   = document.getElementById('tabelKegiatanBody');

    let guruBKId = '';
    if (userRole === 'admin') {
        const sel = document.getElementById('selectGuruBK');
        guruBKId  = sel ? sel.value : '0';
        if (!guruBKId || guruBKId === '0') { alert('Pilih Guru BK terlebih dahulu'); return; }
    } else {
        guruBKId = myIdGuruBk;
    }

    const kegiatan = [];
    tbody.querySelectorAll('tr:not([colspan])').forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const inputs = row.querySelectorAll('input, textarea');
        if (inputs.length < 6) return;
        const data = {
            id_kegiatan:        row.dataset.id,
            tanggal:            tanggal,
            waktu_mulai:        inputs[0].value,
            waktu_selesai:      inputs[1].value,
            uraian_kegiatan:    inputs[2].value,
            jenis_layanan:      inputs[3].value,
            sasaran_layanan:    inputs[4].value,
            bidang_kode_layanan:inputs[5].value,
        };
        if (data.waktu_mulai || data.uraian_kegiatan) kegiatan.push(data);
    });

    if (!kegiatan.length) { alert('Tambahkan minimal satu kegiatan terlebih dahulu'); return; }

    const btnSimpan = document.getElementById('btnSimpan');
    const origText  = btnSimpan.innerHTML;
    btnSimpan.disabled = true;
    btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px"></i>Menyimpan...';

    const fd = new FormData();
    fd.append('mode', 'save_kegiatan');
    fd.append('tanggal', tanggal);
    fd.append('id_guru_bk', guruBKId);
    fd.append('kegiatan_json', JSON.stringify(kegiatan));

    fetch('../../backend/pages/save_kegiatan_harian.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = origText;
            if (data.success) {
                showToast('success', data.message);
                loadKegiatan();
            } else {
                showToast('error', data.message);
            }
        })
        .catch(() => {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = origText;
            showToast('error', 'Gagal terhubung ke server');
        });
}

function resetForm() {
    if (!canEdit) return;
    if (confirm('Reset? Perubahan yang belum disimpan akan hilang.')) loadAssignedGuruBK();
}

/* ─────────────────────────────────────────────────
   Helpers
   ───────────────────────────────────────────────── */
function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(type, msg) {
    const t = document.createElement('div');
    t.innerHTML = (type === 'success' ? '<i class="fas fa-check-circle" style="margin-right:6px"></i>' : '<i class="fas fa-exclamation-circle" style="margin-right:6px"></i>') + msg;
    Object.assign(t.style, {
        position:'fixed', bottom:'1.5rem', right:'1.5rem', zIndex:9999,
        padding:'.85rem 1.25rem', borderRadius:'8px', fontSize:'.88rem', fontWeight:'600',
        background: type === 'success' ? '#28a745' : '#dc3545', color:'white',
        boxShadow:'0 4px 16px rgba(0,0,0,.2)', transition:'opacity .4s'
    });
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 3500);
}
</script>

<?php include '../layouts/footer.php'; ?>
