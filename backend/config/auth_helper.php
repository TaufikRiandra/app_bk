<?php
/**
 * Auth Helper - Kontrol akses berdasarkan kelas yang ditetapkan untuk Guru BK
 * Jika role = admin  → akses penuh ke semua kelas & semua guru BK
 * Jika role = guru_bk → hanya bisa akses kelas yang ditetapkan untuknya
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect ke halaman login jika belum login
 */
function requireLogin() {
    if (!isset($_SESSION['login']) || !$_SESSION['login']) {
        header('Location: /frontend/auth/login.php');
        exit;
    }
}

/**
 * Redirect ke dashboard jika bukan admin
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /frontend/dashboard.php');
        exit;
    }
}

/**
 * Cek apakah user yang login adalah admin
 */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Ambil id_guru_bk dari session (null jika admin)
 */
function getSessionGuruBkId(): ?int {
    if (isset($_SESSION['id_guru_bk']) && $_SESSION['id_guru_bk']) {
        return (int)$_SESSION['id_guru_bk'];
    }
    return null;
}

/**
 * Ambil daftar kelas yang ditetapkan untuk guru_bk yang sedang login.
 * Jika admin → kembalikan semua kelas.
 * 
 * @param mysqli $conn
 * @return array  array of nama_kelas strings
 */
function getKelasForCurrentUser($conn): array {
    if (isAdmin()) {
        // Admin: ambil semua kelas dari DB
        $res = mysqli_query($conn,
            "SELECT DISTINCT nama_kelas FROM kelas ORDER BY nama_kelas");
        $list = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $list[] = $row['nama_kelas'];
        }
        // Fallback ke kelas dari tabel siswa jika kelas kosong
        if (empty($list)) {
            $res2 = mysqli_query($conn,
                "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas");
            while ($row = mysqli_fetch_assoc($res2)) {
                $list[] = $row['kelas'];
            }
        }
        return $list;
    }

    // Guru BK: ambil hanya kelas yang ditetapkan
    $id_guru_bk = getSessionGuruBkId();
    if (!$id_guru_bk) return [];

    $res = mysqli_query($conn,
        "SELECT nama_kelas FROM kelas WHERE id_guru_bk = $id_guru_bk ORDER BY nama_kelas");
    $list = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $list[] = $row['nama_kelas'];
    }
    return $list;
}

/**
 * Cek apakah guru_bk yang login berhak mengakses kelas tertentu.
 * Admin selalu boleh.
 * 
 * @param mysqli $conn
 * @param string $nama_kelas
 * @return bool
 */
function canAccessKelas($conn, string $nama_kelas): bool {
    if (isAdmin()) return true;

    $id_guru_bk = getSessionGuruBkId();
    if (!$id_guru_bk) return false;

    $escaped = mysqli_real_escape_string($conn, $nama_kelas);
    $res = mysqli_query($conn,
        "SELECT id_kelas FROM kelas
         WHERE nama_kelas = '$escaped' AND id_guru_bk = $id_guru_bk
         LIMIT 1");
    return $res && mysqli_num_rows($res) > 0;
}

/**
 * Ambil data guru_bk yang sedang login (nama, nip, id).
 * Mengembalikan null jika admin.
 * 
 * @param mysqli $conn
 * @return array|null
 */
function getCurrentGuruBkData($conn): ?array {
    $id = getSessionGuruBkId();
    if (!$id) return null;

    $res = mysqli_query($conn,
        "SELECT id_guru_bk, nama, nip FROM guru_bk WHERE id_guru_bk = $id LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        return $row;
    }
    return null;
}
