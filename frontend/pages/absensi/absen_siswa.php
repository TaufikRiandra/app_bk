<?php
// Setup role dan parameter yang dipilih
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Ambil tanggal penuh dari picker (format: YYYY-MM-DD)
$tanggal_full = isset($_GET['tanggal_full']) ? htmlspecialchars($_GET['tanggal_full']) : date('Y-m-d');
$kelas_terpilih = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : null;

// Pecah tanggal untuk keperluan display di header tabel
$parts = explode('-', $tanggal_full);
$tahun_terpilih = $parts[0];
$bulan_terpilih = $parts[1];
$tanggal_terpilih = $parts[2];

// Get database connection from global
$conn = $GLOBALS['conn'] ?? null;
if(!$conn) {
    include "../../../backend/config/database.php";
}
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
            <h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Pilih Kelas</h3>
            
            <?php 
            $tingkat_kelas = [
                '7' => ['7A', '7B', '7C', '7D', '7E', '7F'],
                '8' => ['8A', '8B', '8C', '8D', '8E', '8F'],
                '9' => ['9A', '9B', '9C', '9D', '9E', '9F']
            ];

            foreach($tingkat_kelas as $tingkat => $daftar_kelas): ?>
                <div style="margin-bottom:1.5rem">
                    <h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas <?= $tingkat ?></h5>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem">
                        <?php foreach($daftar_kelas as $k): 
                            $is_selected = ($kelas_terpilih === $k);
                            $btn_style = $is_selected 
                                ? 'background:#E8E4FF;color:var(--brand);border:1px solid var(--brand)' 
                                : 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
                        ?>
                            <button type="button" onclick="loadKelasAbsen('<?= $k ?>')" 
                                    style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;cursor:pointer;font-size:0.9rem" title="Kelas <?= $k ?>">
                                <?= $k ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <?php if(!$tanggal_full || !$kelas_terpilih): ?>
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
                    $siswa_query = mysqli_query($conn, "SELECT id_siswa, nis, nama_siswa FROM siswa WHERE kelas = '$kelas_escaped' ORDER BY CAST(SUBSTRING_INDEX(nis, '-', -1) AS UNSIGNED) ASC");
                    $siswa_list = [];
                    while($row = mysqli_fetch_assoc($siswa_query)) { $siswa_list[] = $row; }
                ?>

                <?php if(!empty($siswa_list)): ?>
                    <div style="max-height:600px;overflow-y:auto;padding:1rem">
                        <table style="width:100%;border-collapse:collapse">
                            <tbody>
                                <?php foreach($siswa_list as $index => $siswa): 
                                    $nomor = intval(explode('-', $siswa['nis'])[1] ?? ($index + 1));
                                ?>
                                    <tr style="border-bottom:1px solid var(--border)">
                                        <td style="padding:0.75rem 0.5rem;width:40px;text-align:center;font-weight:600;color:var(--text-light)">
                                            <?= $nomor ?>
                                        </td>
                                        <td style="padding:0.75rem 0.5rem">
                                            <div style="margin-bottom:0.3rem;font-weight:500;color:var(--text-dark)"><?= htmlspecialchars($siswa['nama_siswa']) ?></div>
                                            <div style="display:flex;gap:0.8rem;flex-wrap:wrap">
                                                <?php foreach(['H','I','S','A','C','T'] as $st): ?>
                                                <label style="display:flex;align-items:center;gap:0.2rem;cursor:pointer;font-size:0.85rem">
                                                    <input type="radio" name="absen_siswa_<?= $siswa['id_siswa'] ?>" class="radio-absen" data-siswa-id="<?= $siswa['id_siswa'] ?>" value="<?= $st ?>"> <?= $st ?>
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

                    <div style="background:var(--bg-light);padding:1rem;border-top:1px solid var(--border);text-align:right">
                        <button onclick="simpanAbsen()" style="padding:0.75rem 2rem;background:linear-gradient(135deg, var(--brand) 0%, #5b21b6 100%);color:black;border:none;border-radius:6px;cursor:pointer;font-weight:600;box-shadow:0 4px 12px rgba(91, 78, 255, 0.3)">
                            <i class="fas fa-save"></i> Simpan Absensi
                        </button>
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
        const kelas = '<?= $kelas_terpilih ?? '' ?>';
        
        if(tanggalFull) {
            const params = new URLSearchParams(window.location.search);
            params.set('tab', 'absen_siswa');
            params.set('tanggal_full', tanggalFull);
            if(kelas) params.set('kelas', kelas);
            
            window.location.href = '?' + params.toString();
        }
    }

    function loadKelasAbsen(kelas) {
        const tanggalFull = document.getElementById('datePicker').value;
        
        if(!tanggalFull) {
            alert('Silakan pilih tanggal terlebih dahulu');
            return;
        }
        
        const params = new URLSearchParams(window.location.search);
        params.set('tab', 'absen_siswa');
        params.set('tanggal_full', tanggalFull);
        params.set('kelas', kelas);
        
        window.location.href = '?' + params.toString();
    }

    function resetAbsenRadio(button) {
        const container = button.closest('div');
        const radios = container.querySelectorAll('.radio-absen');
        radios.forEach(radio => radio.checked = false);
    }

    function toggleHadirSemua() {
        const isChecked = document.getElementById('checkHadirSemua').checked;
        const radios = document.querySelectorAll('.radio-absen');

        radios.forEach(radio => {
            if(radio.value === 'H') {
                radio.checked = isChecked;
            }
        });
    }

    function simpanAbsen() {
        const data = [];
        document.querySelectorAll('.radio-absen:checked').forEach(radio => {
            data.push({
                id_siswa: radio.dataset.siswaId,
                keterangan: radio.value
            });
        });

        if(data.length === 0) {
            alert('Mohon pilih minimal satu status absensi');
            return;
        }

        fetch('../../../backend/pages/save_absen.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                kelas: '<?= $kelas_terpilih ?>',
                absen: data,
                tanggal: '<?= $tanggal_full ?>'
            })
        })
        .then(response => response.json())
        .then(result => {
            if(result.status === 'success') {
                alert('Absensi berhasil disimpan!');
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan absensi');
        });
    }
</script>