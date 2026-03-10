<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['login'])) {
    header('Location: ../../auth/login.php'); exit;
}
include '../../../backend/config/database.php';
include '../../layouts/header.php';
include '../../layouts/sidebar.php';

$id_user    = intval($_SESSION['id_user'] ?? 0);
$id_guru_bk = intval($_SESSION['id_guru_bk'] ?? 0);
$role       = $_SESSION['role'] ?? '';

// Ambil data profil
if ($role === 'guru_bk' && $id_guru_bk) {
    $stmt = mysqli_prepare($conn,
        'SELECT gb.*, u.username FROM guru_bk gb JOIN users u ON u.id_user = gb.id_user WHERE gb.id_guru_bk = ?'
    );
    mysqli_stmt_bind_param($stmt, 'i', $id_guru_bk);
    mysqli_stmt_execute($stmt);
    $profil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
} else {
    // Admin: hanya username
    $stmt = mysqli_prepare($conn, 'SELECT username, created_at FROM users WHERE id_user = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    mysqli_stmt_execute($stmt);
    $u_row  = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $profil = [
        'nama'     => 'Administrator',
        'nip'      => '-',
        'no_telp'  => '-',
        'foto'     => '',
        'username' => $u_row['username'] ?? '',
    ];
}

$foto_url = $profil['foto'] ?? '';
?>

<section class="card" style="max-width:700px;margin:0 auto">

    <!-- Header Profil -->
    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border)">
        <!-- Avatar + upload foto -->
        <div style="position:relative;flex-shrink:0">
            <div id="avatarWrap" style="width:90px;height:90px;border-radius:50%;overflow:hidden;border:3px solid var(--primary);background:var(--bg-light);display:flex;align-items:center;justify-content:center">
                <?php if ($foto_url): ?>
                    <img id="avatarImg" src="<?= htmlspecialchars($foto_url) ?>" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                    <i class="fas fa-user-tie" style="font-size:2.5rem;color:var(--text-light)"></i>
                <?php endif; ?>
            </div>
            <?php if ($role === 'guru_bk'): ?>
            <label for="inputFoto" title="Ubah foto" style="position:absolute;bottom:0;right:0;background:var(--primary);color:white;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.75rem">
                <i class="fas fa-camera"></i>
            </label>
            <input type="file" id="inputFoto" accept="image/*" style="display:none" onchange="uploadFoto(this)">
            <?php endif; ?>
        </div>

        <div>
            <h1 style="margin:0 0 0.25rem;font-size:1.5rem"><?= htmlspecialchars($profil['nama']) ?></h1>
            <p style="margin:0 0 0.25rem;color:var(--text-light);font-size:0.9rem">
                <i class="fas fa-user" style="width:16px"></i>
                <?= htmlspecialchars($profil['username']) ?>
            </p>
            <span style="display:inline-flex;align-items:center;gap:5px;background:var(--bg-light);padding:3px 10px;border-radius:999px;font-size:0.8rem;color:var(--text-light)">
                <i class="fas fa-shield-alt"></i>
                <?= $role === 'admin' ? 'Administrator' : 'Guru BK' ?>
            </span>
        </div>
    </div>

    <div id="fotoMsg" style="display:none;padding:0.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:0.9rem"></div>

    <!-- Info Profil (guru_bk saja) -->
    <?php if ($role === 'guru_bk'): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem">
        <div style="padding:1rem;background:var(--bg-light);border-radius:8px;border:1px solid var(--border)">
            <div style="font-size:0.78rem;color:var(--text-light);margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px">NIP</div>
            <div style="font-weight:600"><?= htmlspecialchars($profil['nip']) ?></div>
        </div>
    <div style="padding:1rem;background:var(--bg-light);border-radius:8px;border:1px solid var(--border)">
        <div style="font-size:0.78rem;color:var(--text-light);margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px">No. Telepon</div>
        <div style="display:flex;align-items:center;gap:8px" id="noTelpDisplay">
            <span id="noTelpText" style="font-weight:600"><?= htmlspecialchars($profil['no_telp'] ?: '-') ?></span>
            <button onclick="editNoTelp()" title="Edit" style="background:none;border:none;cursor:pointer;color:var(--text-light);font-size:0.8rem;padding:2px 5px">
                <i class="fas fa-pen"></i>
            </button>
        </div>
        <div id="noTelpEdit" style="display:none;margin-top:6px">
            <input type="text" id="inputNoTelp" value="<?= htmlspecialchars($profil['no_telp'] ?: '') ?>"
                placeholder="Contoh: 08123456789"
                style="width:100%;padding:0.5rem 0.6rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem;box-sizing:border-box;margin-bottom:6px">
            <div style="display:flex;gap:6px">
                <button onclick="simpanNoTelp()" style="padding:4px 12px;background:var(--primary,#4472C4);color:white;border:none;border-radius:5px;cursor:pointer;font-size:0.82rem;font-weight:600">
                    <i class="fas fa-check"></i> Simpan
                </button>
                <button onclick="batalEditNoTelp()" style="padding:4px 10px;background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border);border-radius:5px;cursor:pointer;font-size:0.82rem">
                    Batal
                </button>
            </div>
        </div>
        <div id="noTelpMsg" style="display:none;margin-top:6px;font-size:0.82rem;padding:4px 8px;border-radius:4px"></div>
    </div>
    </div>
    <?php endif; ?>

    <!-- Ganti Password -->
    <div style="padding:1.5rem;border:1px solid var(--border);border-radius:10px">
        <h3 style="margin:0 0 1.25rem;font-size:1rem">
            <i class="fas fa-lock" style="color:var(--primary)"></i> Ganti Password
        </h3>

        <div id="pwMsg" style="display:none;padding:0.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:0.9rem"></div>

        <div style="margin-bottom:1rem">
            <label style="display:block;margin-bottom:0.4rem;font-weight:600;font-size:0.9rem">Password Saat Ini</label>
            <input type="password" id="current_password" placeholder="Masukkan password saat ini"
                style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem;box-sizing:border-box">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
            <div>
                <label style="display:block;margin-bottom:0.4rem;font-weight:600;font-size:0.9rem">Password Baru</label>
                <input type="password" id="new_password" placeholder="Min. 6 karakter"
                    style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem;box-sizing:border-box">
            </div>
            <div>
                <label style="display:block;margin-bottom:0.4rem;font-weight:600;font-size:0.9rem">Konfirmasi Password</label>
                <input type="password" id="confirm_password" placeholder="Ulangi password baru"
                    style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem;box-sizing:border-box">
            </div>
        </div>
        <button onclick="gantiPassword()" class="btn btn-success" style="display:flex;align-items:center;gap:7px">
            <i class="fas fa-key"></i> Simpan Password
        </button>
    </div>

</section>

<script>
/* ── Upload Foto ── */
function uploadFoto(input) {
    const file = input.files[0];
    if (!file) return;

    const fd = new FormData();
    fd.append('action', 'foto');
    fd.append('foto', file);

    const msg = document.getElementById('fotoMsg');
    msg.style.display = 'block';
    msg.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengunggah foto...';
    msg.style.background = '#f0f9ff';
    msg.style.color = '#0369a1';

    fetch('../../../backend/pages/update_profil.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Perbarui avatar tampilan
                const wrap = document.getElementById('avatarWrap');
                wrap.innerHTML = '<img id="avatarImg" src="' + data.foto_url + '?t=' + Date.now() +
                    '" style="width:100%;height:100%;object-fit:cover">';
                showMsg(msg, 'success', '<i class="fas fa-check-circle"></i> ' + data.message);
            } else {
                showMsg(msg, 'error', '<i class="fas fa-exclamation-circle"></i> ' + data.message);
            }
        })
        .catch(() => showMsg(msg, 'error', 'Gagal menghubungi server'));
}

/* ── Ganti Password ── */
function gantiPassword() {
    const current = document.getElementById('current_password').value;
    const newpw   = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;
    const msg     = document.getElementById('pwMsg');

    const fd = new FormData();
    fd.append('action',           'password');
    fd.append('current_password', current);
    fd.append('new_password',     newpw);
    fd.append('confirm_password', confirm);

    fetch('/backend/pages/update_profil.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showMsg(msg, 'success', '<i class="fas fa-check-circle"></i> ' + data.message);
                document.getElementById('current_password').value = '';
                document.getElementById('new_password').value     = '';
                document.getElementById('confirm_password').value = '';
            } else {
                showMsg(msg, 'error', '<i class="fas fa-exclamation-circle"></i> ' + data.message);
            }
        })
        .catch(() => showMsg(msg, 'error', 'Gagal menghubungi server'));
}

function showMsg(el, type, html) {
    el.style.display    = 'block';
    el.innerHTML        = html;
    el.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    el.style.color      = type === 'success' ? '#166534' : '#991b1b';
    setTimeout(() => el.style.display = 'none', 4000);
}
/* ── Edit No. Telepon ── */
function editNoTelp() {
    document.getElementById('noTelpDisplay').style.display = 'none';
    document.getElementById('noTelpEdit').style.display    = 'block';
    document.getElementById('inputNoTelp').focus();
}

function batalEditNoTelp() {
    document.getElementById('noTelpEdit').style.display    = 'none';
    document.getElementById('noTelpDisplay').style.display = 'flex';
    document.getElementById('noTelpMsg').style.display     = 'none';
}

function simpanNoTelp() {
    const val = document.getElementById('inputNoTelp').value.trim();
    const msg = document.getElementById('noTelpMsg');

    const fd = new FormData();
    fd.append('action', 'notelp');
    fd.append('no_telp', val);

    fetch('../../../backend/pages/update_profil.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('noTelpText').textContent = val || '-';
                batalEditNoTelp();
                showMsg(msg, 'success', '<i class="fas fa-check-circle"></i> ' + data.message);
                msg.style.display = 'block';
                setTimeout(() => msg.style.display = 'none', 3000);
            } else {
                showMsg(msg, 'error', '<i class="fas fa-exclamation-circle"></i> ' + data.message);
                msg.style.display = 'block';
            }
        })
        .catch(() => {
            showMsg(msg, 'error', 'Gagal menghubungi server');
            msg.style.display = 'block';
        });
}

</script>

<?php include '../../layouts/footer.php'; ?>
