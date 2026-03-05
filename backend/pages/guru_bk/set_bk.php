<?php
include "../../config/database.php";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id_sekolah = intval($_POST['id_sekolah'] ?? 0);
    $nama_kelas = mysqli_real_escape_string($conn, trim($_POST['kelas'] ?? ''));
    $id_guru_bk  = intval($_POST['id_guru_bk'] ?? 0);

    if($id_sekolah && !empty($nama_kelas) && $id_guru_bk > 0){

        // Verify guru_bk exists
        $guru_exists = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id_guru_bk FROM guru_bk WHERE id_guru_bk = $id_guru_bk"
        ));

        if(!$guru_exists){
            header("Location: ../../../frontend/sekolah/bk.php?error=Guru+BK+tidak+ditemukan");
            exit;
        }

        // Check if kelas already exists (pakai tabel kelas, bukan guru_bk_kelas)
        $check = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id_kelas FROM kelas WHERE nama_kelas = '$nama_kelas' AND id_sekolah = $id_sekolah"
        ));

        if($check){
            // Update: ganti guru BK di kelas tersebut
            $query = "UPDATE kelas SET id_guru_bk = $id_guru_bk
                      WHERE nama_kelas = '$nama_kelas' AND id_sekolah = $id_sekolah";
        } else {
            // Insert: tambah kelas baru
            $query = "INSERT INTO kelas (nama_kelas, id_guru_bk, id_sekolah)
                      VALUES ('$nama_kelas', $id_guru_bk, $id_sekolah)";
        }

        if(mysqli_query($conn, $query)){
            header("Location: ../../../frontend/sekolah/bk.php?success=1");
        } else {
            $err = urlencode(mysqli_error($conn));
            header("Location: ../../../frontend/sekolah/bk.php?error=$err");
        }

    } else {
        header("Location: ../../../frontend/sekolah/bk.php?error=Data+tidak+lengkap");
    }
    exit;
}
?>