<?php
$GLOBALS['sidebar_included'] = true;
$role       = isset($_SESSION['role'])       ? $_SESSION['role']       : 'guest';
$username   = isset($_SESSION['username'])   ? $_SESSION['username']   : '';
$nama_guru  = isset($_SESSION['nama_guru'])  ? $_SESSION['nama_guru']  : $username;
$foto_profil = isset($_SESSION['foto_profil']) ? $_SESSION['foto_profil'] : '';

// Tentukan halaman aktif
$current = $_SERVER['REQUEST_URI'];
function isActive($path) {
    global $current;
    return strpos($current, $path) !== false ? 'active' : '';
}
?>
<div class="main-container">
    <aside class="sidebar">

        <!-- Info user di atas sidebar -->
        <a href="/frontend/pages/profil/index.php" class="sidebar-user-card"
           style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;text-decoration:none;border-bottom:1px solid rgba(255,255,255,0.1);margin-bottom:0.5rem;transition:background 0.2s"
           onmouseover="this.style.background='rgba(255,255,255,0.08)'"
           onmouseout="this.style.background='transparent'">
            <div style="width:38px;height:38px;border-radius:50%;overflow:hidden;border:2px solid rgba(255,255,255,0.3);flex-shrink:0;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center">
                <?php if ($foto_profil): ?>
                    <img src="<?= htmlspecialchars($foto_profil) ?>" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                    <i class="fas fa-user" style="color:rgba(255,255,255,0.7);font-size:1rem"></i>
                <?php endif; ?>
            </div>
            <div style="min-width:0">
                <div style="color:white;font-weight:600;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    <?= htmlspecialchars($nama_guru) ?>
                </div>
                <div style="color:rgba(255,255,255,0.6);font-size:0.75rem">
                    <?= $role === 'admin' ? 'Administrator' : 'Guru BK' ?>
                </div>
            </div>
            <i class="fas fa-chevron-right" style="margin-left:auto;color:rgba(255,255,255,0.4);font-size:0.7rem"></i>
        </a>

        <ul class="sidebar-menu">
            <li>
                <a href="/frontend/dashboard.php" class="sidebar-link <?= isActive('/dashboard') ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/frontend/pages/konseling/data_pribadi.php" class="sidebar-link <?= isActive('/konseling') ?>">
                    <i class="fas fa-user-circle"></i> Data Pribadi Siswa
                </a>
            </li>
            <li>
                <a href="/frontend/pages/absensi/index.php" class="sidebar-link <?= isActive('/absensi') ?>">
                    <i class="fas fa-clipboard-list"></i> Absensi
                </a>
            </li>
            <li>
                <a href="/frontend/pages/kegiatan_harian_manual.php" class="sidebar-link <?= isActive('/kegiatan') ?>">
                    <i class="fas fa-calendar-alt"></i> Kegiatan Harian
                </a>
            </li>
            <li>
                <a href="/frontend/pages/penilaian.php" class="sidebar-link <?= isActive('/penilaian') ?>">
                    <i class="fas fa-star"></i> Penilaian Siswa
                </a>
            </li>
            <li>
                <a href="/frontend/pages/layanan_mediasi_manual.php" class="sidebar-link <?= isActive('/mediasi') ?>">
                    <i class="fas fa-handshake"></i> Layanan Mediasi
                </a>
            </li>

            <?php if ($role === 'admin'): ?>
                <li class="sidebar-divider" style="border-top:1px solid rgba(255,255,255,0.1);margin:0.5rem 0"></li>
                <li>
                    <a href="/frontend/pages/sekolah/index.php" class="sidebar-link <?= isActive('/sekolah') ?>">
                        <i class="fas fa-school"></i> Data Sekolah
                    </a>
                </li>
                <li>
                    <a href="/frontend/pages/guru_bk/index.php" class="sidebar-link <?= isActive('/guru_bk') ?>">
                        <i class="fas fa-chalkboard-user"></i> Kelola Guru BK
                    </a>
                </li>
            <?php elseif ($role === 'guru_bk'): ?>
                <li>
                    <a href="/frontend/pages/sekolah/kelas.php" class="sidebar-link <?= isActive('/kelas') ?>">
                        <i class="fas fa-book"></i> Data Kelas
                    </a>
                </li>
            <?php endif; ?>

            <li class="sidebar-divider" style="border-top:1px solid rgba(255,255,255,0.1);margin:0.5rem 0"></li>
            <li>
                <a href="/frontend/pages/rekap/rekap.php" class="sidebar-link <?= isActive('/rekap') ?>">
                    <i class="fas fa-file-pdf"></i> Rekap
                </a>
            </li>
            <li>
                <a href="/frontend/pages/profil/index.php" class="sidebar-link <?= isActive('/profil') ?>">
                    <i class="fas fa-user-cog"></i> Profil Saya
                </a>
            </li>
            <li>
                <a href="/backend/auth/logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>
    <section class="content-area">
