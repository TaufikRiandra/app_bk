<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$foto_profil  = isset($_SESSION['foto_profil']) ? $_SESSION['foto_profil'] : '';
$nama_display = isset($_SESSION['nama_guru'])   ? $_SESSION['nama_guru']   : ($_SESSION['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi BK</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="/frontend/assets/js/theme.js"></script>
</head>
<body>
    <header class="site-header">
        <div class="header-content">
            <a href="/" class="site-logo">
                <i class="fas fa-graduation-cap"></i>
                BK System
            </a>
            <nav class="header-nav">
                <?php if (isset($_SESSION['login']) && $_SESSION['login']): ?>
                    <!-- Avatar dropdown -->
                    <div style="position:relative" id="avatarMenu">
                        <button onclick="toggleMenu()" style="background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:0.5rem;padding:0.375rem 0.75rem;border-radius:999px;border:1px solid var(--border)">
                            <div style="width:30px;height:30px;border-radius:50%;overflow:hidden;background:var(--bg-light);display:flex;align-items:center;justify-content:center">
                                <?php if ($foto_profil): ?>
                                    <img src="<?= htmlspecialchars($foto_profil) ?>" style="width:100%;height:100%;object-fit:cover">
                                <?php else: ?>
                                    <i class="fas fa-user" style="font-size:0.85rem;color:var(--text-light)"></i>
                                <?php endif; ?>
                            </div>
                            <span style="font-size:0.875rem;font-weight:600;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?= htmlspecialchars($nama_display) ?>
                            </span>
                            <i class="fas fa-chevron-down" style="font-size:0.7rem;color:var(--text-light)"></i>
                        </button>
                        <div id="dropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:white;border:1px solid var(--border);border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.12);min-width:170px;z-index:999;overflow:hidden">
                            <a href="/frontend/pages/profil/index.php" style="display:flex;align-items:center;gap:8px;padding:10px 14px;text-decoration:none;color:var(--text);font-size:0.875rem" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background=''">
                                <i class="fas fa-user-cog" style="width:16px;color:var(--primary)"></i> Profil Saya
                            </a>
                            <div style="border-top:1px solid var(--border)"></div>
                            <a href="/backend/auth/logout.php" style="display:flex;align-items:center;gap:8px;padding:10px 14px;text-decoration:none;color:#ef4444;font-size:0.875rem" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
                                <i class="fas fa-sign-out-alt" style="width:16px"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/frontend/auth/login.php">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                    <a href="/frontend/auth/register.php">
                        <i class="fas fa-user-plus"></i> Daftar
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>

<script>
function toggleMenu() {
    const m = document.getElementById('dropdownMenu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', e => {
    if (!document.getElementById('avatarMenu')?.contains(e.target)) {
        const m = document.getElementById('dropdownMenu');
        if (m) m.style.display = 'none';
    }
});
</script>
