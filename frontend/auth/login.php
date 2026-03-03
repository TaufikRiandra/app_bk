<?php
// include header which will start session if none exists
include "../layouts/header.php";

// Grab and clear flash messages
$error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
$success = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : null;
$old = isset($_SESSION['old_username']) ? $_SESSION['old_username'] : '';
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['old_username']);
?>

<div style="display:flex;justify-content:center;align-items:center;min-height:calc(100vh - 80px);padding:1rem">
  <section class="card" style="max-width:400px;width:100%">
    <div style="text-align:center;margin-bottom:2rem">
      <div style="font-size:2.5rem;margin-bottom:1rem">🔐</div>
      <h1 style="margin-bottom:0.25rem;font-size:1.75rem">Masuk ke Sistem</h1>
      <p class="lead" style="text-align:center;font-size:0.95rem">Selamat datang kembali di Sistem Bimbingan Konseling</p>
    </div>

    <?php if ($error): ?>
      <div class="message error">
        ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="message success">
        ✅ <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form action="../../backend/auth/login_proses.php" method="POST">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required value="<?= htmlspecialchars($old, ENT_QUOTES, 'UTF-8') ?>" placeholder="Masukkan username">
      
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="Masukkan password">
      
      <button type="submit" class="btn" style="width:100%;margin-top:1.5rem;padding:0.875rem">
        🔓 Masuk Sekarang
      </button>
    </form>

    <div style="border-top:1px solid #e5e7eb;padding-top:1rem;margin-top:1.5rem;text-align:center">
      <p style="font-size:0.9rem;color:var(--text-light);margin:0">
        Belum punya akun? 
        <a href="register.php" style="color:var(--brand);text-decoration:none;font-weight:600">Daftar di sini</a>
      </p>
    </div>
  </section>
</div>

<?php include "../layouts/footer.php"; ?>
