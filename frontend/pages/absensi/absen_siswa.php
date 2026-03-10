<?php
// Setup role dan parameter yang dipilih
$role        = $_SESSION['role'] ?? 'guest';
$id_guru_bk  = $_SESSION['id_guru_bk'] ?? null;

// Ambil tanggal penuh dari picker (format: YYYY-MM-DD)
$tanggal_full    = isset($_GET['tanggal_full']) ? htmlspecialchars($_GET['tanggal_full']) : date('Y-m-d');
$kelas_terpilih  = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : null;

// Pecah tanggal untuk keperluan display di header tabel
$parts           = explode('-', $tanggal_full);
$tahun_terpilih  = $parts[0];
$bulan_terpilih  = $parts[1];
$tanggal_terpilih = $parts[2];

// Get database connection from global
$conn = $GLOBALS['conn'] ?? null;
if (!$conn) {
    include "../../../backend/config/database.php";
}

// Include auth helper
require_once "../../../backend/config/auth_helper.php";

// Ambil kelas yang boleh diakses oleh user yang login
$kelas_diizinkan = getKelasForCurrentUser($conn);
$kelas_diizinkan = array_values(array_unique(getKelasForCurrentUser($conn)));

// Jika guru_bk memilih kelas yang bukan haknya → reset
if ($kelas_terpilih && !isAdmin()) {
    if (!in_array($kelas_terpilih, $kelas_diizinkan)) {
        $kelas_terpilih = null;
    }
}

// Kelompokkan kelas berdasarkan tingkat
$kelas_per_tingkat = [];
foreach ($kelas_diizinkan as $k) {
    preg_match('/^(\d+)/', $k, $m);
    $tingkat = $m[1] ?? '?';
    $kelas_per_tingkat[$tingkat][] = $k;
}
ksort($kelas_per_tingkat);
?>

<div style="display:grid;grid-template-columns:280px 1fr;gap:2rem;align-items:start">
    
    <div>
        <div style="position:relative;top:0rem;background:var(--bg-secondary);padding:1.5rem;border-radius:8px;border:1px solid var(--border);margin-bottom:1.5rem">
            <h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Pilih Tanggal</h3>
            
            <div style="margin-bottom:1rem">
                <label style="display:block;font-size:0.85rem;color:var(--text-light);margin-bottom:0.5rem">Pilih Hari</label>
                <input type="date" id="datePicker" 
                       value="<?= $tanggal_full ?>" 
                       onchange="updateDateParams()" 
                       style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem;font-family:inherit;box-sizing:border-box">
            </div>
        </div>

        <div style="position:sticky;top:12rem;background:var(--bg-secondary);padding:1.5rem;border-radius:8px;border:1px solid var(--border)">
            <h3 style="margin-top:0;margin-bottom:0.5rem;color:var(--brand);font-size:1rem">Pilih Kelas</h3>

            <?php if (!isAdmin()): ?>
            <p style="margin:0 0 1rem 0;font-size:0.78rem;color:var(--text-light);background:#f0f4ff;padding:0.5rem 0.75rem;border-radius:5px;border-left:3px solid var(--brand)">
                <i class="fas fa-lock"></i> Kelas yang ditetapkan untuk Anda
            </p>
            <?php endif; ?>
            
            <?php if (empty($kelas_diizinkan)): ?>
                <p style="color:#dc3545;font-size:0.85rem;text-align:center;padding:1rem">
                    <i class="fas fa-exclamation-circle"></i><br>
                    Belum ada kelas yang ditetapkan.<br>Hubungi admin.
                </p>
            <?php else: ?>
                <?php foreach ($kelas_per_tingkat as $tingkat => $daftar_kelas): ?>
                    <div style="margin-bottom:1.5rem">
                        <h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas <?= $tingkat ?></h5>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem">
                            <?php foreach ($daftar_kelas as $k):
                                $is_selected = ($kelas_terpilih === $k);
                                $btn_style   = $is_selected
                                    ? 'background:#E8E4FF;color:var(--brand);border:1px solid var(--brand)'
                                    : 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
                            ?>
                                <button type="button" onclick="loadKelasAbsen('<?= $k ?>')"
                                        style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;cursor:pointer;font-size:0.9rem"
                                        title="Kelas <?= $k ?>">
                                    <?= $k ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <?php if (!$tanggal_full || !$kelas_terpilih): ?>
            <div style="background:var(--surface);border:2px dashed var(--border);border-radius:12px;padding:3rem;text-align:center">
                <div style="font-size:2.5rem;margin-bottom:1rem;color:var(--text-light)"><i class="fas fa-calendar-check"></i></div>
                <p style="color:var(--text-light);margin:0;font-size:1.1rem">Silakan pilih tanggal dan kelas</p>
                <p style="color:var(--text-light);margin:0.5rem 0 0 0;font-size:0.95rem">untuk mulai mengisi absensi siswa</p>
            </div>
        <?php else: ?>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden">
                <div style="background:var(--brand);color:black;padding:1.2rem;text-align:center">
                    <h3 style="margin:0;font-size:1.2rem">Absen Kelas <?= htmlspecialchars($kelas_terpilih) ?></h3>
                    <p style="margin:0.5rem 0 0 0;font-size:0.95rem">
                        <i class="far fa-calendar-alt"></i> <?= date('d F Y', strtotime($tanggal_full)) ?>
                    </p>
                </div>

                <div style="background:var(--bg-light);padding:1rem;border-bottom:1px solid var(--border)">
                    <p style="margin:0;font-size:0.9rem;color:var(--text-dark);text-align:center">
                        <strong>Ket:</strong> 
                        <span style="margin-left:0.8rem">H : Hadir</span>
                        <span style="margin-left:0.8rem">I : Izin</span>
                        <span style="margin-left:0.8rem">S : Sakit</span>
                        <span style="margin-left:0.8rem">A : Alfa</span>
                        <span style="margin-left:0.8rem">C : Cabut</span>
                        <span style="margin-left:0.8rem">T : Telat</span>
                    </p>
                </div>

                <div style="background:white;padding:1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:1rem">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;margin:0;flex:1">
                        <input type="checkbox" id="checkHadirSemua" onchange="toggleHadirSemua()" style="width:18px;height:18px;cursor:pointer">
                        <i class="fas fa-check-circle" style="color:var(--success)"></i> Tandai Hadir Semua
                    </label>
                </div>

                <?php
                    $kelas_escaped = mysqli_real_escape_string($conn, $kelas_terpilih);
                    $siswa_query   = mysqli_query($conn,
                        "SELECT id_siswa, nis, nama_siswa FROM siswa
                         WHERE kelas = '$kelas_escaped'
                         ORDER BY CAST(SUBSTRING_INDEX(nis, '-', -1) AS UNSIGNED) ASC");
                    $siswa_list = [];
                    while ($row = mysqli_fetch_assoc($siswa_query)) { $siswa_list[] = $row; }

                    $absen_existing = [];
                    if (!empty($siswa_list)) {
                        $id_siswa_list = array_column($siswa_list, 'id_siswa');
                        $ids_str       = implode(',', $id_siswa_list);
                        $absen_query   = mysqli_query($conn,
                            "SELECT id_siswa, keterangan FROM absen_siswa
                             WHERE id_siswa IN ($ids_str) AND tanggal = '$tanggal_full'");
                        while ($row = mysqli_fetch_assoc($absen_query)) {
                            $absen_existing[$row['id_siswa']] = $row['keterangan'];
                        }
                    }
                    $keterangan_to_kode = [
                        'Hadir' => 'H', 'Izin' => 'I', 'Sakit' => 'S',
                        'Alfa' => 'A', 'Cabut' => 'C', 'Terlambat' => 'T',
                    ];
                    foreach ($absen_existing as $id => $ket) {
                        $absen_existing[$id] = $keterangan_to_kode[$ket] ?? $ket;
                    }
                    $has_existing_absen = !empty($absen_existing);
                ?>

                <?php if (!empty($siswa_list)): ?>
                    <div style="max-height:600px;overflow-y:auto;padding:1rem">
                        <table style="width:100%;border-collapse:collapse">
                            <tbody>
                                <?php foreach ($siswa_list as $index => $siswa):
                                    $nomor = intval(explode('-', $siswa['nis'])[1] ?? ($index + 1));
                                ?>
                                    <tr style="border-bottom:1px solid var(--border)">
                                        <td style="padding:0.75rem 0.5rem;width:40px;text-align:center;font-weight:600;color:var(--text-light)">
                                            <?= $nomor ?>
                                        </td>
                                        <td style="padding:0.75rem 0.5rem">
                                            <div style="margin-bottom:0.3rem;font-weight:500;color:var(--text-dark)"><?= htmlspecialchars($siswa['nama_siswa']) ?></div>
                                            <div style="display:flex;gap:0.8rem;flex-wrap:wrap">
                                                <?php foreach (['H','I','S','A','C','T'] as $st):
                                                    $is_checked = isset($absen_existing[$siswa['id_siswa']]) && $absen_existing[$siswa['id_siswa']] === $st; ?>
                                                <label style="display:flex;align-items:center;gap:0.2rem;cursor:pointer;font-size:0.85rem">
                                                    <input type="radio"
                                                        name="absen_siswa_<?= $siswa['id_siswa'] ?>"
                                                        class="radio-absen"
                                                        data-siswa-id="<?= $siswa['id_siswa'] ?>"
                                                        value="<?= $st ?>"
                                                        <?= $is_checked ? 'checked' : '' ?>> <?= $st ?>
                                                </label>
                                                <?php endforeach; ?>
                                                <button onclick="resetAbsenRadio(this)" style="margin-left:auto;padding:2px 8px;background:none;border:1px solid var(--border);border-radius:4px;cursor:pointer;font-size:0.7rem;color:var(--text-light)">Reset</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="background:var(--bg-light);padding:1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
                        <?php if ($has_existing_absen): ?>
                        <div style="font-size:0.85rem;color:#854d0e;background:#fef9c3;border:1px solid #fde047;padding:0.5rem 0.875rem;border-radius:6px;display:flex;align-items:center;gap:0.5rem">
                            <i class="fas fa-info-circle"></i>
                            Data absen sudah ada — perubahan akan di-update
                        </div>
                        <?php else: ?>
                        <div></div>
                        <?php endif; ?>

                        <?php if ($has_existing_absen): ?>
                        <button onclick="simpanAbsen()"
                            style="padding:0.75rem 2rem;background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);color:black;border:none;border-radius:6px;cursor:pointer;font-weight:600;box-shadow:0 4px 12px rgba(245,158,11,0.3);display:flex;align-items:center;gap:0.5rem">
                            <i class="fas fa-sync-alt"></i> Update Absensi
                        </button>
                        <?php else: ?>
                        <button onclick="simpanAbsen()"
                            style="padding:0.75rem 2rem;background:linear-gradient(135deg,var(--brand) 0%,#2d5aa0 100%);color:black;border:none;border-radius:6px;cursor:pointer;font-weight:600;box-shadow:0 4px 12px rgba(68,114,196,0.3);display:flex;align-items:center;gap:0.5rem">
                            <i class="fas fa-save"></i> Simpan Absensi
                        </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="padding:3rem;text-align:center;color:var(--text-light)">
                        <p style="margin:0">Belum ada data siswa untuk kelas ini</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function updateDateParams() {
        const tanggalFull = document.getElementById('datePicker').value;
        const kelas       = '<?= $kelas_terpilih ?? '' ?>';
        if (tanggalFull) {
            let url = '?tab=absen_siswa&tanggal_full=' + encodeURIComponent(tanggalFull);
            if (kelas) url += '&kelas=' + encodeURIComponent(kelas);
            window.location.href = url;
        }
    }

    function loadKelasAbsen(kelas) {
        const picker     = document.getElementById('datePicker');
        const tanggalFull = picker ? picker.value : '<?= $tanggal_full ?>';
        if (!tanggalFull) {
            alert('Silakan pilih tanggal terlebih dahulu');
            return;
        }
        window.location.href = '?tab=absen_siswa&tanggal_full=' + encodeURIComponent(tanggalFull) + '&kelas=' + encodeURIComponent(kelas);
    }

    function resetAbsenRadio(button) {
        const container = button.closest('div');
        container.querySelectorAll('.radio-absen').forEach(r => r.checked = false);
    }

    function toggleHadirSemua() {
        const isChecked = document.getElementById('checkHadirSemua').checked;
        document.querySelectorAll('.radio-absen').forEach(radio => {
            if (radio.value === 'H') radio.checked = isChecked;
        });
    }

    const hasExistingAbsen = <?= !empty($has_existing_absen) ? 'true' : 'false' ?>;

    function simpanAbsen() {
        const data = [];
        document.querySelectorAll('.radio-absen:checked').forEach(radio => {
            data.push({ id_siswa: radio.dataset.siswaId, keterangan: radio.value });
        });

        if (data.length === 0) {
            alert('Mohon pilih minimal satu status absensi');
            return;
        }

        fetch('../../../backend/pages/save_absen.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                kelas: '<?= $kelas_terpilih ?>',
                absen: data,
                tanggal: '<?= $tanggal_full ?>',
                mode: hasExistingAbsen ? 'update' : 'insert'
            })
        })
        .then(r => r.json())
        .then(result => {
            if (result.status === 'success') {
                alert(hasExistingAbsen ? 'Absensi berhasil diupdate!' : 'Absensi berhasil disimpan!');
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(() => alert('Terjadi kesalahan saat menyimpan absensi'));
    }
</script>
