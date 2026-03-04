<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('Location: /frontend/dashboard.php'); exit;
}
include '../../../backend/config/database.php';
include '../../layouts/header.php';
include '../../layouts/sidebar.php';

// JOIN guru_bk dengan users untuk dapat username dan is_active
$list = mysqli_query($conn,
    "SELECT gb.*, u.username, u.is_active, u.id_user
     FROM guru_bk gb
     JOIN users u ON u.id_user = gb.id_user
     ORDER BY gb.nama ASC"
);
?>

<section class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:2rem">
        <div>
            <h1 style="margin-bottom:0.25rem;font-size:1.75rem">
                <i class="fas fa-chalkboard-user"></i> Kelola Guru BK
            </h1>
            <p style="color:var(--text-light);margin:0">Manajemen akun dan data guru bimbingan konseling</p>
        </div>
        <a href="./tambah.php" class="btn" style="text-decoration:none">
            <i class="fas fa-plus"></i> Tambah Guru BK
        </a>
    </div>

    <!-- Pending Accounts Banner -->
    <?php
    $pending = mysqli_query($conn,
        "SELECT COUNT(*) AS c FROM users WHERE is_active = 0 AND role = 'guru_bk'"
    );
    $p = mysqli_fetch_assoc($pending);
    if ($p['c'] > 0):
    ?>
    <div style="padding:0.875rem 1rem;background:#fef9c3;border:1px solid #fde047;border-radius:8px;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem">
        <i class="fas fa-clock" style="color:#ca8a04"></i>
        <span><strong><?= $p['c'] ?> akun</strong> menunggu aktivasi dari Anda.</span>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div style="padding:1rem;background:#dcfce7;border:1px solid #86efac;border-radius:6px;margin-bottom:1.5rem;color:#166534">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div style="padding:1rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;margin-bottom:1.5rem;color:#991b1b">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($list) > 0): ?>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:0.9rem">
            <thead style="background:var(--bg-light);border-bottom:2px solid var(--border)">
                <tr>
                    <th style="padding:0.875rem 1rem;text-align:left">#</th>
                    <th style="padding:0.875rem 1rem;text-align:left">Foto</th>
                    <th style="padding:0.875rem 1rem;text-align:left">Nama & NIP</th>
                    <th style="padding:0.875rem 1rem;text-align:left">Username</th>
                    <th style="padding:0.875rem 1rem;text-align:left">Telepon</th>
                    <th style="padding:0.875rem 1rem;text-align:center">Status</th>
                    <th style="padding:0.875rem 1rem;text-align:left">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no=1; while ($row = mysqli_fetch_assoc($list)): ?>
                <tr style="border-bottom:1px solid var(--border)" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:0.875rem 1rem;color:var(--text-light)"><?= $no++ ?></td>
                    <td style="padding:0.875rem 1rem">
                        <?php if ($row['foto']): ?>
                            <img src="<?= htmlspecialchars($row['foto']) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover">
                        <?php else: ?>
                            <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:white">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:0.875rem 1rem">
                        <strong><?= htmlspecialchars($row['nama']) ?></strong>
                        <div style="font-size:0.8rem;color:var(--text-light);margin-top:2px">
                            <code><?= htmlspecialchars($row['nip']) ?></code>
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem">
                        <code style="background:var(--bg-light);padding:2px 6px;border-radius:4px">
                            <?= htmlspecialchars($row['username']) ?>
                        </code>
                    </td>
                    <td style="padding:0.875rem 1rem"><?= htmlspecialchars($row['no_telp'] ?: '-') ?></td>
                    <td style="padding:0.875rem 1rem;text-align:center">
                        <button
                            onclick="toggleAktif(<?= $row['id_user'] ?>, <?= $row['is_active'] ?>, this)"
                            style="border:none;cursor:pointer;padding:4px 12px;border-radius:999px;font-size:0.78rem;font-weight:600;
                                   background:<?= $row['is_active'] ? '#dcfce7' : '#fee2e2' ?>;
                                   color:<?= $row['is_active'] ? '#166534' : '#991b1b' ?>">
                            <i class="fas <?= $row['is_active'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                            <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </button>
                    </td>
                    <td style="padding:0.875rem 1rem">
                        <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
                            <a href="./edit.php?id=<?= $row['id_guru_bk'] ?>" class="btn"
                               style="text-decoration:none;padding:0.4rem 0.875rem;font-size:0.85rem">
                                <i class="fas fa-pencil"></i> Edit
                            </a>
                            <a href="javascript:void(0)"
                               onclick="if(confirm('Hapus guru BK ini beserta akun loginnya?')) location.href='/backend/pages/sekolah/guru_bk/delete.php?id=<?= $row['id_guru_bk'] ?>'"
                               class="btn" style="text-decoration:none;padding:0.4rem 0.875rem;font-size:0.85rem;background:#ef4444;color:white">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:3rem;background:var(--bg-light);border-radius:8px">
        <i class="fas fa-user-slash" style="font-size:2.5rem;color:var(--text-light);display:block;margin-bottom:1rem"></i>
        <p style="color:var(--text-light);margin:0">Belum ada guru BK terdaftar</p>
        <a href="./tambah.php" class="btn" style="text-decoration:none;margin-top:1rem;display:inline-block">
            Tambah Sekarang
        </a>
    </div>
    <?php endif; ?>
</section>

<script>
function toggleAktif(id_user, current_status, btn) {
    const new_status = current_status == 1 ? 0 : 1;
    const label = new_status ? 'mengaktifkan' : 'menonaktifkan';
    if (!confirm('Yakin ingin ' + label + ' akun ini?')) return;

    const fd = new FormData();
    fd.append('id_user',   id_user);
    fd.append('is_active', new_status);

    fetch('/backend/pages/sekolah/guru_bk/activate_user.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.setAttribute('onclick', `toggleAktif(${id_user}, ${new_status}, this)`);
                btn.style.background = new_status ? '#dcfce7' : '#fee2e2';
                btn.style.color      = new_status ? '#166534' : '#991b1b';
                btn.innerHTML = `<i class="fas ${new_status ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${new_status ? 'Aktif' : 'Nonaktif'}`;
            } else {
                alert(data.message);
            }
        })
        .catch(() => alert('Gagal menghubungi server'));
}
</script>

<?php include '../../layouts/footer.php'; ?>
