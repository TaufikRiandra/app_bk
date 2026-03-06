<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// PERBAIKAN KRITIS: $action harus didefinisikan SEBELUM dipakai
// Sebelumnya $action dipakai di baris 11 tapi baru didefinisikan di baris 28
$action = $_POST['action'] ?? 'save';

if ($action === 'set_default_guru') {
    $id_guru_bk = intval($_POST['id_guru_bk'] ?? 0);
    $tanggal    = $_POST['tanggal'] ?? date('Y-m-d');

    if ($id_guru_bk > 0) {
        if (!isset($_SESSION['mediasi_assigned_guru'])) {
            $_SESSION['mediasi_assigned_guru'] = [];
        }
        $_SESSION['mediasi_assigned_guru'][$tanggal] = $id_guru_bk;
        echo json_encode(['success' => true, 'message' => 'Guru BK berhasil ditetapkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID guru BK tidak valid']);
    }
    exit;
}

if ($action === 'save_batch') {
    // Handle deleted records first
    $deleted_ids = json_decode($_POST['deleted_ids'] ?? '[]', true);

    if (!empty($deleted_ids)) {
        foreach ($deleted_ids as $id) {
            $id = intval($id);
            if ($id > 0) {
                mysqli_query($conn, "DELETE FROM layanan_mediasi WHERE id_mediasi = $id");
            }
        }
    }

    // Batch save multiple mediasi records
    $data = json_decode($_POST['data'] ?? '[]', true);

    if (empty($data)) {
        echo json_encode(['success' => false, 'error' => 'Tidak ada data untuk disimpan']);
        exit;
    }

    $success = 0;
    $failed  = 0;

    foreach ($data as $item) {
        $tanggal         = mysqli_real_escape_string($conn, $item['tanggal']         ?? '');
        $id_guru_bk      = intval($item['id_guru_bk'] ?? 0);
        $nama_pihak_1    = mysqli_real_escape_string($conn, $item['nama_pihak_1']    ?? '');
        $kelas_pihak_1   = mysqli_real_escape_string($conn, $item['kelas_pihak_1']   ?? '');
        $masalah_pihak_1 = mysqli_real_escape_string($conn, $item['masalah_pihak_1'] ?? '');
        $nama_pihak_2    = mysqli_real_escape_string($conn, $item['nama_pihak_2']    ?? '');
        $kelas_pihak_2   = mysqli_real_escape_string($conn, $item['kelas_pihak_2']   ?? '');
        $masalah_pihak_2 = mysqli_real_escape_string($conn, $item['masalah_pihak_2'] ?? '');
        $hasil_mediasi   = mysqli_real_escape_string($conn, $item['hasil_mediasi']   ?? '');
        $keterangan      = mysqli_real_escape_string($conn, $item['keterangan']      ?? '');
        $foto            = mysqli_real_escape_string($conn, $item['dokumentasi']     ?? $item['foto'] ?? '');

        if (!$tanggal || !$id_guru_bk || !$nama_pihak_1 || !$nama_pihak_2) {
            $failed++;
            continue;
        }

        if (isset($item['id_mediasi']) && $item['id_mediasi'] > 0) {
            $query = "UPDATE layanan_mediasi SET
                      tanggal          = '$tanggal',
                      id_guru_bk       = $id_guru_bk,
                      nama_pihak_1     = '$nama_pihak_1',
                      kelas_pihak_1    = '$kelas_pihak_1',
                      masalah_pihak_1  = '$masalah_pihak_1',
                      nama_pihak_2     = '$nama_pihak_2',
                      kelas_pihak_2    = '$kelas_pihak_2',
                      masalah_pihak_2  = '$masalah_pihak_2',
                      hasil_mediasi    = '$hasil_mediasi',
                      keterangan       = '$keterangan',
                      foto             = '$foto'
                      WHERE id_mediasi = " . intval($item['id_mediasi']);
        } else {
            $query = "INSERT INTO layanan_mediasi
                      (tanggal, id_guru_bk, nama_pihak_1, kelas_pihak_1, masalah_pihak_1,
                       nama_pihak_2, kelas_pihak_2, masalah_pihak_2, hasil_mediasi, keterangan, foto)
                      VALUES
                      ('$tanggal', $id_guru_bk, '$nama_pihak_1', '$kelas_pihak_1', '$masalah_pihak_1',
                       '$nama_pihak_2', '$kelas_pihak_2', '$masalah_pihak_2', '$hasil_mediasi', '$keterangan', '$foto')";
        }

        if (mysqli_query($conn, $query)) {
            $success++;
        } else {
            $failed++;
        }
    }

    echo json_encode(['success' => true, 'message' => "Berhasil: $success, Gagal: $failed"]);
    exit;
}

if ($action === 'save') {
    $id_mediasi      = intval($_POST['id_mediasi'] ?? 0);
    $tanggal         = mysqli_real_escape_string($conn, $_POST['tanggal']         ?? '');
    $id_guru_bk      = intval($_POST['id_guru_bk'] ?? 0);
    $nama_pihak_1    = mysqli_real_escape_string($conn, $_POST['nama_pihak_1']    ?? '');
    $kelas_pihak_1   = mysqli_real_escape_string($conn, $_POST['kelas_pihak_1']   ?? '');
    $masalah_pihak_1 = mysqli_real_escape_string($conn, $_POST['masalah_pihak_1'] ?? '');
    $nama_pihak_2    = mysqli_real_escape_string($conn, $_POST['nama_pihak_2']    ?? '');
    $kelas_pihak_2   = mysqli_real_escape_string($conn, $_POST['kelas_pihak_2']   ?? '');
    $masalah_pihak_2 = mysqli_real_escape_string($conn, $_POST['masalah_pihak_2'] ?? '');
    $hasil_mediasi   = mysqli_real_escape_string($conn, $_POST['hasil_mediasi']   ?? '');
    $keterangan      = mysqli_real_escape_string($conn, $_POST['keterangan']      ?? '');
    $foto            = mysqli_real_escape_string($conn, $_POST['foto']            ?? '');

    if (!$tanggal || !$id_guru_bk) {
        echo json_encode(['success' => false, 'error' => 'Tanggal dan Guru BK harus diisi']);
        exit;
    }

    if ($id_mediasi > 0) {
        $query = "UPDATE layanan_mediasi SET
                  tanggal          = '$tanggal',
                  id_guru_bk       = $id_guru_bk,
                  nama_pihak_1     = '$nama_pihak_1',
                  kelas_pihak_1    = '$kelas_pihak_1',
                  masalah_pihak_1  = '$masalah_pihak_1',
                  nama_pihak_2     = '$nama_pihak_2',
                  kelas_pihak_2    = '$kelas_pihak_2',
                  masalah_pihak_2  = '$masalah_pihak_2',
                  hasil_mediasi    = '$hasil_mediasi',
                  keterangan       = '$keterangan',
                  foto             = '$foto'
                  WHERE id_mediasi = $id_mediasi";
    } else {
        $query = "INSERT INTO layanan_mediasi
                  (tanggal, id_guru_bk, nama_pihak_1, kelas_pihak_1, masalah_pihak_1,
                   nama_pihak_2, kelas_pihak_2, masalah_pihak_2, hasil_mediasi, keterangan, foto)
                  VALUES
                  ('$tanggal', $id_guru_bk, '$nama_pihak_1', '$kelas_pihak_1', '$masalah_pihak_1',
                   '$nama_pihak_2', '$kelas_pihak_2', '$masalah_pihak_2', '$hasil_mediasi', '$keterangan', '$foto')";
    }

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }

} elseif ($action === 'delete') {
    $id_mediasi = intval($_POST['id_mediasi'] ?? 0);

    if (!$id_mediasi) {
        echo json_encode(['success' => false, 'error' => 'ID mediasi tidak valid']);
        exit;
    }

    if (mysqli_query($conn, "DELETE FROM layanan_mediasi WHERE id_mediasi = $id_mediasi")) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Action tidak dikenali']);
}
