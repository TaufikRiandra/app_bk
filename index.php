<?php
session_start();

/*
|--------------------------------------------------------------------------
| INDEX UTAMA APLIKASI BK
|--------------------------------------------------------------------------
| Jika sudah login → ke dashboard
| Jika belum login → ke halaman login
|--------------------------------------------------------------------------
*/

if(isset($_SESSION['login'])){
    header("Location: frontend/dashboard.php");
} else {
    header("Location: frontend/auth/login.php");
}

exit;
?>