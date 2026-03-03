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
		<div class="header-content">
			<a href="/" class="site-logo">
				<i class="fas fa-graduation-cap"></i>
				BK System
			</a>
			<nav class="header-nav">
				<?php if (isset($_SESSION['login']) && $_SESSION['login']): ?>
					<a href="/backend/auth/logout.php" class="btn-secondary">
						<i class="fas fa-sign-out-alt"></i>
						Logout
					</a>
				<?php else: ?>
					<a href="/frontend/auth/login.php">
						<i class="fas fa-user"></i>
						Masuk
					</a>
					<a href="/frontend/auth/register.php">
						<i class="fas fa-user-plus"></i>
						Daftar
					</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>
	<main>
