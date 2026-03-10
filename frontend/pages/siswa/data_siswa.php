<?php
$role        = $_SESSION['role'] ?? 'guest';
$kelas_terpilih = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : null;

$conn = $GLOBALS['conn'] ?? null;
if (!$conn) {
    include "../../../backend/config/database.php";
}

require_once "../../../backend/config/auth_helper.php";

// Kelas yang boleh diakses
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

// Load existing siswa data for selected kelas
$siswa_exist     = [];
$has_exist_data  = false;
if ($kelas_terpilih) {
    $kelas_escaped = mysqli_real_escape_string($conn, $kelas_terpilih);
    $siswa_query   = mysqli_query($conn,
        "SELECT id_siswa, nama_siswa FROM siswa WHERE kelas = '$kelas_escaped' ORDER BY id_siswa ASC LIMIT 30");
    if ($siswa_query && mysqli_num_rows($siswa_query) > 0) {
        $has_exist_data = true;
        $counter = 1;
        while ($row = mysqli_fetch_assoc($siswa_query)) {
            $siswa_exist[$counter] = $row['nama_siswa'];
            $counter++;
        }
    }
}
?>

<!-- Layout 2 Kolom -->
<div style="display:grid;grid-template-columns:280px 1fr;gap:2rem;align-items:start">

    <!-- Kolom Kiri: Pilih Kelas -->
    <div style="position:relative;top:0rem;background:var(--bg-secondary);padding:1.5rem;border-radius:8px;border:1px solid var(--border)">
        <h3 style="margin-top:0;margin-bottom:0.5rem;color:#4472C4;font-size:1rem">Pilih Kelas</h3>

        <?php if (!isAdmin()): ?>
        <p style="margin:0 0 1rem 0;font-size:0.78rem;color:var(--text-light);background:#f0f4ff;padding:0.5rem 0.75rem;border-radius:5px;border-left:3px solid #4472C4">
            <i class="fas fa-lock"></i> Kelas yang ditetapkan untuk Anda
        </p>
        <?php else: ?>
        <p style="margin:0 0 1rem 0;font-size:0.78rem;color:var(--text-light)">Pilih kelas untuk mengisi data siswa</p>
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
                                ? 'background:gray;color:var(--text);border:0px solid #4F41E8;'
                                : 'background:var(--bg-light);color:var(--text);border:1px solid var(--border)';
                        ?>
                            <button type="button" onclick="loadKelasData('<?= $k ?>')"
                                    style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.3s;cursor:pointer;font-size:0.9rem;position:relative"
                                    title="Kelas <?= $k ?>">
                                <?= $k ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Kolom Kanan: Form Input Nama Siswa -->
    <div>
        <?php if ($kelas_terpilih): ?>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:2rem">
                <div style="margin-bottom:1.5rem">
                    <h2 style="margin:0 0 0.5rem 0;color:var(--brand)">Input Nama Siswa</h2>
                    <p style="color:var(--text-light);margin:0">
                        Kelas <?= htmlspecialchars($kelas_terpilih) ?>
                        <?= $has_exist_data
                            ? '<i class="fas fa-check-circle" style="color:var(--success)"></i> (Data sudah ada)'
                            : '<i class="fas fa-pen-to-square"></i> (Data baru)' ?>
                    </p>
                </div>
                <form id="formNamaSiswa" style="display:grid;grid-template-columns:1fr;gap:1rem;max-height:600px;overflow-y:auto;padding-right:0.5rem">
                    <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas_terpilih) ?>">

                    <?php for ($i = 1; $i <= 30; $i++):
                        $nama_siswa = isset($siswa_exist[$i]) ? htmlspecialchars($siswa_exist[$i]) : '';
                    ?>
                        <div style="display:grid;grid-template-columns:60px 1fr;gap:0.5rem;align-items:center">
                            <label style="font-weight:600;color:var(--text-dark);font-size:0.9rem">No. <?= $i ?></label>
                            <input type="text"
                                   name="nama_siswa[<?= $i ?>]"
                                   class="input-siswa"
                                   placeholder="Nama siswa"
                                   value="<?= $nama_siswa ?>"
                                   style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem">
                        </div>
                    <?php endfor; ?>
                </form>

                <div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
                    <button onclick="simpanSiswa()"
                            style="padding:0.75rem 2rem;background:linear-gradient(135deg, var(--brand) 0%, #5b21b6 100%);color:var(--text);border:none;border-radius:6px;cursor:pointer;font-weight:600;box-shadow:0 4px 12px rgba(91,78,255,0.3)">
                        <i class="fas fa-<?= $has_exist_data ? 'sync-alt' : 'save' ?>"></i>
                        <?= $has_exist_data ? 'Update Data' : 'Simpan Data' ?>
                    </button>
                    <button onclick="kosongkanForm()"
                            style="padding:0.75rem 2rem;background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border);border-radius:6px;cursor:pointer;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                        <i class="fas fa-redo"></i> Bersihkan
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div style="background:var(--surface);border:2px dashed var(--border);border-radius:12px;padding:3rem;text-align:center">
                <div style="font-size:2.5rem;margin-bottom:1rem"><i class="fas fa-clipboard"></i></div>
                <p style="color:var(--text-light);margin:0;font-size:1.1rem">Pilih kelas untuk mulai</p>
                <p style="color:var(--text-light);margin:0.5rem 0 0 0;font-size:0.95rem">mengisi data nama siswa</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function loadKelasData(kelas) {
        window.history.pushState({}, '', '?tab=data_siswa&kelas=' + encodeURIComponent(kelas));
        window.location.reload();
    }

    function simpanSiswa() {
        const formData   = new FormData(document.getElementById('formNamaSiswa'));
        const kelas      = formData.get('kelas');
        const namaSiswa  = [];

        for (let i = 1; i <= 30; i++) {
            const nama = formData.get(`nama_siswa[${i}]`);
            if (nama.trim()) {
                namaSiswa.push({ no: i, nama: nama });
            }
        }

        if (namaSiswa.length === 0) {
            if (!confirm('Semua nama siswa kosong. Lanjutkan? Ini akan menghapus semua siswa di kelas ' + kelas + '.')) {
                return;
            }
        }

        fetch('../../../backend/pages/save_siswa.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ kelas: kelas, siswa: namaSiswa })
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP Error: ' + response.status);
            return response.text();
        })
        .then(text => {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                alert(data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('⚠️ Error: ' + error.message));
    }

    function kosongkanForm() {
        document.getElementById('formNamaSiswa').reset();
        document.querySelectorAll('.input-siswa').forEach(input => input.value = '');
    }
</script>
