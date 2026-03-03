<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: ./auth/login.php");
  exit;
}
include "layouts/header.php";
include "layouts/sidebar.php";
?>

<section class="card">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap">
    <div>
      <h1 style="margin:0;font-size:1.75rem">📊 Dashboard</h1>
      <p style="color:var(--text-light);margin:0.5rem 0 1rem 0">Ringkasan cepat sistem Bimbingan & Konseling.</p>

      <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
        <a class="btn" href="./siswa/index.php">👥 Data Siswa</a>
        <?php 
          $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
          if($role === 'admin'):
        ?>
          <a class="btn" href="./sekolah/index.php">🏫 Data Sekolah</a>
        <?php elseif($role === 'guru_bk'): ?>
          <a class="btn" href="./sekolah/kelas.php">📚 Data Kelas</a>
        <?php endif; ?>
        <a class="btn" href="./rekap/index.php">📋 Rekap Layanan</a>
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:0.5rem;align-items:flex-end">
      <a href="./siswa/tambah.php" class="btn">➕ Tambah Siswa</a>
    </div>
  </div>
</section>

<section style="border-top:1px solid #e5e7eb;padding:2.5rem 0">
  <h2 style="text-align:center;margin-bottom:2rem;color:var(--text)">✨ Fitur Utama</h2>
  <div class="features">
    <div class="feature">
      <div>👥</div>
      <h4>Manajemen Siswa</h4>
      <p>Data siswa terpusat, mudah dicari dan dibagikan.</p>
    </div>
    <div class="feature">
      <div>🏫</div>
      <h4>Data Sekolah</h4>
      <p>Informasi sekolah dan tahun ajaran yang dapat diatur cepat.</p>
    </div>
    <div class="feature">
      <div>📋</div>
      <h4>Rekap Layanan</h4>
      <p>Catatan layanan BK untuk analisis dan laporan.</p>
    </div>
    <div class="feature">
      <div>📁</div>
      <h4>File & Dokumen</h4>
      <p>Manajemen dokumen dan ekspor rekap tersedia di menu Rekap.</p>
    </div>
  </div>
</section>

<?php include "layouts/footer.php"; ?>