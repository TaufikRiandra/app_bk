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

// Setup role dan kelas terpilih
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$kelas_terpilih = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : null;

// Get school data
$sekolah = mysqli_query($conn, "SELECT * FROM sekolah WHERE kelas IS NULL OR kelas = '' LIMIT 1");
$data_sekolah = mysqli_fetch_assoc($sekolah);

// Get guru BK untuk kelas yang dipilih
$guru_bk_data = null;
if($kelas_terpilih){
	$kelas_escaped = mysqli_real_escape_string($conn, $kelas_terpilih);
	$guru_bk_query = mysqli_query($conn, "SELECT gk.*, g.nama, g.nip, g.no_telp, g.foto FROM guru_bk_kelas gk JOIN guru_bk g ON gk.id_guru_bk = g.id_guru_bk WHERE gk.kelas = '$kelas_escaped' LIMIT 1");
	$guru_bk_data = mysqli_fetch_assoc($guru_bk_query);
}
?>

<section class="card">
	<div style="margin-bottom:2rem">
		<h1 style="margin-bottom:0.25rem;font-size:1.75rem">📚 Data Kelas</h1>
		<p style="color:var(--text-light);margin:0">
			<?= htmlspecialchars($data_sekolah['nama_sekolah'] ?? 'Sekolah') ?> - 
			<?= htmlspecialchars($data_sekolah['tahun_pelajaran'] ?? 'Tahun Pelajaran') ?>
		</p>
	</div>

	<!-- Layout 3 Kolom -->
	<div style="display:grid;grid-template-columns:280px 1fr 350px;gap:2rem;align-items:start">
		
		<!-- Kolom Kiri: Data Sekolah -->
		<div style="position:sticky;top:2rem;background:var(--bg-light);padding:1.5rem;border-radius:8px;border:1px solid var(--border)">
			<h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">📋 Data Sekolah</h3>
			
			<table style="width:100%;font-size:0.9rem">
				<tbody>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0">Nama:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['nama_sekolah'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0">Pemerintah:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['pemerintah'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0">Dinas:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['dinas'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0">Alamat:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['alamat'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0">Jalan:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['jalan'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0">Tahun Pelajaran:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['tahun_pelajaran'] ?? null) ?: '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight:600;color:var(--text-light);padding:0.5rem 0">Kepala Sekolah:</td>
						<td style="padding:0.5rem 0"><?= htmlspecialchars(($data_sekolah['kepala_sekolah'] ?? null) ?: '-') ?></td>
					</tr>
				</tbody>
			</table>
			
			<a href="<?php echo ($role === 'guru_bk' ? '/frontend/dashboard.php' : './index.php'); ?>" class="btn secondary" style="text-decoration:none;width:100%;text-align:center;margin-top:1rem">← Kembali</a>
		</div>

		<!-- Kolom Tengah: Pilih Kelas -->
		<div>
			<h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Pilih Kelas</h3>
			
			<!-- Kelas 7 -->
			<div style="margin-bottom:1.5rem">
				<h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas 7</h5>
				<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem">
					<?php 
						$kelas_7 = ['7A', '7B', '7C', '7D', '7E', '7F'];
						foreach($kelas_7 as $k):
							$is_selected = ($kelas_terpilih === $k) ? true : false;
							$btn_style = $is_selected 
								? 'background:var(--brand);color:white' 
								: 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
					?>
						<button type="button" onclick="loadKelasData('<?= $k ?>')" style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.3s;cursor:pointer;border:none;font-size:0.9rem" title="Kelas <?= $k ?>">
							<?= $k ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Kelas 8 -->
			<div style="margin-bottom:1.5rem">
				<h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas 8</h5>
				<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem">
					<?php 
						$kelas_8 = ['8A', '8B', '8C', '8D', '8E', '8F'];
						foreach($kelas_8 as $k):
							$is_selected = ($kelas_terpilih === $k) ? true : false;
							$btn_style = $is_selected 
								? 'background:var(--brand);color:white' 
								: 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
					?>
						<button type="button" onclick="loadKelasData('<?= $k ?>')" style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.3s;cursor:pointer;border:none;font-size:0.9rem" title="Kelas <?= $k ?>">
							<?= $k ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Kelas 9 -->
			<div style="margin-bottom:1.5rem">
				<h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas 9</h5>
				<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem">
					<?php 
						$kelas_9 = ['9A', '9B', '9C', '9D', '9E', '9F'];
						foreach($kelas_9 as $k):
							$is_selected = ($kelas_terpilih === $k) ? true : false;
							$btn_style = $is_selected 
								? 'background:var(--brand);color:white' 
								: 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
					?>
						<button type="button" onclick="loadKelasData('<?= $k ?>')" style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.3s;cursor:pointer;border:none;font-size:0.9rem" title="Kelas <?= $k ?>">
							<?= $k ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Pesan jika tidak ada kelas dipilih -->
			<?php if(!$kelas_terpilih): ?>
				<div style="text-align:center;padding:2rem;color:var(--text-light);background:var(--bg-light);border-radius:8px">
					<p style="margin:0">👈 Pilih kelas untuk melihat info</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Kolom Kanan: Card Guru BK -->
		<div style="position:sticky;top:2rem">
			<?php if($kelas_terpilih && $guru_bk_data): ?>
				<!-- Card Guru BK -->
				<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
					<!-- Header dengan Kelas -->
					<div style="background:var(--brand);color:white;padding:1rem;text-align:center">
						<h3 style="margin:0;font-size:1.2rem">Kelas <?= htmlspecialchars($kelas_terpilih) ?></h3>
					</div>

					<!-- Foto + Info -->
					<div style="padding:1.5rem">
						<div style="display:flex;gap:1rem">
							<!-- Foto (Kiri Besar) -->
							<div style="flex:0 0 100px">
								<?php 
									$foto = $guru_bk_data['foto'] ?? null;
									$foto_path = dirname(__FILE__) . '/../../assets/uploads/guru_bk/' . $foto;
									if($foto && file_exists($foto_path)):
								?>
									<img src="/frontend/assets/uploads/guru_bk/<?= htmlspecialchars($foto) ?>" alt="Foto Guru" style="width:100px;height:120px;border-radius:8px;object-fit:cover">
								<?php else: ?>
									<div style="width:100px;height:120px;background:linear-gradient(135deg, var(--brand) 0%, #5b21b6 100%);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:3rem">
										👨‍🏫
									</div>
								<?php endif; ?>
							</div>

							<!-- Info (Kanan) -->
							<div style="flex:1">
								<h4 style="margin:0 0 1rem 0;color:var(--brand);font-size:1.1rem"><?= htmlspecialchars(($guru_bk_data['nama'] ?? null) ?: '-') ?></h4>
								<div style="font-size:0.85rem;color:var(--text-light);line-height:1.8">
									<div><strong>NIP:</strong></div>
									<div><?= htmlspecialchars(($guru_bk_data['nip'] ?? null) ?: 'Tidak tersedia') ?></div>
									<div style="margin-top:0.75rem"><strong>No. Telepon:</strong></div>
									<div><?= htmlspecialchars(($guru_bk_data['no_telp'] ?? null) ?: 'Tidak tersedia') ?></div>
								</div>
							</div>
						</div>
					</div>

					<!-- Footer -->
					<div style="background:var(--bg-light);padding:1rem;border-top:1px solid var(--border);text-align:center;font-size:0.85rem;color:var(--text-light)">
						Guru Bimbingan Konseling yang ditugaskan
					</div>
				</div>
			<?php elseif($kelas_terpilih && !$guru_bk_data): ?>
				<!-- Guru BK Belum Ditetapkan -->
				<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
					<div style="background:#f59e0b;color:white;padding:1rem;text-align:center">
						<h3 style="margin:0;font-size:1.2rem">Kelas <?= htmlspecialchars($kelas_terpilih) ?></h3>
					</div>
					<div style="padding:2rem;text-align:center">
						<div style="font-size:2.5rem;margin-bottom:1rem">⚠️</div>
						<p style="color:var(--text-light);margin:0">Guru BK belum ditetapkan untuk kelas ini</p>
					</div>
				</div>
			<?php else: ?>
				<!-- Kelas Belum Dipilih -->
				<div style="background:var(--surface);border:2px dashed var(--border);border-radius:12px;padding:2rem;text-align:center">
					<div style="font-size:2.5rem;margin-bottom:1rem">📌</div>
					<p style="color:var(--text-light);margin:0">Pilih kelas untuk melihat</p>
					<p style="color:var(--text-light);margin:0.5rem 0 0 0;font-size:0.9rem">informasi Guru BK</p>
				</div>
			<?php endif; ?>
		</div>

	</div>

</section>

<script>
	function loadKelasData(kelas) {
		// Update URL
		window.history.pushState({}, '', '?kelas=' + encodeURIComponent(kelas));
		// Reload halaman untuk ambil data guru BK
		window.location.reload();
	}

	// Load data otomatis jika ada kelas di URL
	<?php if ($kelas_terpilih): ?>
		// Kelas sudah dimuat dari URL
	<?php endif; ?>
</script>

<?php include "../../layouts/footer.php"; ?>


