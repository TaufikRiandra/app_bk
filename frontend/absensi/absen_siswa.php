<?php
// Setup role dan parameter yang dipilih
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$tanggal_terpilih = isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal']) : null;
$bulan_terpilih = isset($_GET['bulan']) ? htmlspecialchars($_GET['bulan']) : null;
$tahun_terpilih = isset($_GET['tahun']) ? htmlspecialchars($_GET['tahun']) : date('Y');
$kelas_terpilih = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : null;

// Get database connection from global
$conn = $GLOBALS['conn'] ?? null;
if(!$conn) {
	include "../../backend/config/database.php";
}
?>

<!-- Layout 2 Kolom -->
<div style="display:grid;grid-template-columns:280px 1fr;gap:2rem;align-items:start">
	
	<!-- Kolom Kiri: Pilih Tanggal, Bulan, Tahun & Kelas -->
	<div>
		<!-- Pilih Tanggal, Bulan, Tahun -->
		<div style="sticky:top;top:2rem;background:var(--bg-light);padding:1.5rem;border-radius:8px;border:1px solid var(--border);margin-bottom:1.5rem">
			<h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Pilih Tanggal & Bulan</h3>
			
			<!-- Tanggal -->
			<div style="margin-bottom:1rem">
				<label style="display:block;font-size:0.85rem;color:var(--text-light);margin-bottom:0.5rem">Tanggal</label>
				<select id="selectTanggal" onchange="updateDateParams()" style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem">
					<option value="">-- Pilih Tanggal --</option>
					<?php for($i = 1; $i <= 31; $i++): ?>
						<option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $tanggal_terpilih === str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>><?= $i ?></option>
					<?php endfor; ?>
				</select>
			</div>
			
			<!-- Bulan -->
			<div style="margin-bottom:1rem">
				<label style="display:block;font-size:0.85rem;color:var(--text-light);margin-bottom:0.5rem">Bulan</label>
				<select id="selectBulan" onchange="updateDateParams()" style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem">
					<option value="">-- Pilih Bulan --</option>
					<option value="01" <?= $bulan_terpilih === '01' ? 'selected' : '' ?>>Januari</option>
					<option value="02" <?= $bulan_terpilih === '02' ? 'selected' : '' ?>>Februari</option>
					<option value="03" <?= $bulan_terpilih === '03' ? 'selected' : '' ?>>Maret</option>
					<option value="04" <?= $bulan_terpilih === '04' ? 'selected' : '' ?>>April</option>
					<option value="05" <?= $bulan_terpilih === '05' ? 'selected' : '' ?>>Mei</option>
					<option value="06" <?= $bulan_terpilih === '06' ? 'selected' : '' ?>>Juni</option>
					<option value="07" <?= $bulan_terpilih === '07' ? 'selected' : '' ?>>Juli</option>
					<option value="08" <?= $bulan_terpilih === '08' ? 'selected' : '' ?>>Agustus</option>
					<option value="09" <?= $bulan_terpilih === '09' ? 'selected' : '' ?>>September</option>
					<option value="10" <?= $bulan_terpilih === '10' ? 'selected' : '' ?>>Oktober</option>
					<option value="11" <?= $bulan_terpilih === '11' ? 'selected' : '' ?>>November</option>
					<option value="12" <?= $bulan_terpilih === '12' ? 'selected' : '' ?>>Desember</option>
				</select>
			</div>
			
			<!-- Tahun -->
			<div>
				<label style="display:block;font-size:0.85rem;color:var(--text-light);margin-bottom:0.5rem">Tahun</label>
				<select id="selectTahun" onchange="updateDateParams()" style="width:100%;padding:0.75rem;border:1px solid var(--border);border-radius:6px;font-size:0.9rem">
					<?php for($y = 2024; $y <= 2030; $y++): ?>
						<option value="<?= $y ?>" <?= $tahun_terpilih == $y ? 'selected' : '' ?>><?= $y ?></option>
					<?php endfor; ?>
				</select>
			</div>
		</div>

		<!-- Pilih Kelas -->
		<div style="sticky:top;top:22rem;background:var(--bg-light);padding:1.5rem;border-radius:8px;border:1px solid var(--border)">
			<h3 style="margin-top:0;margin-bottom:1rem;color:var(--brand);font-size:1rem">Pilih Kelas</h3>
			
			<!-- Kelas 7 -->
			<div style="margin-bottom:1.5rem">
				<h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas 7</h5>
				<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem">
					<?php 
						$kelas_7 = ['7A', '7B', '7C', '7D', '7E', '7F'];
						foreach($kelas_7 as $k):
							$is_selected = ($kelas_terpilih === $k) ? true : false;
							$btn_style = $is_selected 
								? 'background:var(--brand);color:white' 
								: 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
					?>
						<button type="button" onclick="loadKelasAbsen('<?= $k ?>')" style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.3s;cursor:pointer;border:none;font-size:0.9rem" title="Kelas <?= $k ?>">
							<?= $k ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Kelas 8 -->
			<div style="margin-bottom:1.5rem">
				<h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas 8</h5>
				<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem">
					<?php 
						$kelas_8 = ['8A', '8B', '8C', '8D', '8E', '8F'];
						foreach($kelas_8 as $k):
							$is_selected = ($kelas_terpilih === $k) ? true : false;
							$btn_style = $is_selected 
								? 'background:var(--brand);color:white' 
								: 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
					?>
						<button type="button" onclick="loadKelasAbsen('<?= $k ?>')" style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.3s;cursor:pointer;border:none;font-size:0.9rem" title="Kelas <?= $k ?>">
							<?= $k ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Kelas 9 -->
			<div style="margin-bottom:1.5rem">
				<h5 style="margin:0 0 0.75rem 0;font-size:0.95rem">Kelas 9</h5>
				<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem">
					<?php 
						$kelas_9 = ['9A', '9B', '9C', '9D', '9E', '9F'];
						foreach($kelas_9 as $k):
							$is_selected = ($kelas_terpilih === $k) ? true : false;
							$btn_style = $is_selected 
								? 'background:var(--brand);color:white' 
								: 'background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border)';
					?>
						<button type="button" onclick="loadKelasAbsen('<?= $k ?>')" style="<?= $btn_style ?>;padding:0.75rem;text-align:center;border-radius:6px;font-weight:600;transition:all 0.3s;cursor:pointer;border:none;font-size:0.9rem" title="Kelas <?= $k ?>">
							<?= $k ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Kolom Kanan: Data Absen Siswa -->
	<div>
		<?php if(!$tanggal_terpilih || !$bulan_terpilih || !$kelas_terpilih): ?>
			<div style="background:var(--surface);border:2px dashed var(--border);border-radius:12px;padding:3rem;text-align:center">
				<div style="font-size:2.5rem;margin-bottom:1rem">📅</div>
				<p style="color:var(--text-light);margin:0;font-size:1.1rem">Pilih tanggal, bulan, tahun dan kelas</p>
				<p style="color:var(--text-light);margin:0.5rem 0 0 0;font-size:0.95rem">untuk mulai mengisi absensi siswa</p>
			</div>
		<?php else: ?>
			<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden">
				<div style="background:var(--brand);color:white;padding:1rem;text-align:center">
					<h3 style="margin:0;font-size:1.2rem">Absen Kelas <?= htmlspecialchars($kelas_terpilih ?? '') ?></h3>
					<p style="margin:0.5rem 0 0 0;font-size:0.95rem">Tanggal <?= $tanggal_terpilih ?? '' ?> <?= date('F Y', strtotime($tahun_terpilih . '-' . $bulan_terpilih . '-01')) ?></p>
				</div>

				<!-- Keterangan Simbol -->
				<div style="background:var(--bg-light);padding:1rem;border-bottom:1px solid var(--border)">
					<p style="margin:0;font-size:0.9rem;color:var(--text-dark)">
						<strong>Keterangan:</strong> 
						<span style="margin-left:1rem">H = Hadir</span>
						<span style="margin-left:1rem">I = Izin</span>
						<span style="margin-left:1rem">S = Sakit</span>
						<span style="margin-left:1rem">A = Alfa</span>
						<span style="margin-left:1rem">C = Cabut</span>
						<span style="margin-left:1rem">T = Terlambat</span>
					</p>
				</div>

				<!-- Checkbox Hadir Semua -->
				<div style="background:white;padding:1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:1rem">
					<label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;margin:0;flex:1">
						<input type="checkbox" id="checkHadirSemua" onchange="toggleHadirSemua()" style="width:18px;height:18px;cursor:pointer">
						✅ Tandai Hadir Semua
					</label>
				</div>

				<?php
					// Get siswa dari kelas yang dipilih, order by NIS (format: KELAS-NOMOR)
					$kelas_escaped = mysqli_real_escape_string($conn, $kelas_terpilih ?? '');
					$siswa_query = mysqli_query($conn, "SELECT id_siswa, nis, nama_siswa FROM siswa WHERE kelas = '$kelas_escaped' ORDER BY CAST(SUBSTRING_INDEX(nis, '-', -1) AS UNSIGNED) ASC");
					$siswa_list = [];
					while($row = mysqli_fetch_assoc($siswa_query)) {
						$siswa_list[] = $row;
					}
				?>

				<?php if(!empty($siswa_list)): ?>
					<div style="max-height:600px;overflow-y:auto;padding:1.5rem">
						<table style="width:100%;border-collapse:collapse">
							<tbody>
								<?php foreach($siswa_list as $index => $siswa): 
									// Extract nomor dari NIS (format: KELAS-NOMOR)
									$nomor = intval(explode('-', $siswa['nis'])[1] ?? ($index + 1));
								?>
									<tr style="border-bottom:1px solid var(--border);padding:1rem 0">
										<td style="padding:0.75rem 0.5rem;width:8%;text-align:center;font-weight:600;color:var(--text-light)">
											<?= $nomor ?>
										</td>
										<td style="padding:0.75rem 0.5rem;width:42%">
											<div style="display:flex;gap:0.6rem;flex-wrap:wrap">
												<label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem">
													<input type="radio" name="absen_siswa_<?= $siswa['id_siswa'] ?>" class="radio-absen" data-siswa-id="<?= $siswa['id_siswa'] ?>" value="H"> H
												</label>
												<label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem">
													<input type="radio" name="absen_siswa_<?= $siswa['id_siswa'] ?>" class="radio-absen" data-siswa-id="<?= $siswa['id_siswa'] ?>" value="I"> I
												</label>
												<label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem">
													<input type="radio" name="absen_siswa_<?= $siswa['id_siswa'] ?>" class="radio-absen" data-siswa-id="<?= $siswa['id_siswa'] ?>" value="S"> S
												</label>
												<label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem">
													<input type="radio" name="absen_siswa_<?= $siswa['id_siswa'] ?>" class="radio-absen" data-siswa-id="<?= $siswa['id_siswa'] ?>" value="A"> A
												</label>
												<label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem">
													<input type="radio" name="absen_siswa_<?= $siswa['id_siswa'] ?>" class="radio-absen" data-siswa-id="<?= $siswa['id_siswa'] ?>" value="C"> C
												</label>
												<label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem">
													<input type="radio" name="absen_siswa_<?= $siswa['id_siswa'] ?>" class="radio-absen" data-siswa-id="<?= $siswa['id_siswa'] ?>" value="T"> T
												</label>
											</div>
										</td>
										<td style="padding:0.75rem 0.5rem;width:40%">
											<span style="color:var(--text-dark)"><?= htmlspecialchars($siswa['nama_siswa']) ?></span>
										</td>
										<td style="padding:0.75rem 0.5rem;width:10%;text-align:right">
											<button onclick="resetAbsenRadio(this)" style="padding:0.4rem 0.8rem;background:var(--bg-light);color:var(--text-dark);border:1px solid var(--border);border-radius:4px;cursor:pointer;font-size:0.85rem">Reset</button>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div style="background:var(--bg-light);padding:1rem;border-top:1px solid var(--border);text-align:right">
						<button onclick="simpanAbsen()" style="padding:0.75rem 2rem;background:var(--brand);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;transition:all 0.3s">
							💾 Simpan Absen
						</button>
					</div>
				<?php else: ?>
					<div style="padding:2rem;text-align:center;color:var(--text-light)">
						<p style="margin:0">Belum ada data siswa untuk kelas ini</p>
					</div>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	</div>

</div>

<script>
	function updateDateParams() {
		const tanggal = document.getElementById('selectTanggal').value;
		const bulan = document.getElementById('selectBulan').value;
		const tahun = document.getElementById('selectTahun').value;
		const kelas = '<?= $kelas_terpilih ?? '' ?>';
		
		if(tanggal && bulan && tahun && kelas) {
			const params = new URLSearchParams();
			params.set('tab', 'absen_siswa');
			params.set('tanggal', tanggal);
			params.set('bulan', bulan);
			params.set('tahun', tahun);
			params.set('kelas', kelas);
			window.history.pushState({}, '', '?' + params.toString());
			window.location.reload();
		}
	}

	function loadKelasAbsen(kelas) {
		const tanggal = document.getElementById('selectTanggal').value;
		const bulan = document.getElementById('selectBulan').value;
		const tahun = document.getElementById('selectTahun').value;
		
		if(!tanggal || !bulan || !tahun) {
			alert('Silakan pilih tanggal, bulan, dan tahun terlebih dahulu');
			return;
		}
		
		const params = new URLSearchParams();
		params.set('tab', 'absen_siswa');
		params.set('tanggal', tanggal);
		params.set('bulan', bulan);
		params.set('tahun', tahun);
		params.set('kelas', kelas);
		window.history.pushState({}, '', '?' + params.toString());
		window.location.reload();
	}

	function resetAbsenRadio(button) {
		const row = button.closest('tr');
		const radios = row.querySelectorAll('.radio-absen');
		radios.forEach(radio => radio.checked = false);
	}

	function toggleHadirSemua() {
		const checkbox = document.getElementById('checkHadirSemua');
		const radios = document.querySelectorAll('.radio-absen');

		if(checkbox.checked) {
			// Check all radio button "H" (Hadir)
			radios.forEach(radio => {
				if(radio.value === 'H') {
					radio.checked = true;
				}
			});
		} else {
			// Uncheck all radio button "H" (Hadir)
			radios.forEach(radio => {
				if(radio.value === 'H') {
					radio.checked = false;
				}
			});
		}
	}

	function simpanAbsen() {
		const data = [];

		// Collect data from radio buttons
		document.querySelectorAll('.radio-absen:checked').forEach(radio => {
			const siswaId = radio.dataset.siswaId;
			const keterangan = radio.value;
			
			data.push({
				id_siswa: siswaId,
				keterangan: keterangan
			});
		});

		if(data.length === 0) {
			alert('Mohon pilih minimal satu status absensi');
			return;
		}

		fetch('../../backend/absensi/save_absen.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				kelas: '<?= $kelas_terpilih ?>',
				absen: data,
				tanggal: '<?= $tahun_terpilih ?>-<?= $bulan_terpilih ?>-<?= $tanggal_terpilih ?>'
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
