<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['login'])) {
    header("Location: /frontend/auth/login.php");
    exit;
}
include "../../../backend/config/database.php";
include "../../layouts/header.php";
include "../../layouts/sidebar.php";

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'kegiatan_harian';
// Jadikan $conn global agar bisa diakses oleh file yang di-include
$GLOBALS['conn'] = $conn;

// Get school data — pakai kolom yang ada di DB (tahun_ajaran)
$sekolah_result = mysqli_query($conn, "SELECT * FROM sekolah LIMIT 1");
$data_sekolah = mysqli_fetch_assoc($sekolah_result);
?>

    <div style="background:var(--brand,#4472C4);color:white;padding:1.25rem 1.5rem;border-radius:8px;margin-bottom:1.25rem">
        <h2 style="margin:0 0 4px;font-size:1.1rem;font-weight:700">
            <i class="fas fa-chart-pie"></i> REKAP LAYANAN BIMBINGAN
        </h2>
        <p style="margin:0;font-size:.85rem;opacity:.9">Lihat rekap semua layanan bimbingan yang telah diberikan</p>
        <p>
			<?= htmlspecialchars($data_sekolah['nama_sekolah'] ?? 'Sekolah') ?>
		</p>
    </div>

    <!-- Tab Navigation -->
    <div style="display:flex;gap:0;margin-bottom:2rem;border-bottom:2px solid var(--border);flex-wrap:wrap">
        <a href="?tab=kegiatan_harian"
           style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;
                  color:<?= $tab === 'kegiatan_harian' ? 'var(--brand)' : 'var(--text-light)' ?>;
                  background-color:<?= $tab === 'kegiatan_harian' ? '#E8E4FF' : 'transparent' ?>;
                  border-bottom:3px solid <?= $tab === 'kegiatan_harian' ? 'var(--brand)' : 'transparent' ?>;
                  cursor:pointer;
                  font-weight:<?= $tab === 'kegiatan_harian' ? '600' : 'normal' ?>;
                  border-radius:4px 4px 0 0">
            <i class="fas fa-calendar-check"></i> Kegiatan Harian
        </a>
        <a href="?tab=layanan_mediasi"
           style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;
                  color:<?= $tab === 'layanan_mediasi' ? 'var(--brand)' : 'var(--text-light)' ?>;
                  background-color:<?= $tab === 'layanan_mediasi' ? '#E8E4FF' : 'transparent' ?>;
                  border-bottom:3px solid <?= $tab === 'layanan_mediasi' ? 'var(--brand)' : 'transparent' ?>;
                  cursor:pointer;
                  font-weight:<?= $tab === 'layanan_mediasi' ? '600' : 'normal' ?>;
                  border-radius:4px 4px 0 0">
            <i class="fas fa-handshake"></i> Layanan Mediasi
        </a>
		<a href="?tab=rekap_absen"
           style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;
                  color:<?= $tab === 'rekap_absen' ? 'var(--brand)' : 'var(--text-light)' ?>;
                  background-color:<?= $tab === 'rekap_absen' ? '#E8E4FF' : 'transparent' ?>;
                  border-bottom:3px solid <?= $tab === 'rekap_absen' ? 'var(--brand)' : 'transparent' ?>;
                  cursor:pointer;
                  font-weight:<?= $tab === 'rekap_absen' ? '600' : 'normal' ?>;
                  border-radius:4px 4px 0 0">
            <i class="fas fa-clipboard-list"></i> Absen Siswa
        </a>
		<a href="?tab=data_siswa_content"
           style="padding:0.75rem 1.5rem;text-decoration:none;display:flex;align-items:center;gap:0.5rem;
                  color:<?= $tab === 'data_siswa_content' ? 'var(--brand)' : 'var(--text-light)' ?>;
                  background-color:<?= $tab === 'data_siswa_content' ? '#E8E4FF' : 'transparent' ?>;
                  border-bottom:3px solid <?= $tab === 'data_siswa_content' ? 'var(--brand)' : 'transparent' ?>;
                  cursor:pointer;
                  font-weight:<?= $tab === 'data_siswa_content' ? '600' : 'normal' ?>;
                  border-radius:4px 4px 0 0">
            <i class="fas fa-user-circle"></i> Rekap Data Pribadi Siswa
        </a>
    </div>

    <!-- Content Tab -->
    <div class="card">
        <?php if ($tab === 'kegiatan_harian'): ?>
            <?php include 'kegiatan_harian_content.php'; ?>
        <?php elseif ($tab === 'rekap_absen'): ?>
            <?php include '../absensi/rekap_absen.php'; ?>
        <?php elseif ($tab === 'layanan_mediasi'): ?>
            <?php include 'layanan_mediasi_content.php'; ?>
		<?php elseif($tab === 'data_siswa_content'): ?>
			<?php include '../rekap/data_siswa_content.php'; ?>
        <?php endif; ?>
    </div>
</div>

<?php include "../../layouts/footer.php"; ?>
