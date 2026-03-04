<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('Location: /frontend/dashboard.php'); exit;
}
include '../../../backend/config/database.php';
include '../../layouts/header.php';
include '../../layouts/sidebar.php';

$id_guru_bk = intval($_GET['id'] ?? 0);
if (!$id_guru_bk) { header('Location: ./index.php?error=' . urlencode('ID tidak valid')); exit; }

$stmt = mysqli_prepare($conn,
    'SELECT gb.*, u.username, u.is_active FROM guru_bk gb JOIN users u ON u.id_user = gb.id_user WHERE gb.id_guru_bk = ?'
);
mysqli_stmt_bind_param($stmt, 'i', $id_guru_bk);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) { header('Location: ./index.php?error=' . urlencode('Guru BK tidak ditemukan')); exit; }
?>

<section class="card" style="max-width:620px;margin:0 auto">
    <div style="margin-bottom:2rem">
        <h1 style="margin-bottom:0.25rem;font-size:1.75rem">
            <i class="fas fa-user-edit"></i> Edit Guru BK
        </h1>
        <p style="color:var(--text-light);margin:0">Perbarui data guru BK — username tidak dapat diubah</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div style="padding:1rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;margin-bottom:1.5rem;color:#991b1b">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="/backend/pages/sekolah/guru_bk/update.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_guru_bk" value="<?= $data['id_guru_bk'] ?>">

        <!-- Read-only username -->
        <div style="margin-bottom:1.25rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600;color:var(--text-light)">
                <i class="fas fa-user"></i> Username
            </label>
            <input type="text" value="<?= htmlspecialchars($data['username']) ?>" disabled
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;background:var(--bg-light);color:var(--text-light);box-sizing:border-box">
        </div>

        <!-- Reset Password (opsional) -->
        <div style="margin-bottom:1.5rem;padding:1rem;background:var(--bg-light);border-radius:8px;border:1px solid var(--border)">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">
                <i class="fas fa-lock"></i> Reset Password <span style="font-weight:normal;color:var(--text-light)">(opsional)</span>
            </label>
            <input type="password" name="new_password" placeholder="Kosongkan jika tidak ingin mengubah"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
            <small style="color:var(--text-light)">Minimal 6 karakter jika diisi</small>
        </div>

        <h3 style="font-size:1rem;margin-bottom:1rem;color:var(--text-light);border-bottom:1px solid var(--border);padding-bottom:0.5rem">
            <i class="fas fa-id-card"></i> Data Pribadi
        </h3>

        <div style="margin-bottom:1.25rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">NIP <span style="color:#ef4444">*</span></label>
            <input type="text" name="nip" required value="<?= htmlspecialchars($data['nip']) ?>"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
        </div>

        <div style="margin-bottom:1.25rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">Nama Lengkap <span style="color:#ef4444">*</span></label>
            <input type="text" name="nama" required value="<?= htmlspecialchars($data['nama']) ?>"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
        </div>

        <div style="margin-bottom:1.25rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">No. Telepon</label>
            <input type="tel" name="no_telp" value="<?= htmlspecialchars($data['no_telp'] ?? '') ?>"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">Foto Profil</label>
            <div style="display:flex;align-items:center;gap:1rem">
                <div id="preview" style="width:80px;height:80px;border:2px dashed var(--border);border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--bg-light)">
                    <?php if ($data['foto']): ?>
                        <img src="<?= htmlspecialchars($data['foto']) ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                        <i class="fas fa-camera" style="font-size:1.5rem;color:var(--text-light)"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="file" id="foto" name="foto" accept="image/*" style="display:none" onchange="previewImage(event)">
                    <button type="button" onclick="document.getElementById('foto').click()" class="btn" style="border:none;cursor:pointer">
                        <i class="fas fa-upload"></i> Ubah Foto
                    </button>
                    <small style="display:block;margin-top:0.4rem;color:var(--text-light)">JPG/PNG/GIF maks 5MB</small>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:1rem;flex-wrap:wrap">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            <a href="./index.php" class="btn secondary" style="text-decoration:none">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</section>

<script>
function previewImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('preview').innerHTML =
            '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover">';
    };
    reader.readAsDataURL(file);
}
</script>

<?php include '../../layouts/footer.php'; ?>
