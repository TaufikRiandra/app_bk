<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if(!isset($_SESSION['login'])){
	header("Location: /frontend/auth/login.php");
	exit;
}
include "../../../backend/config/database.php";
include "../../layouts/header.php";
include "../../layouts/sidebar.php";

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$kelas_terpilih = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : null;

// Get school data — pakai kolom yang ada di DB (tahun_ajaran)
$sekolah_result = mysqli_query($conn, "SELECT * FROM sekolah LIMIT 1");
$data_sekolah = mysqli_fetch_assoc($sekolah_result);

// Get guru BK untuk kelas yang dipilih — join tabel kelas + guru_bk
$guru_bk_data = null;
if($kelas_terpilih){
	$kelas_escaped = mysqli_real_escape_string($conn, $kelas_terpilih);
	$guru_bk_query = mysqli_query($conn, "
		SELECT k.*, g.nama, g.nip, g.no_telp, g.foto
		FROM kelas k
		JOIN guru_bk g ON k.id_guru_bk = g.id_guru_bk
		WHERE k.nama_kelas = '$kelas_escaped'
		LIMIT 1
	");
	$guru_bk_data = mysqli_fetch_assoc($guru_bk_query);
}

// Ambil semua nama kelas dari tabel kelas (untuk tahu kelas mana yang sudah terdaftar)
$kelas_terdaftar = [];
$kelas_list_result = mysqli_query($conn, "SELECT nama_kelas FROM kelas");
while($row = mysqli_fetch_assoc($kelas_list_result)){
	$kelas_terdaftar[] = $row['nama_kelas'];
}
?>

    <div style="background:var(--brand,#4472C4);color:white;padding:1.25rem 1.5rem;border-radius:8px;margin-bottom:1.25rem">
        <h2 style="margin:0 0 4px;font-size:1.1rem;font-weight:700">
            <i class="fa-solid fa-people-roof"></i> DATA KELAS
        </h2>
        <p style="margin:0;font-size:.85rem;opacity:.9">Data Guru BK per kelas</p>
        <p>
			<?= htmlspecialchars($data_sekolah['nama_sekolah'] ?? 'Sekolah') ?>
		</p>
    </div>

<section class="card">

	<!-- Layout 3 Kolom -->
	<div style="display:grid;grid-template-columns:280px 1fr 350px;gap:2rem;align-items:start">
		
		<!-- Kolom Kiri: Data Sekolah -->
		<div style="position:relative;top:0rem;background:var(--bg-light);padding:1.5rem;border-radius:8px;border:1px solid var(--border)">
			<h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Data Sekolah</h3>
			
			<table style="width:100%;font-size:0.9rem">
				<tbody>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Nama:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['nama_sekolah'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Pemerintah:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['pemerintah'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Dinas:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['dinas'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Alamat:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['alamat'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Jalan:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['jalan'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Tahun Ajaran:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['tahun_ajaran'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Kepala Sekolah:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['kepala_sekolah'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0;white-space:nowrap;padding-right:0.75rem">Nip Kepala Sekolah:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['nip_kepala_sekolah'] ?? null) ?: '-') ?></td>
					</tr>
				</tbody>
			</table>
			
			<a href="<?php echo ($role === 'guru_bk' ? '/frontend/dashboard.php' : './index.php'); ?>" class="btn secondary" style="text-decoration:none;width:100%;text-align:center;margin-top:1rem;display:block">← Kembali</a>
		</div>

		<!-- Kolom Tengah: Pilih Kelas -->
		<div>
			<h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Pilih Kelas</h3>
			
			<?php
			// Ambil tingkat kelas dari kolom 'kelas' di tabel sekolah (contoh: "7, 8, 9")
			$tingkat_list = [7, 8, 9]; // default fallback
			if (!empty($data_sekolah['kelas'])) {
				$parsed = array_map('trim', explode(',', $data_sekolah['kelas']));
				$tingkat_list = array_filter($parsed, 'is_numeric');
			}

			$suffix_list = ['A', 'B', 'C', 'D', 'E', 'F'];

			foreach($tingkat_list as $tingkat):
			?>
			<div style="margin-bottom:1.5rem">
				<h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas <?= $tingkat ?></h5>
				<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem">
					<?php foreach($suffix_list as $suffix):
						$nama_kelas = $tingkat . $suffix;
						$is_selected = ($kelas_terpilih === $nama_kelas);
						$terdaftar = in_array($nama_kelas, $kelas_terdaftar);

						if($is_selected){
							$btn_style = 'background:gray;color:black;border:black';
						} elseif($terdaftar){
							// kelas sudah ada di DB — tampilkan lebih menonjol
							$btn_style = 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--brand)';
						} else {
							$btn_style = 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
						}
					?>
						<button type="button"
							onclick="loadKelasData('<?= $nama_kelas ?>')"
							style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.2s;cursor:pointer;font-size:0.9rem"
							title="Kelas <?= $nama_kelas ?>">
							<?= $nama_kelas ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endforeach; ?>

			<?php if(!$kelas_terpilih): ?>
				<div style="text-align:center;padding:2rem;color:var(--text-light);background:var(--bg-light);border-radius:8px">
					<p style="margin:0">Pilih kelas untuk melihat info Guru BK</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Kolom Kanan: Card Guru BK -->
		<div style="position:sticky;top:2rem">
			<?php if($kelas_terpilih && $guru_bk_data): ?>
				<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
					<div style="background:var(--brand,#4472C4);color:black;padding:1rem;text-align:center">
						<h3 style="margin:0;font-size:1.2rem">Kelas <?= htmlspecialchars($kelas_terpilih) ?></h3>
					</div>
					<div style="padding:1.5rem">
						<div style="display:flex;gap:1rem">
							<div style="flex:0 0 100px">
								<?php if(!empty($guru_bk_data['foto'])): ?>
									<img src="<?= htmlspecialchars($guru_bk_data['foto']) ?>"
										alt="Foto Guru"
										style="width:100px;height:120px;border-radius:8px;object-fit:cover">
								<?php else: ?>
									<div style="width:100px;height:120px;background:linear-gradient(135deg,var(--brand) 0%,#5b21b6 100%);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:3rem">
									</div>
								<?php endif; ?>
							</div>
							<div style="flex:1">
								<h4 style="margin:0 0 1rem 0;color:var(--brand);font-size:1.1rem">
									<?= htmlspecialchars(($guru_bk_data['nama'] ?? null) ?: '-') ?>
								</h4>
								<div style="font-size:0.85rem;color:var(--text-light);line-height:1.8">
									<div><strong>NIP:</strong></div>
									<div><?= htmlspecialchars(($guru_bk_data['nip'] ?? null) ?: 'Tidak tersedia') ?></div>
									<div style="margin-top:0.75rem"><strong>No. Telepon:</strong></div>
									<div><?= htmlspecialchars(($guru_bk_data['no_telp'] ?? null) ?: 'Tidak tersedia') ?></div>
								</div>
							</div>
						</div>
					</div>
					<div style="background:var(--bg-light);padding:1rem;border-top:1px solid var(--border);text-align:center;font-size:0.85rem;color:var(--text-light)">
						Guru Bimbingan Konseling yang ditugaskan
					</div>
				</div>

			<?php elseif($kelas_terpilih && !$guru_bk_data): ?>
				<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
					<div style="background:#f59e0b;color:black;padding:1rem;text-align:center">
						<h3 style="margin:0;font-size:1.2rem">Kelas <?= htmlspecialchars($kelas_terpilih) ?></h3>
					</div>
					<div style="padding:2rem;text-align:center">
						<div style="font-size:2.5rem;margin-bottom:1rem"><i class="fa-solid fa-triangle-exclamation"></i></div>
						<p style="color:var(--text-light);margin:0">Guru BK belum ditetapkan untuk kelas ini</p>
						<?php if($role === 'admin'): ?>
							<p style="color:var(--text-light);margin:0.5rem 0 0 0;font-size:0.85rem">
								Tambahkan kelas ini terlebih dahulu di menu manajemen kelas
							</p>
						<?php endif; ?>
					</div>
				</div>

			<?php else: ?>
				<div style="background:var(--surface);border:2px dashed var(--border);border-radius:12px;padding:2rem;text-align:center">
					<div style="font-size:2.5rem;margin-bottom:1rem"><i class="fa-solid fa-user"></i></div>
					<p style="color:var(--text-light);margin:0">Pilih kelas untuk melihat</p>
					<p style="color:var(--text-light);margin:0.5rem 0 0 0;font-size:0.9rem">informasi Guru BK</p>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>

<script>
	function loadKelasData(kelas) {
		window.location.href = '?kelas=' + encodeURIComponent(kelas);
	}
</script>

<?php include "../../layouts/footer.php"; ?>