<?php
header('Content-Type: application/json; charset=utf-8');
ob_clean();

session_start();
include '../config/database.php';

// Check authorization
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['role'] ?? 'guru_bk';
$mode = $_POST['mode'] ?? 'save_kegiatan';

// For set_guru_bk mode: only admin can do this
if ($mode === 'set_guru_bk' && $user_role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Hanya admin yang bisa menetapkan guru BK']);
    exit();
}

// For save_kegiatan mode: both admin and guru_bk can do this
if ($mode === 'save_kegiatan' && $user_role === 'guru_bk') {
    // Guru_bk saving kegiatan is allowed
} else if ($mode === 'save_kegiatan' && $user_role === 'admin') {
    // Admin saving kegiatan is allowed
} else if ($mode !== 'set_guru_bk') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses']);
    exit();
}

$tanggal = $_POST['tanggal'] ?? '';
$id_guru_bk = intval($_POST['id_guru_bk'] ?? 0);
$kegiatan_json = $_POST['kegiatan_json'] ?? '[]';

// Validasi tanggal
if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit();
}

// Handle set_guru_bk mode (admin only)
if ($mode === 'set_guru_bk') {
    if ($id_guru_bk <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID Guru BK tidak valid']);
        exit();
    }
    
    // Just verify guru_bk exists and return success
    $query = "SELECT nama FROM guru_bk WHERE id_guru_bk = $id_guru_bk";
    $result = mysqli_query($conn, $query);
    if (!$result || !($row = mysqli_fetch_assoc($result))) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guru BK tidak ditemukan']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Guru BK ditetapkan bertugas',
        'guru_bk_name' => $row['nama']
    ]);
    exit();
}

// Handle save_kegiatan mode (both admin and guru_bk)
if ($mode === 'save_kegiatan') {
    // For guru_bk, get id_guru_bk from first guru_bk record (simplified)
    if ($user_role === 'guru_bk') {
        $query = "SELECT id_guru_bk FROM guru_bk LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && ($row = mysqli_fetch_assoc($result))) {
            $id_guru_bk = $row['id_guru_bk'];
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Guru BK tidak ditemukan']);
            exit();
        }
    }
    
    if ($id_guru_bk <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID Guru BK tidak valid']);
        exit();
    }
}

// Parse kegiatan JSON
$kegiatan_data = json_decode($kegiatan_json, true);
if (!is_array($kegiatan_data) || empty($kegiatan_data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data kegiatan tidak valid']);
    exit();
}

try {
    mysqli_begin_transaction($conn);

    $tanggal_escaped = mysqli_real_escape_string($conn, $tanggal);
    
    // Load existing kegiatan for this date and guru
    $existing_kegiatan = [];
    $query = "SELECT id_kegiatan, waktu_mulai, waktu_selesai, uraian_kegiatan, jenis_layanan, sasaran_layanan, bidang_kode_layanan, hasil, keterangan 
              FROM kegiatan_harian 
              WHERE tanggal = '$tanggal_escaped' AND id_guru_bk = $id_guru_bk
              ORDER BY waktu_mulai ASC";
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception('Query error: ' . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $existing_kegiatan[] = $row;
    }

    // Counters
    $updated_count = 0;
    $added_count = 0;
    $deleted_count = 0;

    // Track yang sudah diproses dari kegiatan baru
    $processed_ids = [];

    // Process new kegiatan data
    foreach ($kegiatan_data as $idx => $new_k) {
        $waktu_mulai = trim($new_k['waktu_mulai'] ?? '');
        $waktu_selesai = trim($new_k['waktu_selesai'] ?? '');
        $uraian_kegiatan = trim($new_k['uraian_kegiatan'] ?? '');
        $jenis_layanan = trim($new_k['jenis_layanan'] ?? '');
        $sasaran_layanan = trim($new_k['sasaran_layanan'] ?? '');
        $bidang_kode_layanan = trim($new_k['bidang_kode_layanan'] ?? '');
        $hasil = trim($new_k['hasil'] ?? '');
        $keterangan = trim($new_k['keterangan'] ?? '');
        $id_kegiatan = $new_k['id_kegiatan'] ?? 'new';

        // Minimal harus ada waktu_mulai atau uraian_kegiatan
        if (empty($waktu_mulai) && empty($uraian_kegiatan)) {
            continue;
        }

        if ($id_kegiatan !== 'new') {
            // UPDATE: kegiatan sudah ada di DB
            $id_kegiatan = intval($id_kegiatan);
            $processed_ids[] = $id_kegiatan;

            // Check if this kegiatan exists
            $check_query = "SELECT id_kegiatan FROM kegiatan_harian WHERE id_kegiatan = $id_kegiatan AND id_guru_bk = $id_guru_bk";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update kegiatan
                $waktu_mulai_esc = mysqli_real_escape_string($conn, $waktu_mulai);
                $waktu_selesai_esc = mysqli_real_escape_string($conn, $waktu_selesai);
                $uraian_esc = mysqli_real_escape_string($conn, $uraian_kegiatan);
                $jenis_layanan_esc = mysqli_real_escape_string($conn, $jenis_layanan);
                $sasaran_layanan_esc = mysqli_real_escape_string($conn, $sasaran_layanan);
                $bidang_kode_layanan_esc = mysqli_real_escape_string($conn, $bidang_kode_layanan);
                $hasil_esc = mysqli_real_escape_string($conn, $hasil);
                $keterangan_esc = mysqli_real_escape_string($conn, $keterangan);

                $update_query = "UPDATE kegiatan_harian 
                                SET waktu_mulai = '$waktu_mulai_esc',
                                    waktu_selesai = '$waktu_selesai_esc',
                                    uraian_kegiatan = '$uraian_esc',
                                    jenis_layanan = '$jenis_layanan_esc',
                                    sasaran_layanan = '$sasaran_layanan_esc',
                                    bidang_kode_layanan = '$bidang_kode_layanan_esc',
                                    hasil = '$hasil_esc',
                                    keterangan = '$keterangan_esc'
                                WHERE id_kegiatan = $id_kegiatan";

                if (!mysqli_query($conn, $update_query)) {
                    throw new Exception('Update kegiatan error: ' . mysqli_error($conn));
                }
                $updated_count++;
            }
        } else {
            // INSERT: kegiatan baru
            $waktu_mulai_esc = mysqli_real_escape_string($conn, $waktu_mulai);
            $waktu_selesai_esc = mysqli_real_escape_string($conn, $waktu_selesai);
            $uraian_esc = mysqli_real_escape_string($conn, $uraian_kegiatan);
            $jenis_layanan_esc = mysqli_real_escape_string($conn, $jenis_layanan);
            $sasaran_layanan_esc = mysqli_real_escape_string($conn, $sasaran_layanan);
            $bidang_kode_layanan_esc = mysqli_real_escape_string($conn, $bidang_kode_layanan);
            $hasil_esc = mysqli_real_escape_string($conn, $hasil);
            $keterangan_esc = mysqli_real_escape_string($conn, $keterangan);

            $insert_query = "INSERT INTO kegiatan_harian (tanggal, id_guru_bk, waktu_mulai, waktu_selesai, uraian_kegiatan, jenis_layanan, sasaran_layanan, bidang_kode_layanan, hasil, keterangan)
                            VALUES ('$tanggal_escaped', $id_guru_bk, '$waktu_mulai_esc', '$waktu_selesai_esc', '$uraian_esc', '$jenis_layanan_esc', '$sasaran_layanan_esc', '$bidang_kode_layanan_esc', '$hasil_esc', '$keterangan_esc')";

            if (!mysqli_query($conn, $insert_query)) {
                throw new Exception('Insert kegiatan error: ' . mysqli_error($conn));
            }
            $added_count++;
        }
    }

    // DELETE kegiatan yang tidak ada di form baru
    foreach ($existing_kegiatan as $old_k) {
        if (!in_array($old_k['id_kegiatan'], $processed_ids)) {
            $id_k = intval($old_k['id_kegiatan']);
            $delete_query = "DELETE FROM kegiatan_harian WHERE id_kegiatan = $id_k";
            
            if (!mysqli_query($conn, $delete_query)) {
                throw new Exception('Delete kegiatan error: ' . mysqli_error($conn));
            }
            $deleted_count++;
        }
    }

    mysqli_commit($conn);

    // Build message
    $message = "";
    if ($updated_count > 0) $message .= "$updated_count kegiatan diubah";
    if ($added_count > 0) {
        if ($message) $message .= ", ";
        $message .= "$added_count kegiatan ditambah";
    }
    if ($deleted_count > 0) {
        if ($message) $message .= ", ";
        $message .= "$deleted_count kegiatan dihapus";
    }
    if (!$message) $message = "Tidak ada perubahan";

    echo json_encode([
        'success' => true,
        'message' => $message,
        'updated' => $updated_count,
        'added' => $added_count,
        'deleted' => $deleted_count
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
