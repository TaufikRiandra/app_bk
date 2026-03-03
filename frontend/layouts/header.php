<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Aplikasi BK</title>
	<link rel="stylesheet" href="/frontend/assets/css/style.css?v=2">
	<script defer src="/frontend/assets/js/theme.js"></script>
</head>
<body>
	<header class="site-header">
		<div style="max-width:1200px;margin:0 auto;padding:1rem;display:flex;align-items:center;justify-content:space-between">
			<div class="brand">SISTEM BIMBINGAN KONSELING</div>
			<nav class="top-nav">
				<button id="theme-toggle" aria-label="Toggle theme">☀️</button>
				<?php if (isset($_SESSION['login']) && $_SESSION['login']): ?>
					<a href="/backend/auth/logout.php" class="btn" style="background:#dc2626">🚪 Logout</a>
				<?php else: ?>
					<a class="btn register" href="/frontend/auth/login.php">Masuk</a>
					<a class="btn" href="/frontend/auth/register.php" style="margin-left:0.25rem">Daftar</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>
	<main>