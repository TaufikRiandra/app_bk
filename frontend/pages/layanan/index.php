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

$sekolah_result = mysqli_query($conn, "SELECT * FROM sekolah LIMIT 1");
$data_sekolah = mysqli_fetch_assoc($sekolah_result);

$layanan = [
    [
        'judul'  => '(Maintenance) Konseling Individu',
        'icon'   => 'fa-solid fa-user',
        'warna'  => '#4472C4',
        'desc'   => 'Layanan konseling secara perorangan antara konselor dan siswa',
        'url'    => '#', // ganti URL sesuai kebutuhan
    ],
    [
        'judul'  => '(Maintenance) Konseling Kelompok',
        'icon'   => 'fa-solid fa-users',
        'warna'  => '#2e7d32',
        'desc'   => 'Layanan konseling dalam setting kelompok kecil',
        'url'    => '#',
    ],
    [
        'judul'  => '(Maintenance) Alih Tangan Kasus',
        'icon'   => 'fa-solid fa-right-left',
        'warna'  => '#c0392b',
        'desc'   => 'Pelimpahan penanganan kasus kepada pihak yang lebih berwenang',
        'url'    => '#',
    ],
    [
        'judul'  => '(Maintenance) Layanan Konsultasi',
        'icon'   => 'fa-solid fa-comments',
        'warna'  => '#7b1fa2',
        'desc'   => 'Konsultasi antara konselor dengan pihak terkait (orang tua, guru)',
        'url'    => '#',
    ],
    [
        'judul'  => 'Layanan Mediasi',
        'icon'   => 'fa-solid fa-handshake',
        'warna'  => '#e65100',
        'desc'   => 'Fasilitasi penyelesaian konflik antar pihak oleh konselor',
        'url'    => 'layanan_mediasi_manual.php',
    ],
    [
        'judul'  => '(Maintenance) Konferensi Kasus',
        'icon'   => 'fa-solid fa-people-arrows',
        'warna'  => '#00838f',
        'desc'   => 'Pertemuan bersama untuk membahas penanganan kasus siswa',
        'url'    => '#',
    ],
    [
        'judul'  => '(Maintenance) Bimbingan Kelompok',
        'icon'   => 'fa-solid fa-chalkboard-user',
        'warna'  => '#558b2f',
        'desc'   => 'Layanan bimbingan yang diberikan kepada sekelompok siswa',
        'url'    => '#',
    ],
    [
        'judul'  => '(Maintenance) Home Visit',
        'icon'   => 'fa-solid fa-house-chimney-user',
        'warna'  => '#6d4c41',
        'desc'   => 'Kunjungan rumah untuk memperoleh data dan tindak lanjut',
        'url'    => '#',
    ],
];
?>

<!-- Header -->
<div style="background:var(--brand,#4472C4);color:white;padding:1.25rem 1.5rem;border-radius:8px;margin-bottom:1.25rem">
    <h2 style="margin:0 0 4px;font-size:1.1rem;font-weight:700">
        <i class="fa-solid fa-layer-group"></i> LAYANAN BK
    </h2>
    <p style="margin:0;font-size:.85rem;opacity:.9">Pilih jenis layanan Bimbingan dan Konseling</p>
    <p style="margin:4px 0 0;font-size:.85rem;opacity:.85">
        <?= htmlspecialchars($data_sekolah['nama_sekolah'] ?? 'Sekolah') ?>
    </p>
</div>

<section class="card">

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem">
        <?php foreach($layanan as $item): ?>
        <a href="<?= htmlspecialchars($item['url']) ?>"
           style="text-decoration:none;display:flex;flex-direction:column;border-radius:10px;overflow:hidden;border:1px solid var(--border,#e0e0e0);background:var(--surface,#fff);box-shadow:0 1px 4px rgba(0,0,0,.07);transition:transform .15s,box-shadow .15s"
           onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,.12)'"
           onmouseout="this.style.transform='';this.style.boxShadow='0 1px 4px rgba(0,0,0,.07)'">

            <!-- Strip warna + ikon -->
            <div style="background:<?= $item['warna'] ?>;padding:1.5rem;display:flex;align-items:center;justify-content:center">
                <i class="<?= $item['icon'] ?>" style="font-size:2.2rem;color:white"></i>
            </div>

            <!-- Konten -->
            <div style="padding:1.1rem 1.25rem 1.25rem;flex:1;display:flex;flex-direction:column;gap:.4rem">
                <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--text-dark,#1a1a1a)">
                    <?= htmlspecialchars($item['judul']) ?>
                </h3>
                <p style="margin:0;font-size:.82rem;color:var(--text-light,#666);line-height:1.5;flex:1">
                    <?= htmlspecialchars($item['desc']) ?>
                </p>
                <div style="margin-top:.75rem;display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;color:<?= $item['warna'] ?>">
                    Buka <i class="fa-solid fa-arrow-right" style="font-size:.75rem"></i>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

</section>

<div id="toastBelumAda" style="display:none;position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);background:#1a1a1a;color:white;padding:0.75rem 1.5rem;border-radius:8px;font-size:0.9rem;font-weight:500;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,0.25);display:flex;align-items:center;gap:0.6rem;opacity:0;transition:opacity .3s">
    <i class="fa-solid fa-clock" style="color:#f59e0b"></i> Fungsi segera ditambahkan
</div>

<script>
document.querySelectorAll('a[href="#"]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        const toast = document.getElementById('toastBelumAda');
        toast.style.display = 'flex';
        setTimeout(() => toast.style.opacity = '1', 10);
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 300);
        }, 2500);
    });
});
</script>

<?php include "../../layouts/footer.php"; ?>