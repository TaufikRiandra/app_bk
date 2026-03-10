<?php
// Tutup content-area dan main-layout jika sidebar diinclude
if (isset($GLOBALS['sidebar_included']) && $GLOBALS['sidebar_included']) {
    echo '</section></div>';
}
?>
</main>

<footer style="background:linear-gradient(135deg,#4472C4 0%,#2d5aa0 100%);color:white;margin-top:auto;padding:1.5rem 0">
    <div style="max-width:1400px;margin:0 auto;padding:0 1.5rem">
        
        <!-- Baris Atas -->
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,0.2)">
            
            <!-- Kiri: Brand -->
            <div style="display:flex;align-items:center;gap:0.75rem">
                <div style="width:36px;height:36px;background:rgba(255,255,255,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-hand-holding-heart" style="font-size:1rem;color:white"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:1rem;letter-spacing:.3px">Sistem BK</div>
                    <div style="font-size:0.75rem;opacity:0.8">Bimbingan & Konseling Sekolah</div>
                </div>
            </div>

            <!-- Tengah: Nav Links -->
            <div style="display:flex;gap:1.5rem;font-size:0.85rem">
                <a href="/frontend/dashboard.php" style="color:rgba(255,255,255,0.85);text-decoration:none;transition:color .2s"
                   onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.85)'">
                    <i class="fas fa-home" style="margin-right:4px"></i>Dashboard
                </a>
                <a href="/frontend/pages/siswa/index.php" style="color:rgba(255,255,255,0.85);text-decoration:none"
                   onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.85)'">
                    <i class="fas fa-users" style="margin-right:4px"></i>Data Siswa
                </a>
                <a href="/frontend/pages/kegiatan_harian_manual.php" style="color:rgba(255,255,255,0.85);text-decoration:none"
                   onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.85)'">
                    <i class="fas fa-calendar-check" style="margin-right:4px"></i>Kegiatan
                </a>
            </div>

            <!-- Kanan: Developer -->
            <div style="text-align:right;font-size:0.8rem;opacity:0.9">
                <div style="font-size:0.72rem;opacity:0.75;margin-bottom:2px;text-transform:uppercase;letter-spacing:.5px">Dikembangkan oleh</div>
                <div style="font-weight:400;font-size:0.95rem;display:flex;align-items:center;gap:6px;justify-content:flex-end">
                    <i class="fas fa-code" style="font-size:0.8rem;opacity:0.8"></i>
                    Taufik Riandra & Zikhrul Azmi
                </div>
            </div>
        </div>

        <!-- Baris Bawah -->
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;padding-top:0.85rem;font-size:0.78rem;opacity:0.75">
            <span>© <?= date('Y') ?> Sistem Bimbingan Konseling Sekolah. All rights reserved.</span>
            <span style="display:flex;align-items:center;gap:4px">
                <i class="fas fa-shield-alt" style="font-size:0.75rem"></i>
                Sistem terlindungi & terenkripsi
            </span>
        </div>

    </div>
</footer>

</body>
</html>