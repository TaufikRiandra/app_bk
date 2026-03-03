<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['login'])){
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Validate file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'File upload error']);
    exit;
}

$file = $_FILES['file'];
$upload_dir = $_POST['upload_dir'] ?? 'uploads';

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'File type not allowed']);
    exit;
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'error' => 'File too large']);
    exit;
}

// Create upload directory if it doesn't exist
$upload_path = '../../frontend/assets/uploads/' . $upload_dir;
if (!is_dir($upload_path)) {
    mkdir($upload_path, 0755, true);
}

// Generate unique filename
$file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $file_ext;
$filepath = $upload_path . '/' . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'path' => $filepath
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
}
?>
