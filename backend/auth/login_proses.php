<?php
session_start();
include "../config/database.php";

$username = $_POST['username'];
$password = md5($_POST['password']);

$query = mysqli_query($conn,"SELECT * FROM users 
WHERE username='$username' AND password='$password'");

if(mysqli_num_rows($query) > 0){
    $user = mysqli_fetch_assoc($query);
    $_SESSION['login'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];
    header("Location: ../../frontend/dashboard.php");
} else {
    $_SESSION['flash_error'] = 'Login gagal: username atau password salah.';
    $_SESSION['old_username'] = $username;
    header("Location: ../../frontend/auth/login.php");
}
?>