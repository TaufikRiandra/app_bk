<?php
// Setup
$kelas_terpilih    = isset($_GET['kelas'])    ? htmlspecialchars($_GET['kelas'])    : null;
$semester_terpilih = isset($_GET['semester']) ? htmlspecialchars($_GET['semester']) : null;

// Get database connection (global dari rekap.php)
$conn = $GLOBALS['conn'] ?? null;
if (!$conn) {
    include "../../../backend/config/database.php";
}

// Ambil data sekolah
$sekolah_query = mysqli_query($conn, "SELECT nama_sekolah FROM sekolah LIMIT 1");
$sekolah       = mysqli_fetch_assoc($sekolah_query);
$nama_sekolah  = $sekolah['nama_sekolah'] ?? 'UPT SMPN 03 SOLOK SELATAN';

// Ambil semua kelas yang tersedia dari database (dinamis)
$all_kelas = [];
$kelas_q   = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas");
while ($row = mysqli_fetch_assoc($kelas_q)) {
    $all_kelas[] = $row['kelas'];
}
// Fallback jika DB kosong
if (empty($all_kelas)) {
    $all_kelas = ['7A','7B','7C','7D','7E','7F','8A','8B','8C','8D','8E','8F','9A','9B','9C','9D','9E','9F'];
}

// Jika belum pilih kelas
if (!$kelas_terpilih): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1.5rem">
        <?php foreach ($all_kelas as $kelas): ?>
            <button
                onclick="window.history.pushState({},'','?tab=rekap_absen&kelas=<?= $kelas ?>&semester=');window.location.reload()"
                onmouseover="this.style.boxShadow='0 8px 24px rgba(91,78,255,0.4)';this.style.transform='translateY(-4px)'"
                onmouseout="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';this.style.transform='translateY(0)'"
                style="padding:2rem;background:linear-gradient(135deg,var(--brand) 0%,#5b21b6 100%);color:var(--text);border:none;border-radius:12px;cursor:pointer;font-size:1.2rem;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.15);display:flex;align-items:center;justify-content:center;gap:0.75rem;transition:all 0.3s ease">
                <i class="fas fa-chart-pie"></i> Kelas <?= $kelas ?>
            </button>
        <?php endforeach; ?>
    </div>

<?php else:
    // Sudah pilih kelas
    ?>
    <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border);overflow:hidden">

        <!-- Header -->
        <div style="background:var(--brand);color:var(--text);padding:2rem;text-align:center">
            <h2 style="margin:0 0 0.5rem 0;font-size:1.5rem">
                REKAP ABSEN KELAS <?= htmlspecialchars($kelas_terpilih) ?>
            </h2>
            <p style="margin:0.5rem 0 0 0;font-size:0.95rem"><?= htmlspecialchars($nama_sekolah) ?></p>
        </div>

        <!-- Pilih Semester -->
        <div style="padding:1.5rem;border-bottom:1px solid var(--border);background:var(--bg-light)">
            <label style="display:block;margin-bottom:0.5rem;font-weight:600;color:var(--text-dark)">Pilih Semester:</label>
            <select id="semesterSelect" onchange="updateSemester(this.value)"
                    style="padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;cursor:pointer;min-width:200px">
                <option value="">-- Pilih Semester --</option>
                <option value="1" <?= $semester_terpilih === '1' ? 'selected' : '' ?>>Semester 1 (Juli - Desember)</option>
                <option value="2" <?= $semester_terpilih === '2' ? 'selected' : '' ?>>Semester 2 (Januari - Juni)</option>
            </select>
        </div>

        <?php if ($semester_terpilih):
            // PERBAIKAN: tahun dinamis berdasarkan tanggal sekarang
            $current_year = (int) date('Y');
            $current_month_num = (int) date('n');

            if ($semester_terpilih === '1') {
                $months = ['07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                // Semester 1: Juli–Des. Jika bulan sekarang Jan–Jun, kemungkinan tahun lalu
                $tahun = ($current_month_num >= 7) ? $current_year : $current_year - 1;
            } else {
                $months = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni'];
                // Semester 2: Jan–Jun. Jika bulan sekarang Jul–Des, ini tahun berikutnya
                $tahun = ($current_month_num <= 6) ? $current_year : $current_year + 1;
            }

            // Ambil semua siswa di kelas ini
            $kelas_escaped = mysqli_real_escape_string($conn, $kelas_terpilih);
            $siswa_query   = mysqli_query($conn,
                "SELECT id_siswa, nis, nama_siswa FROM siswa
                 WHERE kelas = '$kelas_escaped'
                 ORDER BY CAST(SUBSTRING_INDEX(nis, '-', -1) AS UNSIGNED) ASC"
            );
            $siswa_list = [];
            while ($row = mysqli_fetch_assoc($siswa_query)) {
                $siswa_list[] = $row;
            }
        ?>

        <!-- Tabel Rekap -->
        <div style="overflow-x:auto;padding:1.5rem;background:white">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <!-- Row 1: Bulan -->
                    <tr style="border-bottom:1px solid var(--border)">
                        <th rowspan="2" style="padding:0.5rem;text-align:center;font-weight:600;border-right:1px solid var(--border);min-width:40px;background:#FFC000;color:black">NO</th>
                        <th rowspan="2" style="padding:0.5rem;text-align:left;font-weight:600;border-right:1px solid var(--border);min-width:120px;background:#FFC000;color:black">NAMA</th>
                        <?php foreach ($months as $bulan_kode => $bulan_nama): ?>
                            <th colspan="6" style="padding:0.5rem;text-align:center;font-weight:600;border-right:1px solid var(--border);background:#FFC000;color:black">
                                <?= htmlspecialchars($bulan_nama) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    <!-- Row 2: Kode Status -->
                    <tr style="border-bottom:2px solid var(--border)">
                        <?php foreach ($months as $bulan_kode => $bulan_nama):
                            foreach (['H','I','S','A','C','T'] as $code): ?>
                                <th style="padding:0.5rem;text-align:center;font-weight:600;border-right:1px solid var(--border);min-width:35px;background:#FFC000;color:black">
                                    <?= $code ?>
                                </th>
                        <?php endforeach; endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($siswa_list as $siswa):
                        $id_siswa = $siswa['id_siswa'];
                    ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:0.5rem;text-align:center;border-right:1px solid var(--border);background:var(--bg-light);color:black !important"><?= $no ?></td>
                            <td style="padding:0.5rem;border-right:1px solid var(--border);color:black !important"><?= htmlspecialchars($siswa['nama_siswa']) ?></td>
                            <?php
                            $bulan_index = 0;
                            foreach ($months as $bulan_kode => $bulan_nama):
                                $cell_bg = $bulan_index % 2 == 0 ? '#FFF9E6' : '#F9F9F9';
                                $bulan_index++;

                                $tanggal_awal = "$tahun-$bulan_kode-01";
                                if ($bulan_kode == '12') {
                                    $tanggal_akhir = ($tahun + 1) . "-01-01";
                                } else {
                                    $next_bulan    = str_pad((int)$bulan_kode + 1, 2, '0', STR_PAD_LEFT);
                                    $tanggal_akhir = "$tahun-$next_bulan-01";
                                }

                                $absen_q = mysqli_query($conn, "
                                    SELECT keterangan, COUNT(*) as jumlah
                                    FROM absen_siswa
                                    WHERE id_siswa = $id_siswa
                                    AND tanggal >= '$tanggal_awal'
                                    AND tanggal < '$tanggal_akhir'
                                    GROUP BY keterangan
                                ");
                                $absen_data = [];
                                while ($row = mysqli_fetch_assoc($absen_q)) {
                                    $absen_data[$row['keterangan']] = $row['jumlah'];
                                }

                                foreach (['H'=>'Hadir','I'=>'Izin','S'=>'Sakit','A'=>'Alfa','C'=>'Cabut','T'=>'Terlambat'] as $code => $status):
                                    $value = $absen_data[$status] ?? 0;
                            ?>
                                <td style="padding:0.5rem;text-align:center;border-right:1px solid var(--border);background:<?= $cell_bg ?>;color:black !important">
                                    <?= $value ?>
                                </td>
                            <?php endforeach; endforeach; ?>
                        </tr>
                    <?php $no++; endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tombol Export & Kembali -->
        <div style="padding:1.5rem;text-align:center;border-top:1px solid var(--border);display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <button onclick="exportToExcel()"
                    style="padding:0.75rem 1.5rem;background:linear-gradient(135deg,#27ae60 0%,#229954 100%);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 12px rgba(39,174,96,0.3)">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button onclick="exportToPDF()"
                    style="padding:0.75rem 1.5rem;background:linear-gradient(135deg,#e74c3c 0%,#c0392b 100%);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 12px rgba(231,76,60,0.3)">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button onclick="window.history.pushState({},'','?tab=rekap_absen');window.location.reload()"
                    style="padding:0.75rem 1.5rem;background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border);border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:0.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>

        <?php else: ?>
            <div style="padding:3rem;text-align:center;color:var(--text-light)">
                <div style="font-size:2rem;margin-bottom:1rem"><i class="fas fa-clipboard-list"></i></div>
                <p style="margin:0;font-size:1.1rem">Pilih semester untuk melihat rekap absensi</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateSemester(semester) {
            if (semester) {
                const params = new URLSearchParams();
                params.set('tab', 'rekap_absen');
                params.set('kelas', '<?= $kelas_terpilih ?>');
                params.set('semester', semester);
                window.history.pushState({}, '', '?' + params.toString());
                window.location.reload();
            }
        }

        function exportToExcel() {
            const kelas    = '<?= $kelas_terpilih ?>';
            const semester = '<?= $semester_terpilih ?>';
            window.location.href = '../../../backend/pages/export_absen_excel.php?kelas=' + encodeURIComponent(kelas) + '&semester=' + encodeURIComponent(semester);
        }

        function exportToPDF() {
            const kelas    = '<?= $kelas_terpilih ?>';
            const semester = '<?= $semester_terpilih ?>';
            window.open('../../../backend/pages/export_absen_pdf.php?kelas=' + encodeURIComponent(kelas) + '&semester=' + encodeURIComponent(semester), '_blank');
        }
    </script>

<?php endif; ?>
