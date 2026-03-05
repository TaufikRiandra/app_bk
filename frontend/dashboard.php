<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: ./auth/login.php");
  exit;
}
include "layouts/header.php";
include "layouts/sidebar.php";
?>

<!-- Dashboard Wrapper -->
<div style="min-height:100vh; background:#f0f4ff; font-family:'Segoe UI',sans-serif; padding:2rem;">

  <!-- Page Header Card -->
  <div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); border-radius:16px; padding:2rem 2.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem; box-shadow:0 8px 32px rgba(79,70,229,0.25); margin-bottom:2rem;">
    
    <div>
      <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.4rem;">
        <div style="background:rgba(255,255,255,0.2); border-radius:10px; width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
          <i class="fas fa-chart-line" style="color:#fff; font-size:1.1rem;"></i>
        </div>
        <h1 style="margin:0; color:#fff; font-size:1.75rem; font-weight:700; letter-spacing:-0.3px;">Dashboard</h1>
      </div>
      <p style="margin:0 0 1.25rem 0; color:rgba(255,255,255,0.75); font-size:0.95rem;">Ringkasan cepat sistem Bimbingan &amp; Konseling.</p>

      <!-- Action Buttons -->
      <div style="display:flex; flex-wrap:wrap; gap:0.6rem;">
        <a href="./siswa/index.php" style="background:rgba(255,255,255,0.18); color:#fff; text-decoration:none; padding:0.5rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:500; border:1px solid rgba(255,255,255,0.3); display:inline-flex; align-items:center; gap:0.4rem; backdrop-filter:blur(4px);">
          <i class="fas fa-users"></i> Data Siswa
        </a>
        <?php
          $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
          if($role === 'admin'):
        ?>
          <a href="./sekolah/index.php" style="background:rgba(255,255,255,0.18); color:#fff; text-decoration:none; padding:0.5rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:500; border:1px solid rgba(255,255,255,0.3); display:inline-flex; align-items:center; gap:0.4rem; backdrop-filter:blur(4px);">
            <i class="fas fa-school"></i> Data Sekolah
          </a>
        <?php elseif($role === 'guru_bk'): ?>
          <a href="./sekolah/kelas.php" style="background:rgba(255,255,255,0.18); color:#fff; text-decoration:none; padding:0.5rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:500; border:1px solid rgba(255,255,255,0.3); display:inline-flex; align-items:center; gap:0.4rem; backdrop-filter:blur(4px);">
            <i class="fas fa-book"></i> Data Kelas
          </a>
        <?php endif; ?>
        <a href="./rekap/index.php" style="background:rgba(255,255,255,0.18); color:#fff; text-decoration:none; padding:0.5rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:500; border:1px solid rgba(255,255,255,0.3); display:inline-flex; align-items:center; gap:0.4rem; backdrop-filter:blur(4px);">
          <i class="fas fa-file-pdf"></i> Rekap Layanan
        </a>
      </div>
    </div>

    <!-- CTA Button -->
  </div>

  <!-- Section Title -->
  <div style="text-align:center; margin-bottom:1.75rem;">
    <span style="display:inline-flex; align-items:center; gap:0.5rem; background:#ede9fe; color:#6d28d9; padding:0.35rem 1rem; border-radius:999px; font-size:0.8rem; font-weight:600; letter-spacing:0.5px; text-transform:uppercase;">
      <i class="fas fa-sparkles"></i> Fitur Utama
    </span>
    <h2 style="margin:0.75rem 0 0 0; color:#1e1b4b; font-size:1.4rem; font-weight:700;">Apa yang bisa kamu lakukan?</h2>
  </div>

  <!-- Feature Cards Grid -->
  <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.25rem;">

    <!-- Card 1 -->
    <div style="background:#fff; border-radius:14px; padding:1.75rem 1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid #e8e5ff;">
      <div style="width:48px; height:48px; background:linear-gradient(135deg,#4f46e5,#7c3aed); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; box-shadow:0 4px 12px rgba(79,70,229,0.3);">
        <i class="fas fa-users" style="color:#fff; font-size:1.2rem;"></i>
      </div>
      <h4 style="margin:0 0 0.5rem 0; color:#1e1b4b; font-size:1rem; font-weight:700;">Manajemen Siswa</h4>
      <p style="margin:0; color:#64748b; font-size:0.875rem; line-height:1.6;">Data siswa terpusat, mudah dicari dan dibagikan.</p>
    </div>

    <!-- Card 2 -->
    <div style="background:#fff; border-radius:14px; padding:1.75rem 1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid #e8e5ff;">
      <div style="width:48px; height:48px; background:linear-gradient(135deg,#0ea5e9,#0284c7); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; box-shadow:0 4px 12px rgba(14,165,233,0.3);">
        <i class="fas fa-school" style="color:#fff; font-size:1.2rem;"></i>
      </div>
      <h4 style="margin:0 0 0.5rem 0; color:#1e1b4b; font-size:1rem; font-weight:700;">Data Sekolah</h4>
      <p style="margin:0; color:#64748b; font-size:0.875rem; line-height:1.6;">Informasi sekolah dan tahun ajaran yang dapat diatur cepat.</p>
    </div>

    <!-- Card 3 -->
    <div style="background:#fff; border-radius:14px; padding:1.75rem 1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid #e8e5ff;">
      <div style="width:48px; height:48px; background:linear-gradient(135deg,#10b981,#059669); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; box-shadow:0 4px 12px rgba(16,185,129,0.3);">
        <i class="fas fa-file-alt" style="color:#fff; font-size:1.2rem;"></i>
      </div>
      <h4 style="margin:0 0 0.5rem 0; color:#1e1b4b; font-size:1rem; font-weight:700;">Rekap Layanan</h4>
      <p style="margin:0; color:#64748b; font-size:0.875rem; line-height:1.6;">Catatan layanan BK untuk analisis dan laporan.</p>
    </div>

    <!-- Card 4 -->
    <div style="background:#fff; border-radius:14px; padding:1.75rem 1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid #e8e5ff;">
      <div style="width:48px; height:48px; background:linear-gradient(135deg,#f59e0b,#d97706); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; box-shadow:0 4px 12px rgba(245,158,11,0.3);">
        <i class="fas fa-folder" style="color:#fff; font-size:1.2rem;"></i>
      </div>
      <h4 style="margin:0 0 0.5rem 0; color:#1e1b4b; font-size:1rem; font-weight:700;">File &amp; Dokumen</h4>
      <p style="margin:0; color:#64748b; font-size:0.875rem; line-height:1.6;">Manajemen dokumen dan ekspor rekap tersedia di menu Rekap.</p>
    </div>

  </div>
</div>

<?php include "layouts/footer.php"; ?>