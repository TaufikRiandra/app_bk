<?php
ob_clean(); // Clear any output buffer
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

include "../config/database.php";

// Check if this is POST request
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
	exit;
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);

if(!isset($input['kelas']) || !isset($input['siswa'])) {
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
	exit;
}

$kelas = mysqli_real_escape_string($conn, $input['kelas']);
$siswa_list = $input['siswa'];

try {
	// Start transaction
	mysqli_begin_transaction($conn);

	// Load existing siswa data from database with position mapping
	$existing_siswa = [];
	$existing_query = "SELECT id_siswa, nama_siswa FROM siswa WHERE kelas = '$kelas' ORDER BY id_siswa ASC LIMIT 30";
	$existing_result = mysqli_query($conn, $existing_query);
	if($existing_result && mysqli_num_rows($existing_result) > 0) {
		$position = 1;
		while($row = mysqli_fetch_assoc($existing_result)) {
			$existing_siswa[$position] = [
				'id_siswa' => $row['id_siswa'],
				'nama_siswa' => $row['nama_siswa']
			];
			$position++;
		}
	}

	$is_update = count($existing_siswa) > 0;
	$updated_count = 0;
	$added_count = 0;
	$deleted_count = 0;

	// Process changes based on comparison
	for($i = 1; $i <= 30; $i++) {
		// Get new nama from form
		$new_nama = '';
		foreach($siswa_list as $siswa) {
			if($siswa['no'] == $i) {
				$new_nama = trim($siswa['nama']);
				break;
			}
		}

		// Get existing nama from database
		$old_nama = isset($existing_siswa[$i]) ? $existing_siswa[$i]['nama_siswa'] : '';
		$old_id = isset($existing_siswa[$i]) ? $existing_siswa[$i]['id_siswa'] : null;

		// Compare and process
		if(!empty($old_nama) && empty($new_nama)) {
			// DELETE: siswa yang sebelumnya ada, sekarang kosong
			$delete_absen_query = "DELETE FROM absen_siswa WHERE id_siswa = $old_id";
			if(!mysqli_query($conn, $delete_absen_query)) {
				throw new Exception('Error deleting absen data: ' . mysqli_error($conn));
			}
			
			$delete_siswa_query = "DELETE FROM siswa WHERE id_siswa = $old_id";
			if(!mysqli_query($conn, $delete_siswa_query)) {
				throw new Exception('Error deleting siswa data: ' . mysqli_error($conn));
			}
			$deleted_count++;

		} else if(!empty($old_nama) && !empty($new_nama) && $old_nama !== $new_nama) {
			// UPDATE: nama berbeda
			$new_nama_escaped = mysqli_real_escape_string($conn, $new_nama);
			$update_query = "UPDATE siswa SET nama_siswa = '$new_nama_escaped' WHERE id_siswa = $old_id";
			if(!mysqli_query($conn, $update_query)) {
				throw new Exception('Error updating siswa: ' . mysqli_error($conn));
			}
			$updated_count++;

		} else if(empty($old_nama) && !empty($new_nama)) {
			// INSERT: siswa baru
			$new_nama_escaped = mysqli_real_escape_string($conn, $new_nama);
			$nis = $kelas . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
			$insert_query = "INSERT INTO siswa (nis, nama_siswa, kelas) VALUES ('$nis', '$new_nama_escaped', '$kelas')";
			if(!mysqli_query($conn, $insert_query)) {
				throw new Exception('Error inserting siswa: ' . mysqli_error($conn));
			}
			$added_count++;
		}
		// Else: no change needed
	}

	// Commit transaction
	mysqli_commit($conn);

	$summary = [];
	if($updated_count > 0) $summary[] = "$updated_count nama diubah";
	if($added_count > 0) $summary[] = "$added_count siswa ditambah";
	if($deleted_count > 0) $summary[] = "$deleted_count siswa dihapus";

	$message = $is_update 
		? '✅ Data siswa kelas ' . $kelas . ' berhasil diperbarui! (' . implode(', ', $summary) . ')'
		: '✅ Data siswa kelas ' . $kelas . ' berhasil disimpan! (' . $added_count . ' siswa)';

	echo json_encode([
		'status' => 'success',
		'message' => $message
	]);

} catch(Exception $e) {
	// Rollback on error
	mysqli_rollback($conn);
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>
