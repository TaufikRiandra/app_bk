<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('Location: /frontend/dashboard.php'); exit;
}
include '../../../backend/config/database.php';
include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<section class="card" style="max-width:620px;margin:0 auto">
    <div style="margin-bottom:2rem">
        <h1 style="margin-bottom:0.25rem;font-size:1.75rem">
            <i class="fas fa-user-plus"></i> Tambah Guru BK
        </h1>
        <p style="color:var(--text-light);margin:0">
            Daftarkan guru BK baru — akun login akan otomatis dibuat dan langsung aktif.
        </p>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div style="padding:1rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;margin-bottom:1.5rem;color:#991b1b">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="/backend/pages/sekolah/guru_bk/create.php" method="POST" enctype="multipart/form-data">

        <!-- ── Data Akun ── -->
        <h3 style="font-size:1rem;margin-bottom:1rem;color:var(--text-light);border-bottom:1px solid var(--border);padding-bottom:0.5rem">
            <i class="fas fa-key"></i> Data Akun Login
        </h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
                <label style="display:block;margin-bottom:0.4rem;font-weight:600">
                    Username <span style="color:#ef4444">*</span>
                </label>
                <input type="text" name="username" required placeholder="e.g. budi.santoso"
                    style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
                <small style="color:var(--text-light)">Minimal 3 karakter</small>
            </div>
            <div>
                <label style="display:block;margin-bottom:0.4rem;font-weight:600">
                    Password <span style="color:#ef4444">*</span>
                </label>
                <input type="password" name="password" required placeholder="Min. 6 karakter"
                    style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
            </div>
        </div>

        <!-- ── Data Pribadi ── -->
        <h3 style="font-size:1rem;margin:1.5rem 0 1rem;color:var(--text-light);border-bottom:1px solid var(--border);padding-bottom:0.5rem">
            <i class="fas fa-id-card"></i> Data Pribadi
        </h3>

        <div style="margin-bottom:1.25rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">
                NIP <span style="color:#ef4444">*</span>
            </label>
            <input type="text" name="nip" required placeholder="19xx0101 xxxxxx x xxx"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box"
                pattern="[0-9\s]+" title="NIP hanya angka dan spasi">
        </div>

        <div style="margin-bottom:1.25rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">
                Nama Lengkap <span style="color:#ef4444">*</span>
            </label>
            <input type="text" name="nama" required placeholder="Nama guru BK"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
        </div>

        <div style="margin-bottom:1.25rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">No. Telepon</label>
            <input type="tel" name="no_telp" placeholder="08xxxxxxxxxx"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.95rem;box-sizing:border-box">
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600">Foto Profil</label>
            <div style="display:flex;align-items:center;gap:1rem">
                <div id="preview" style="width:80px;height:80px;border:2px dashed var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--bg-light);color:var(--text-light);font-size:2rem;overflow:hidden">
                    <i class="fas fa-camera"></i>
                </div>
                <div>
                    <input type="file" id="foto" name="foto" accept="image/*" style="display:none" onchange="previewImage(event)">
                    <button type="button" onclick="document.getElementById('foto').click()" class="btn" style="border:none;cursor:pointer">
                        <i class="fas fa-upload"></i> Pilih Foto
                    </button>
                    <small style="display:block;margin-top:0.4rem;color:var(--text-light)">JPG/PNG/GIF, maks 5MB</small>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Simpan Guru BK
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
            '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:6px">';
    };
    reader.readAsDataURL(file);
}
</script>

<?php include '../../layouts/footer.php'; ?>
