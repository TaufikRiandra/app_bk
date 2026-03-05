<?php
header('Content-Type: application/json; charset=utf-8');
ob_clean();
session_start();
include __DIR__ . '/../config/database.php';

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_role  = $_SESSION['role'] ?? 'guru_bk';
$mode       = $_POST['mode'] ?? 'save_kegiatan';
$tanggal    = $_POST['tanggal'] ?? '';
$id_guru_bk = intval($_POST['id_guru_bk'] ?? 0);

if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit;
}

$t = mysqli_real_escape_string($conn, $tanggal);

/* ═══════════════════════════════════════════════
   MODE: set_guru_bk  (admin only)
   Simpan ke tabel guru_bk_jadwal supaya persisten
   ═══════════════════════════════════════════════ */
if ($mode === 'set_guru_bk') {
    if ($user_role !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Hanya admin yang dapat menetapkan guru BK']);
        exit;
    }

    $id_user_admin = intval($_SESSION['id_user'] ?? 0);

    if ($id_guru_bk === 0) {
        // "Tidak Ada" dipilih — simpan NULL
        $q = mysqli_query($conn,
            "INSERT INTO guru_bk_jadwal (tanggal, id_guru_bk, ditetapkan_oleh)
             VALUES ('$t', NULL, $id_user_admin)
             ON DUPLICATE KEY UPDATE id_guru_bk = NULL, ditetapkan_oleh = $id_user_admin"
        );
        if (!$q) { http_response_code(500); echo json_encode(['success'=>false,'message'=>mysqli_error($conn)]); exit; }
        echo json_encode(['success' => true, 'message' => 'Tanggal ini dikosongkan', 'guru_bk_name' => null, 'id_guru_bk' => 0]);
        exit;
    }

    // Verifikasi guru_bk ada
    $r = mysqli_query($conn, "SELECT nama FROM guru_bk WHERE id_guru_bk = $id_guru_bk");
    if (!$r || !($row = mysqli_fetch_assoc($r))) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guru BK tidak ditemukan']);
        exit;
    }
    $nama_guru = $row['nama'];

    $q = mysqli_query($conn,
        "INSERT INTO guru_bk_jadwal (tanggal, id_guru_bk, ditetapkan_oleh)
         VALUES ('$t', $id_guru_bk, $id_user_admin)
         ON DUPLICATE KEY UPDATE id_guru_bk = $id_guru_bk, ditetapkan_oleh = $id_user_admin"
    );
    if (!$q) { http_response_code(500); echo json_encode(['success'=>false,'message'=>mysqli_error($conn)]); exit; }

    echo json_encode([
        'success'      => true,
        'message'      => $nama_guru . ' ditetapkan bertugas',
        'guru_bk_name' => $nama_guru,
        'id_guru_bk'   => $id_guru_bk,
    ]);
    exit;
}

/* ═══════════════════════════════════════════════
   MODE: save_kegiatan
   Hanya guru yang DITETAPKAN untuk tanggal tsb & admin
   ═══════════════════════════════════════════════ */
if ($mode === 'save_kegiatan') {

    if ($user_role === 'guru_bk') {
        $id_guru_bk = intval($_SESSION['id_guru_bk'] ?? 0);
        if (!$id_guru_bk) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Profil guru BK tidak ditemukan di sesi']);
            exit;
        }
        // Cek penetapan
        $jq = mysqli_query($conn, "SELECT id_guru_bk FROM guru_bk_jadwal WHERE tanggal = '$t' LIMIT 1");
        $jadwal = $jq ? mysqli_fetch_assoc($jq) : null;
        if (!$jadwal || intval($jadwal['id_guru_bk']) !== $id_guru_bk) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Anda tidak ditetapkan bertugas untuk tanggal ini. Hubungi admin.']);
            exit;
        }

    } elseif ($user_role === 'admin') {
        if ($id_guru_bk <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Pilih Guru BK terlebih dahulu']);
            exit;
        }
    } else {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
        exit;
    }

    $kegiatan_json = $_POST['kegiatan_json'] ?? '[]';
    $kegiatan_data = json_decode($kegiatan_json, true);
    if (!is_array($kegiatan_data) || empty($kegiatan_data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data kegiatan tidak valid atau kosong']);
        exit;
    }

    try {
        mysqli_begin_transaction($conn);

        $existing = [];
        $eq = mysqli_query($conn,
            "SELECT id_kegiatan FROM kegiatan_harian WHERE tanggal='$t' AND id_guru_bk=$id_guru_bk"
        );
        while ($row = mysqli_fetch_assoc($eq)) $existing[] = intval($row['id_kegiatan']);

        $added = $updated = $deleted = 0;
        $processed_ids = [];

        foreach ($kegiatan_data as $k) {
            $wm  = mysqli_real_escape_string($conn, trim($k['waktu_mulai']         ?? ''));
            $ws  = mysqli_real_escape_string($conn, trim($k['waktu_selesai']       ?? ''));
            $ur  = mysqli_real_escape_string($conn, trim($k['uraian_kegiatan']     ?? ''));
            $jl  = mysqli_real_escape_string($conn, trim($k['jenis_layanan']       ?? ''));
            $sl  = mysqli_real_escape_string($conn, trim($k['sasaran_layanan']     ?? ''));
            $bl  = mysqli_real_escape_string($conn, trim($k['bidang_kode_layanan'] ?? ''));
            $hs  = mysqli_real_escape_string($conn, trim($k['hasil']               ?? ''));
            $ket = mysqli_real_escape_string($conn, trim($k['keterangan']          ?? ''));
            $id_k = $k['id_kegiatan'] ?? 'new';

            if (empty($wm) && empty($ur)) continue;

            if ($id_k !== 'new') {
                $id_k = intval($id_k);
                $processed_ids[] = $id_k;
                $chk = mysqli_query($conn,
                    "SELECT id_kegiatan FROM kegiatan_harian WHERE id_kegiatan=$id_k AND id_guru_bk=$id_guru_bk"
                );
                if (mysqli_num_rows($chk) > 0) {
                    if (!mysqli_query($conn,
                        "UPDATE kegiatan_harian SET
                            waktu_mulai='$wm', waktu_selesai='$ws', uraian_kegiatan='$ur',
                            jenis_layanan='$jl', sasaran_layanan='$sl', bidang_kode_layanan='$bl',
                            hasil='$hs', keterangan='$ket'
                         WHERE id_kegiatan=$id_k"
                    )) throw new Exception(mysqli_error($conn));
                    $updated++;
                }
            } else {
                if (!mysqli_query($conn,
                    "INSERT INTO kegiatan_harian
                        (tanggal, id_guru_bk, waktu_mulai, waktu_selesai, uraian_kegiatan,
                         jenis_layanan, sasaran_layanan, bidang_kode_layanan, hasil, keterangan)
                     VALUES ('$t', $id_guru_bk, '$wm', '$ws', '$ur', '$jl', '$sl', '$bl', '$hs', '$ket')"
                )) throw new Exception(mysqli_error($conn));
                $added++;
            }
        }

        foreach ($existing as $eid) {
            if (!in_array($eid, $processed_ids)) {
                if (!mysqli_query($conn, "DELETE FROM kegiatan_harian WHERE id_kegiatan=$eid"))
                    throw new Exception(mysqli_error($conn));
                $deleted++;
            }
        }

        mysqli_commit($conn);

        $parts = [];
        if ($updated) $parts[] = "$updated diubah";
        if ($added)   $parts[] = "$added ditambah";
        if ($deleted) $parts[] = "$deleted dihapus";

        echo json_encode(['success' => true, 'message' => $parts ? implode(', ', $parts) : 'Tidak ada perubahan']);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Mode tidak dikenali']);
