<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

include '../../backend/config/database.php';
include '../../backend/config/auth_helper.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

// Filter kelas sesuai role
if (isAdmin()) {
    $kelas_query = "SELECT DISTINCT nama_kelas as kelas FROM kelas 
                    WHERE nama_kelas IS NOT NULL AND nama_kelas != '' 
                    ORDER BY nama_kelas";
} else {
    $id_gbk = getSessionGuruBkId();
    $kelas_query = "SELECT DISTINCT nama_kelas as kelas FROM kelas 
                    WHERE id_guru_bk = $id_gbk 
                    AND nama_kelas IS NOT NULL AND nama_kelas != '' 
                    ORDER BY nama_kelas";
}
$kelas_result = mysqli_query($conn, $kelas_query);
$kelas_list = [];
while ($row = mysqli_fetch_assoc($kelas_result)) {
    $kelas_list[] = $row['kelas'];
}

$school_result = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$school = mysqli_fetch_assoc($school_result);
$school_name = $school['nama_sekolah'] ?? '';
?>

<div style="max-width:1600px; margin:0 auto; padding:20px; font-family:'Segoe UI',sans-serif;">

  <!-- Page Header -->
  <div style="background:var(--brand,#4472C4);color:white;padding:1.25rem 1.5rem;border-radius:8px;margin-bottom:1.25rem">
      <h2 style="margin:0 0 4px;font-size:1.1rem;font-weight:700">
          <i class="fa-solid fa-percent" style="margin-right:8px"></i>PENILAIAN SISWA
      </h2>
      <p style="margin:0;font-size:.85rem;opacity:.9">Masukkan nilai tugas siswa</p>
      <p style="margin:0;font-size:.82rem;opacity:.8"><?= htmlspecialchars($school_name) ?></p>
  </div>

  <?php if (!isAdmin()): ?>
  <div style="background:#f0f4ff;border-left:3px solid #4472C4;border-radius:5px;padding:0.6rem 1rem;margin-bottom:1rem;font-size:0.82rem;color:#1a56db">
      <i class="fas fa-lock" style="margin-right:5px"></i>
      Anda hanya dapat melihat dan menilai kelas yang ditetapkan untuk Anda.
  </div>
  <?php endif; ?>

  <!-- Tab Navigation -->
  <div style="display:flex; gap:0; margin-bottom:24px; background:#f1f5f9; border-radius:10px; padding:5px;">
    <button onclick="switchTab('generate')" id="tab-generate"
      style="flex:1; border:none; padding:11px 20px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13.5px; display:flex; align-items:center; justify-content:center; gap:8px; transition:all 0.2s; background:#4472C4; color:white; box-shadow:0 2px 8px rgba(68,114,196,0.3);">
      <i class="fas fa-plus-circle"></i> Generate Baru
    </button>
    <button onclick="switchTab('tersimpan')" id="tab-tersimpan"
      style="flex:1; border:none; padding:11px 20px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13.5px; display:flex; align-items:center; justify-content:center; gap:8px; transition:all 0.2s; background:transparent; color:#64748b;">
      <i class="fas fa-save"></i> Penilaian Tersimpan
    </button>
  </div>

  <!-- ============ GENERATE TAB ============ -->
  <div id="generate-tab">

    <!-- Filter Bar -->
    <div style="background:white; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,0.08); border:1px solid #e8edf2;">
      <div style="display:grid; grid-template-columns:1fr 1fr 140px; gap:15px; align-items:flex-end;">
        <div>
          <label style="display:block; margin-bottom:7px; font-weight:600; color:#374151; font-size:13px;">
            <i class="fas fa-chalkboard" style="color:#4472C4; margin-right:5px;"></i> Pilih Kelas
          </label>
          <select id="kelasSelect" style="width:100%; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:13px; color:#374151; background:white;">
            <option value="">-- Pilih Kelas --</option>
            <?php foreach ($kelas_list as $kls): ?>
              <option value="<?= htmlspecialchars($kls) ?>"><?= htmlspecialchars($kls) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; margin-bottom:7px; font-weight:600; color:#374151; font-size:13px;">
            <i class="fas fa-tasks" style="color:#4472C4; margin-right:5px;"></i> Jumlah Tugas <span style="color:#ef4444;">*</span>
          </label>
          <input type="number" id="jumlahTugas" min="1" max="50" value="5"
            style="width:100%; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:7px; font-size:13px; color:#374151; box-sizing:border-box;">
        </div>
        <button onclick="generateTable()"
          style="background:#4472C4; color:white; border:none; padding:10px 20px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; box-shadow:0 2px 8px rgba(68,114,196,0.25);">
          <i class="fas fa-search"></i> Generate
        </button>
      </div>
    </div>

    <!-- Table Container -->
    <div id="tableContainer" style="display:none; background:white; padding:20px; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.08); border:1px solid #e8edf2; overflow-x:auto;">
      <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
        <h3 style="margin:0; font-size:15px; color:#1e293b;">
          <i class="fas fa-table" style="color:#4472C4; margin-right:6px;"></i>
          Penilaian <span id="kelasLabel" style="color:#4472C4;"></span>
          &mdash; <span id="siswaCount" style="color:#4472C4;">0</span> siswa
        </h3>
        <div style="background:#f8fafc; padding:6px 12px; border-radius:6px; font-size:12px; color:#64748b; border:1px solid #e2e8f0;">
          <i class="fas fa-clipboard-list" style="margin-right:5px;"></i>
          Jumlah Tugas: <strong id="infoJumlahTugas" style="color:#374151;">5</strong>
        </div>
      </div>

      <div id="exportButtons" style="display:flex; gap:8px; margin-bottom:14px;"></div>

      <hr style="margin:14px 0; border:none; border-top:1px solid #e8edf2;">

      <table id="tabelPenilaian" style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead id="tabelHead"></thead>
        <tbody id="tabelBody"></tbody>
      </table>

      <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap;">
        <button onclick="simpanSemualNilai()"
          style="background:#22c55e; color:white; border:none; padding:11px 26px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:7px; box-shadow:0 2px 8px rgba(34,197,94,0.25);">
          <i class="fas fa-save"></i> Simpan Semua Nilai
        </button>
        <button onclick="resetTable()"
          style="background:#94a3b8; color:white; border:none; padding:11px 26px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:7px;">
          <i class="fas fa-undo"></i> Reset
        </button>
      </div>

      <div id="statusMessage" style="display:none; margin-top:14px; padding:12px 14px; border-radius:7px; border-left:4px solid; font-size:13px;"></div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" style="display:none; background:#f8fafc; padding:48px 20px; border-radius:10px; text-align:center; border:1.5px dashed #cbd5e1;">
      <i class="fas fa-inbox" style="font-size:2.5rem; color:#cbd5e1; display:block; margin-bottom:12px;"></i>
      <p id="emptyMessage" style="color:#94a3b8; font-size:14px; margin:0;">Pilih kelas untuk menampilkan daftar siswa</p>
    </div>

    <!-- Error State -->
    <div id="errorState" style="display:none; background:#fef2f2; padding:14px 16px; border-radius:8px; border-left:4px solid #ef4444;">
      <p id="errorMessage" style="color:#dc2626; margin:0; font-size:13px;"></p>
    </div>

  </div><!-- END GENERATE TAB -->

  <!-- ============ TERSIMPAN TAB ============ -->
  <div id="tersimpan-tab" style="display:none;">

    <!-- List View -->
    <div id="tersimpan-list-view">
      <div style="background:white; padding:20px; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.08); border:1px solid #e8edf2;">
        <h3 style="margin:0 0 16px 0; font-size:15px; color:#1e293b;">
          <i class="fas fa-list" style="color:#4472C4; margin-right:6px;"></i> Daftar Penilaian Tersimpan
        </h3>
        <div id="tersimpanList" style="min-height:200px;">
          <p style="color:#94a3b8; text-align:center; padding:40px 0; font-size:14px;">
            <i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Memuat data...
          </p>
        </div>
      </div>
    </div>

    <!-- Detail View -->
    <div id="tersimpan-detail-view" style="display:none;">
      <button onclick="backToTersimpanList()"
        style="background:#94a3b8; color:white; border:none; padding:8px 16px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px; margin-bottom:16px;">
        <i class="fas fa-arrow-left"></i> Kembali
      </button>

      <div style="background:white; padding:20px; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.08); border:1px solid #e8edf2; overflow-x:auto;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
          <h3 style="margin:0; font-size:15px; color:#1e293b;">
            <i class="fas fa-table" style="color:#4472C4; margin-right:6px;"></i>
            Penilaian <span id="detailKelasLabel" style="color:#4472C4;"></span>
            &mdash; <span id="detailSiswaCount" style="color:#4472C4;">0</span> siswa
          </h3>
          <div style="background:#f8fafc; padding:6px 12px; border-radius:6px; font-size:12px; color:#64748b; border:1px solid #e2e8f0;">
            <i class="fas fa-clipboard-list" style="margin-right:5px;"></i>
            Jumlah Tugas: <strong id="detailJumlahTugas" style="color:#374151;">5</strong>
          </div>
        </div>

        <div id="detailExportButtons" style="display:flex; gap:8px; margin-bottom:14px;"></div>

        <hr style="margin:14px 0; border:none; border-top:1px solid #e8edf2;">

        <table id="detailTablePenilaian" style="width:100%; border-collapse:collapse; font-size:13px;">
          <thead id="detailTabelHead"></thead>
          <tbody id="detailTabelBody"></tbody>
        </table>

        <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap;">
          <button onclick="simpanPenilaianTersimpan()"
            style="background:#22c55e; color:white; border:none; padding:11px 26px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:7px; box-shadow:0 2px 8px rgba(34,197,94,0.25);">
            <i class="fas fa-save"></i> Simpan Perubahan
          </button>
          <button onclick="backToTersimpanList()"
            style="background:#94a3b8; color:white; border:none; padding:11px 26px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:7px;">
            <i class="fas fa-times"></i> Batal
          </button>
        </div>

        <div id="detailStatusMessage" style="display:none; margin-top:14px; padding:12px 14px; border-radius:7px; border-left:4px solid; font-size:13px;"></div>
      </div>
    </div>

  </div><!-- END TERSIMPAN TAB -->

</div>

<!-- Delete Confirm Modal -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:white; border-radius:12px; padding:28px 28px 22px; max-width:380px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
      <div style="background:#fef2f2; border-radius:50%; width:44px; height:44px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fas fa-trash-alt" style="color:#ef4444; font-size:1.1rem;"></i>
      </div>
      <div>
        <h4 style="margin:0 0 3px 0; color:#1e293b; font-size:15px;">Hapus Penilaian?</h4>
        <p style="margin:0; font-size:12px; color:#94a3b8;">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
    </div>
    <p style="color:#475569; font-size:13px; margin:0 0 20px 0;">
      Seluruh data penilaian kelas <strong id="deleteKelasLabel" style="color:#4472C4;"></strong> akan dihapus permanen.
    </p>
    <div style="display:flex; gap:10px; justify-content:flex-end;">
      <button onclick="closeDeleteModal()"
        style="background:#f1f5f9; color:#475569; border:none; padding:9px 20px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px;">
        Batal
      </button>
      <button onclick="confirmDelete()"
        style="background:#ef4444; color:white; border:none; padding:9px 20px; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:6px;">
        <i class="fas fa-trash-alt"></i> Hapus
      </button>
    </div>
  </div>
</div>

<style>
  input[type="text"], input[type="number"], select { font-family:inherit; transition:border-color 0.2s,box-shadow 0.2s; }
  input[type="text"]:focus, input[type="number"]:focus, select:focus { outline:none; border-color:#4472C4 !important; box-shadow:0 0 0 3px rgba(68,114,196,0.12); }
  th { font-family:inherit; }
  input[type="checkbox"].task-checkbox { width:17px; height:17px; cursor:pointer; margin:0; accent-color:#4472C4; }
  tr:hover td { background-color:#f8fafc; }
</style>

<script>
  const USER_ROLE      = '<?= isAdmin() ? 'admin' : 'guru_bk' ?>';
  const ALLOWED_KELAS  = <?= json_encode($kelas_list) ?>;

  let siswaData    = [];
  let jumlahTugas  = 5;
  let currentTab   = 'generate';
  let deleteTarget = null;

  /* ── TAB SWITCH ── */
  function switchTab(tab) {
    currentTab = tab;
    ['generate','tersimpan'].forEach(t => {
      const isActive = t === tab;
      document.getElementById(t + '-tab').style.display   = isActive ? 'block' : 'none';
      document.getElementById('tab-' + t).style.background  = isActive ? '#4472C4' : 'transparent';
      document.getElementById('tab-' + t).style.color       = isActive ? 'white'   : '#64748b';
      document.getElementById('tab-' + t).style.boxShadow   = isActive ? '0 2px 8px rgba(68,114,196,0.3)' : 'none';
    });
    if (tab === 'tersimpan') loadTersimpan();
  }

  /* ── LOAD SAVED LIST ── */
  function loadTersimpan() {
    document.getElementById('tersimpanList').innerHTML =
      '<p style="color:#94a3b8;text-align:center;padding:40px 0;font-size:14px;"><i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Memuat data...</p>';

    fetch('../../backend/pages/get_penilaian_tersimpan.php')
      .then(r => r.json())
      .then(data => {
        if (data.success && data.penilaian.length > 0) {
          let html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">';
          data.penilaian.forEach(item => {
            const kls = item.kelas.replace(/'/g,"\\'");
            html += `
              <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,0.06);transition:all 0.2s;"
                   onmouseover="this.style.borderColor='#4472C4';this.style.boxShadow='0 4px 14px rgba(68,114,196,0.15)'"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='0 1px 4px rgba(0,0,0,0.06)'">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;">
                  <div style="display:flex;align-items:center;gap:9px;">
                    <div style="background:#eff6ff;border-radius:8px;width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                      <i class="fas fa-book-open" style="color:#4472C4;font-size:1rem;"></i>
                    </div>
                    <div>
                      <div style="font-size:15px;font-weight:700;color:#1e293b;">${item.kelas}</div>
                      <div style="font-size:11px;color:#94a3b8;margin-top:2px;">
                        <i class="fas fa-clock" style="margin-right:3px;"></i>
                        ${new Date(item.updated_at).toLocaleDateString('id-ID',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}
                      </div>
                    </div>
                  </div>
                  <button onclick="openDeleteModal('${kls}')"
                    style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:12px;flex-shrink:0;display:flex;align-items:center;gap:5px;" title="Hapus penilaian">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
                  <div style="background:white;border-radius:6px;padding:9px;border:1px solid #e2e8f0;text-align:center;">
                    <div style="font-size:11px;color:#94a3b8;margin-bottom:3px;">Tugas</div>
                    <div style="font-size:17px;font-weight:700;color:#4472C4;">${item.jumlah_tugas}</div>
                  </div>
                  <div style="background:white;border-radius:6px;padding:9px;border:1px solid #e2e8f0;text-align:center;">
                    <div style="font-size:11px;color:#94a3b8;margin-bottom:3px;">Siswa</div>
                    <div style="font-size:17px;font-weight:700;color:#22c55e;">${item.jumlah_siswa}</div>
                  </div>
                </div>
                <button onclick="viewPenilaianDetail('${kls}')"
                  style="width:100%;background:#4472C4;color:white;border:none;padding:9px;border-radius:7px;cursor:pointer;font-weight:600;font-size:13px;display:flex;align-items:center;justify-content:center;gap:7px;">
                  <i class="fas fa-eye"></i> Lihat Detail &amp; Edit
                </button>
              </div>`;
          });
          html += '</div>';
          document.getElementById('tersimpanList').innerHTML = html;
        } else {
          document.getElementById('tersimpanList').innerHTML =
            '<div style="text-align:center;padding:60px 20px;"><i class="fas fa-folder-open" style="font-size:3rem;color:#cbd5e1;display:block;margin-bottom:14px;"></i><p style="color:#94a3b8;font-size:14px;margin:0;">Belum ada penilaian yang tersimpan.</p></div>';
        }
      })
      .catch(() => {
        document.getElementById('tersimpanList').innerHTML =
          '<p style="color:#dc2626;text-align:center;"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>Gagal memuat data penilaian.</p>';
      });
  }

  /* ── DELETE MODAL ── */
  function openDeleteModal(kelas) {
    deleteTarget = kelas;
    document.getElementById('deleteKelasLabel').textContent = kelas;
    document.getElementById('deleteModal').style.display = 'flex';
  }
  function closeDeleteModal() {
    deleteTarget = null;
    document.getElementById('deleteModal').style.display = 'none';
  }
  function confirmDelete() {
    if (!deleteTarget) return;
    const fd = new FormData();
    fd.append('kelas', deleteTarget);
    fetch('../../backend/pages/delete_penilaian.php', { method:'POST', body:fd })
      .then(r => r.json())
      .then(data => { closeDeleteModal(); if (data.success) loadTersimpan(); else alert('Gagal: ' + (data.message||'Error')); })
      .catch(() => { closeDeleteModal(); alert('Terjadi kesalahan.'); });
  }
  document.getElementById('deleteModal').addEventListener('click', function(e) { if (e.target === this) closeDeleteModal(); });

  /* ── VIEW DETAIL ── */
  function viewPenilaianDetail(kelas) {
    document.getElementById('tersimpan-list-view').style.display = 'none';
    document.getElementById('tersimpan-detail-view').style.display = 'block';
    document.getElementById('detailTabelBody').innerHTML =
      '<tr><td colspan="100%" style="text-align:center;padding:30px;color:#94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i>Memuat...</td></tr>';

    fetch('../../backend/pages/get_penilaian_detail.php?kelas=' + encodeURIComponent(kelas))
      .then(r => { if(!r.ok) throw new Error('Network error'); return r.json(); })
      .then(data => {
        if (data.success) renderDetailTable(data.siswa, data.kelas, data.jumlah_tugas);
        else showDetailStatusMessage('error', data.message || 'Gagal memuat data');
      })
      .catch(() => showDetailStatusMessage('error', 'Terjadi kesalahan saat memuat data'));
  }

  function renderDetailTable(siswa, kelas, jTugas) {
    document.getElementById('detailKelasLabel').textContent = '(' + kelas + ')';
    document.getElementById('detailSiswaCount').textContent = siswa.length;
    document.getElementById('detailJumlahTugas').textContent = jTugas;
    const ke = encodeURIComponent(kelas);
    document.getElementById('detailExportButtons').innerHTML = `
      <a href="../../backend/pages/export_penilaian_excel.php?mode=saved&kelas=${ke}"
         style="background:#22c55e;color:white;text-decoration:none;padding:8px 14px;border-radius:7px;font-weight:600;font-size:12px;display:inline-flex;align-items:center;gap:6px;" target="_blank">
        <i class="fas fa-file-excel"></i> Export Excel</a>
      <a href="../../backend/pages/export_penilaian_pdf.php?mode=saved&kelas=${ke}"
         style="background:#ef4444;color:white;text-decoration:none;padding:8px 14px;border-radius:7px;font-weight:600;font-size:12px;display:inline-flex;align-items:center;gap:6px;" target="_blank">
        <i class="fas fa-file-pdf"></i> Export PDF</a>`;
    document.getElementById('detailTabelHead').innerHTML = buildHeaderHtml(jTugas);
    document.getElementById('detailTabelBody').innerHTML = buildBodyHtml(siswa, jTugas, true);
  }

  function backToTersimpanList() {
    document.getElementById('tersimpan-list-view').style.display = 'block';
    document.getElementById('tersimpan-detail-view').style.display = 'none';
  }

  /* ── GENERATE TABLE ── */
  function generateTable() {
    const kelas  = document.getElementById('kelasSelect').value;
    const jumlah = parseInt(document.getElementById('jumlahTugas').value) || 5;

    if (!kelas) { showEmpty('Pilih kelas terlebih dahulu'); return; }

    // Validasi frontend: guru BK tidak boleh akses kelas diluar ALLOWED_KELAS
    if (USER_ROLE !== 'admin' && !ALLOWED_KELAS.includes(kelas)) {
      showError('Akses ditolak: kelas ini bukan kelas Anda');
      return;
    }

    if (jumlah < 1 || jumlah > 50) { showError('Jumlah tugas harus antara 1-50'); return; }

    jumlahTugas = jumlah;
    document.getElementById('tableContainer').style.display = 'none';
    document.getElementById('emptyState').style.display    = 'none';
    document.getElementById('errorState').style.display    = 'none';

    fetch('../../backend/pages/get_siswa_for_penilaian.php?kelas=' + encodeURIComponent(kelas))
      .then(r => { if(!r.ok) throw new Error('Network error'); return r.json(); })
      .then(data => {
        if (data.success) {
          if (data.count === 0) {
            showEmpty('Belum ada siswa di kelas ' + kelas + '. Tambahkan siswa terlebih dahulu di menu Data Siswa.');
          } else {
            siswaData = data.siswa;
            renderTable(data.siswa, kelas, jumlahTugas);
            document.getElementById('tableContainer').style.display = 'block';
          }
        } else {
          showError(data.message || 'Gagal memuat data siswa');
        }
      })
      .catch(() => showError('Terjadi kesalahan saat memuat data siswa'));
  }

  function renderTable(siswa, kelas, jTugas) {
    document.getElementById('kelasLabel').textContent = '(' + kelas + ')';
    document.getElementById('siswaCount').textContent = siswa.length;
    document.getElementById('infoJumlahTugas').textContent = jTugas;
    const ke = encodeURIComponent(kelas);
    document.getElementById('exportButtons').innerHTML = `
      <a href="../../backend/pages/export_penilaian_excel.php?kelas=${ke}&jumlah_tugas=${jTugas}"
         style="background:#22c55e;color:white;text-decoration:none;padding:8px 14px;border-radius:7px;font-weight:600;font-size:12px;display:inline-flex;align-items:center;gap:6px;" target="_blank">
        <i class="fas fa-file-excel"></i> Export Excel</a>
      <a href="../../backend/pages/export_penilaian_pdf.php?kelas=${ke}&jumlah_tugas=${jTugas}"
         style="background:#ef4444;color:white;text-decoration:none;padding:8px 14px;border-radius:7px;font-weight:600;font-size:12px;display:inline-flex;align-items:center;gap:6px;" target="_blank">
        <i class="fas fa-file-pdf"></i> Export PDF</a>`;
    document.getElementById('tabelHead').innerHTML = buildHeaderHtml(jTugas);
    document.getElementById('tabelBody').innerHTML = buildBodyHtml(siswa, jTugas, false);
  }

  /* ── SHARED TABLE BUILDERS ── */
  function buildHeaderHtml(jTugas) {
    let h = `<tr>
        <th rowspan="2" style="background:#FFC000;color:#1e293b;font-weight:700;padding:12px 14px;border:1px solid #e2e8f0;text-align:center;vertical-align:middle;width:50px;">No</th>
        <th rowspan="2" style="background:#FFC000;color:#1e293b;font-weight:700;padding:12px 14px;border:1px solid #e2e8f0;text-align:left;vertical-align:middle;min-width:150px;">Nama Siswa</th>
        <th colspan="${jTugas}" style="background:#FFC000;color:#1e293b;font-weight:700;padding:12px 14px;border:1px solid #e2e8f0;text-align:center;">Tugas Ke</th>
      </tr><tr>`;
    for (let i = 1; i <= jTugas; i++) {
      h += `<th style="background:#FFC000;color:#1e293b;font-weight:700;padding:10px;border:1px solid #e2e8f0;text-align:center;width:55px;">${i}</th>`;
    }
    return h + '</tr>';
  }

  function buildBodyHtml(siswa, jTugas, withScores) {
    return siswa.map((row, idx) => {
      let cells = '';
      for (let i = 1; i <= jTugas; i++) {
        const checked = withScores && row.scores && row.scores[i-1] == 1 ? 'checked' : '';
        cells += `<td style="padding:10px;border:1px solid #e2e8f0;text-align:center;width:55px;">
          <input type="checkbox" class="task-checkbox task-${i}" ${checked}>
        </td>`;
      }
      return `<tr data-id="${row.id_siswa}">
        <td style="text-align:center;padding:12px 14px;border:1px solid #e2e8f0;vertical-align:middle;color:#64748b;font-size:12px;">${idx+1}</td>
        <td style="text-align:left;padding:12px 14px;border:1px solid #e2e8f0;vertical-align:middle;">
          <div style="font-weight:600;color:#1e293b;">${htmlEscape(row.nama_siswa)}</div>
        </td>
        ${cells}
      </tr>`;
    }).join('');
  }

  /* ── SAVE ── */
  function simpanSemualNilai() {
    if (siswaData.length === 0) { showStatusMessage('error','Tidak ada data yang disimpan'); return; }
    savePenilaian(document.getElementById('tabelBody'), jumlahTugas, 'statusMessage', event.target);
  }
  function simpanPenilaianTersimpan() {
    const jTugas = parseInt(document.getElementById('detailJumlahTugas').textContent);
    savePenilaian(document.getElementById('detailTabelBody'), jTugas, 'detailStatusMessage', event.target);
  }
  function savePenilaian(tbody, jTugas, statusId, btn) {
    const rows = tbody.querySelectorAll('tr');
    if (!rows.length) { showMsg(statusId,'error','Tidak ada data'); return; }
    btn.disabled = true; btn.style.opacity = '0.55';
    let saved = 0, error = 0, total = rows.length;
    rows.forEach(row => {
      const id_siswa = parseInt(row.dataset.id);
      const scores = [];
      for (let i = 1; i <= jTugas; i++) scores.push(row.querySelector('.task-'+i).checked ? 1 : 0);
      const fd = new FormData();
      fd.append('id_siswa', id_siswa);
      fd.append('jumlah_tugas', jTugas);
      fd.append('scores', JSON.stringify(scores));
      fetch('../../backend/pages/save_penilaian.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => { d.success ? saved++ : error++; })
        .catch(() => error++)
        .finally(() => {
          if (saved + error === total) {
            btn.disabled = false; btn.style.opacity = '1';
            error === 0
              ? showMsg(statusId,'success',`${saved} nilai siswa berhasil disimpan`)
              : showMsg(statusId,'error',`${saved} disimpan, ${error} gagal`);
          }
        });
    });
  }

  /* ── HELPERS ── */
  function showMsg(id, type, message) {
    const el = document.getElementById(id);
    el.style.display = 'block';
    el.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':'exclamation-circle'}" style="margin-right:6px;"></i>${message}`;
    el.style.background      = type==='success' ? '#f0fdf4' : '#fef2f2';
    el.style.color           = type==='success' ? '#166534' : '#dc2626';
    el.style.borderLeftColor = type==='success' ? '#22c55e' : '#ef4444';
    setTimeout(() => el.style.display='none', 3500);
  }
  function showStatusMessage(type,msg) { showMsg('statusMessage',type,msg); }
  function showDetailStatusMessage(type,msg) { showMsg('detailStatusMessage',type,msg); }
  function showEmpty(msg) {
    document.getElementById('tableContainer').style.display='none';
    document.getElementById('errorState').style.display='none';
    document.getElementById('emptyState').style.display='block';
    document.getElementById('emptyMessage').textContent=msg;
  }
  function showError(msg) {
    document.getElementById('tableContainer').style.display='none';
    document.getElementById('emptyState').style.display='none';
    document.getElementById('errorState').style.display='block';
    document.getElementById('errorMessage').innerHTML='<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>'+msg;
  }
  function resetTable() { if (confirm('Reset tabel? Semua input nilai akan dikosongkan.')) generateTable(); }
  function htmlEscape(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }
</script>

<?php include '../layouts/footer.php'; ?>