<?php
include "../config/database.php";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id_sekolah = intval($_POST['id_sekolah']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
    $id_guru_bk = intval($_POST['id_guru_bk'] ?? 0);
    
    if($id_sekolah && !empty($kelas) && $id_guru_bk > 0){
        // Verify guru_bk exists
        $guru_exists = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT id_guru_bk FROM guru_bk WHERE id_guru_bk = $id_guru_bk"
        ));
        
        if(!$guru_exists){
            header("Location: ../../frontend/sekolah/bk.php?error=Guru+BK+tidak+ditemukan");
            exit;
        }
        
        // Check if record exists for this class
        $check = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT id_bk FROM guru_bk_kelas WHERE id_sekolah = $id_sekolah AND kelas = '$kelas'"
        ));
        
        if($check){
            // Update existing record
            $query = "UPDATE guru_bk_kelas SET 
                     id_guru_bk = $id_guru_bk
                     WHERE id_sekolah = $id_sekolah AND kelas = '$kelas'";
        } else {
            // Insert new record
            $query = "INSERT INTO guru_bk_kelas 
                     (id_sekolah, id_guru_bk, kelas) 
                     VALUES 
                     ($id_sekolah, $id_guru_bk, '$kelas')";
        }
        
        if(mysqli_query($conn, $query)){
            header("Location: ../../frontend/sekolah/bk.php?success=1");
        } else {
            header("Location: ../../frontend/sekolah/bk.php?error=1");
        }
    } else {
        header("Location: ../../frontend/sekolah/bk.php?error=Data+tidak+lengkap");
    }
    exit;
}
?>

