-- Insert Jenis Layanan untuk BK App
-- Jalankan script ini di database bk_app untuk menambahkan jenis-jenis layanan

INSERT INTO `jenis_layanan` (`id_layanan`, `nama_layanan`) VALUES
(1, 'Konseling Individu'),
(2, 'Konseling Kelompok'),
(3, 'Alih Tangan Kasus'),
(4, 'Layanan Konsultasi'),
(5, 'Layanan Mediasi'),
(6, 'Konferensi Kasus'),
(7, 'Layanan Home Visit'),
(8, 'Layanan Manual')
ON DUPLICATE KEY UPDATE `nama_layanan`=VALUES(`nama_layanan`);
