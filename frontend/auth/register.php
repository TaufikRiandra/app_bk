<?php
session_start();
include '../layouts/header.php';

$error   = isset($_SESSION['flash_error'])   ? $_SESSION['flash_error']   : null;
$success = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : null;
$old     = isset($_SESSION['old_username'])  ? $_SESSION['old_username']  : '';
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['old_username']);
?>

<div style="display:flex;justify-content:center;align-items:center;min-height:calc(100vh - 80px);padding:1rem">
  <section class="card" style="max-width:420px;width:100%">

    <div style="text-align:center;margin-bottom:2rem">
      <div style="width:60px;height:60px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
        <i class="fas fa-user-plus" style="color:white;font-size:1.4rem"></i>
      </div>
      <h1 style="margin-bottom:0.25rem;font-size:1.6rem">Buat Akun Baru</h1>
      <p style="color:var(--text-light);font-size:0.9rem;margin:0">
        Daftar sebagai Guru BK — akun akan aktif setelah disetujui admin
      </p>
    </div>

    <!-- Info banner aktivasi -->
    <div style="padding:0.75rem 1rem;background:#fef9c3;border:1px solid #fde047;border-radius:8px;margin-bottom:1.5rem;font-size:0.85rem;color:#854d0e;display:flex;gap:0.5rem;align-items:flex-start">
      <i class="fas fa-clock" style="margin-top:2px;flex-shrink:0"></i>
      <span>Akun baru memerlukan <strong>aktivasi dari admin</strong> sebelum bisa digunakan.</span>
    </div>

    <?php if ($error): ?>
      <div class="message error">
        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="message success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form action="../../backend/auth/register_proses.php" method="POST">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required
             value="<?= htmlspecialchars($old) ?>" placeholder="Minimal 3 karakter">
      <small style="color:var(--text-light);display:block;margin-top:0.25rem;margin-bottom:1rem">
        Unik, tidak bisa diubah setelah daftar
      </small>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="Minimal 6 karakter">
      <small style="color:var(--text-light);display:block;margin-top:0.25rem;margin-bottom:1rem">
        Gunakan kombinasi huruf dan angka
      </small>

      <label for="password_confirm">Konfirmasi Password</label>
      <input type="password" id="password_confirm" name="password_confirm" required placeholder="Ulangi password">

      <button type="submit" class="btn" style="width:100%;margin-top:1.5rem;padding:0.875rem ;font-size:1rem;display:flex;align-items:center;justify-content:center;gap:0.5rem">
        <i class="fas fa-user-plus"></i> Daftar Akun
      </button>
    </form>

    <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:1.5rem;text-align:center">
      <p style="font-size:0.9rem;color:var(--text-light);margin:0">
        Sudah punya akun?
        <a href="login.php" style="color:var(--brand);text-decoration:none;font-weight:600">Masuk di sini</a>
      </p>
    </div>
  </section>
</div>

<?php include '../layouts/footer.php'; ?>
