<?php
/**
 * Database Setup Helper
 * Checks dan auto-fix database requirements untuk fitur upload mediasi
 */

class MediasiSetupHelper {
    private $conn;
    private $errors = [];
    private $warnings = [];
    private $success = [];
    
    public function __construct($mysqli_conn) {
        $this->conn = $mysqli_conn;
    }
    
    /**
     * Check if column exists in table
     */
    public function hasColumn($table, $column) {
        $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_NAME='$table' 
                 AND COLUMN_NAME='$column' 
                 AND TABLE_SCHEMA=DATABASE()";
        $result = mysqli_query($this->conn, $query);
        return mysqli_num_rows($result) > 0;
    }
    
    /**
     * Check all requirements
     */
    public function checkAll() {
        // Check database column
        if (!$this->hasColumn('layanan_mediasi', 'foto')) {
            $this->warnings[] = "Kolom 'foto' belum ada di database. Silakan jalankan setup.";
        } else {
            $this->success[] = "Database column 'foto' OK";
        }
        
        // Check upload folder
        $upload_dir = dirname(__DIR__) . '/frontend/assets/uploads/mediasi';
        if (!is_dir($upload_dir)) {
            $this->warnings[] = "Folder upload mediasi belum ada.";
        } else {
            $this->success[] = "Upload folder OK";
            
            if (!is_writable($upload_dir)) {
                $this->warnings[] = "Folder upload tidak writable. Permission issue.";
            }
        }
        
        // Check required files
        $required_files = [
            'backend/pages/upload_file.php',
            'backend/pages/save_mediasi.php',
            'backend/pages/get_mediasi.php',
        ];
        
        foreach ($required_files as $file) {
            $path = dirname(__DIR__) . '/' . $file;
            if (!file_exists($path)) {
                $this->errors[] = "File tidak ditemukan: $file";
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Try to auto-fix common issues
     */
    public function autoFix() {
        $fixed = false;
        
        // Try to create folder
        $upload_dir = dirname(__DIR__) . '/frontend/assets/uploads/mediasi';
        if (!is_dir($upload_dir)) {
            if (@mkdir($upload_dir, 0755, true)) {
                $this->success[] = "Folder upload berhasil dibuat";
                $fixed = true;
            }
        }
        
        return $fixed;
    }
    
    /**
     * Get setup status
     */
    public function getStatus() {
        return [
            'ok' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'success' => $this->success
        ];
    }
    
    /**
     * Get HTML alert if there are issues
     */
    public function getAlertHtml() {
        $this->checkAll();
        $status = $this->getStatus();
        
        if (empty($this->errors) && empty($this->warnings)) {
            return '';
        }
        
        $html = '<div class="setup-alert">';
        $html .= '<p class="setup-alert-title">⚠️ Setup Required</p>';
        
        if (!empty($this->errors)) {
            $html .= '<p class="setup-alert-label setup-alert-error-label">Errors:</p>';
            foreach ($this->errors as $error) {
                $html .= '<p class="setup-alert-error">❌ ' . htmlspecialchars($error) . '</p>';
            }
        }
        
        if (!empty($this->warnings)) {
            if (!empty($this->errors)) $html .= '<hr class="setup-alert-divider">';
            $html .= '<p class="setup-alert-label setup-alert-warning-label">Warnings:</p>';
            foreach ($this->warnings as $warning) {
                $html .= '<p class="setup-alert-warning">⚠️ ' . htmlspecialchars($warning) . '</p>';
            }
        }
        
        $html .= '<p class="setup-alert-footer">';
        $html .= 'Silakan jalankan <a href="../../backend/setup/setup_mediasi_upload.php" class="setup-alert-link">Setup Database</a> untuk menyelesaikan konfigurasi.';
        $html .= '</p>';
        $html .= '</div>';
        
        return $html;
    }
}

// Function helper untuk include
function checkMediasiSetup($conn) {
    $helper = new MediasiSetupHelper($conn);
    $helper->checkAll();
    $helper->autoFix();
    return $helper;
}
?>
