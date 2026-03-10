<?php
/**
 * Database Setup untuk Feature Upload Dokumentasi Mediasi
 * Script ini akan:
 * 1. Check apakah field 'foto' ada di table layanan_mediasi
 * 2. Jika tidak, membuat field tersebut
 * 3. Create folder uploadsuntuk menyimpan file
 */

session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

include '../config/database.php';

$message = '';
$error = '';
$success = false;

if($_POST['action'] === 'setup'){
    // 1. Check and add 'foto' column
    $check_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME='layanan_mediasi' 
                    AND COLUMN_NAME='foto' 
                    AND TABLE_SCHEMA=DATABASE()";
    
    $result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($result) == 0){
        // Column doesn't exist, create it
        $alter_query = "ALTER TABLE `layanan_mediasi` 
                       ADD COLUMN `foto` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL 
                       AFTER `keterangan`";
        
        if(mysqli_query($conn, $alter_query)){
            $message .= "✅ Kolom 'foto' berhasil ditambahkan ke database\n";
        } else {
            $error .= "❌ Gagal menambahkan kolom 'foto': " . mysqli_error($conn) . "\n";
        }
    } else {
        $message .= "✅ Kolom 'foto' sudah ada di database\n";
    }
    
    // 2. Create upload directory
    $upload_dir = '../../frontend/assets/uploads/mediasi';
    if(!is_dir($upload_dir)){
        if(mkdir($upload_dir, 0755, true)){
            $message .= "✅ Folder upload berhasil dibuat di: " . $upload_dir . "\n";
        } else {
            $error .= "❌ Gagal membuat folder upload\n";
        }
    } else {
        $message .= "✅ Folder upload sudah ada di: " . $upload_dir . "\n";
    }
    
    // 3. Check file permissions
    if(is_writable($upload_dir)){
        $message .= "✅ Folder upload memiliki permission untuk write\n";
    } else {
        $error .= "⚠️  Folder upload tidak memiliki permission untuk write\n";
    }
    
    // Check if backend pages exist
    $required_files = [
        '../../frontend/assets/uploads/mediasi' => 'Upload Folder',
        '../pages/upload_file.php' => 'Upload Handler',
        '../pages/save_mediasi.php' => 'Save Handler',
        '../pages/get_mediasi.php' => 'Get Handler'
    ];
    
    $message .= "\n📋 Status File:\n";
    foreach($required_files as $path => $label){
        if(file_exists($path)){
            $message .= "✅ $label: OK\n";
        } else {
            $error .= "❌ $label: TIDAK DITEMUKAN - $path\n";
        }
    }
    
    $success = empty($error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Database - Upload Mediasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #4472C4;
            text-align: center;
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .button {
            background-color: #4472C4;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin: 20px auto;
        }
        .button:hover {
            background-color: #3a5fa0;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #0c5460;
        }
        a {
            color: #4472C4;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Setup Database - Upload Mediasi</h1>
        
        <div class="info">
            Halaman ini akan setup database dan folder untuk fitur upload dokumentasi mediasi.
            Pastikan Anda adalah admin sebelum menjalankan setup ini.
        </div>

        <?php if($message): ?>
            <div class="status <?= $success ? 'success' : 'error' ?>">
<?= htmlspecialchars($message) ?>
<?= $error ? "\n" . htmlspecialchars($error) : '' ?>
            </div>
        <?php endif; ?>

        <?php if($success && $_POST['action'] === 'setup'): ?>
            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #28a745; font-weight: bold; font-size: 18px;">✅ Setup Berhasil!</p>
                <p>Database dan folder sudah siap untuk upload dokumentasi mediasi.</p>
                <a href="../pages/layanan_mediasi_manual.php" class="button">← Kembali ke Mediasi Manual</a>
            </div>
        <?php else: ?>
            <form method="POST" style="text-align: center;">
                <input type="hidden" name="action" value="setup">
                <button type="submit" class="button">🚀 Jalankan Setup</button>
            </form>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 13px; color: #666;">
                <h3>Apa yang akan dilakukan:</h3>
                <ul>
                    <li>✅ Menambahkan kolom 'foto' ke table layanan_mediasi (jika belum ada)</li>
                    <li>✅ Membuat folder upload untuk dokumen mediasi</li>
                    <li>✅ Mengecek permission folder</li>
                    <li>✅ Memverifikasi file-file yang diperlukan</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
