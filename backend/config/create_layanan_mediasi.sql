-- Tabel untuk menyimpan data Layanan Mediasi
CREATE TABLE IF NOT EXISTS `layanan_mediasi` (
  `id_mediasi` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_guru_bk` int(11) NOT NULL,
  `nama_pihak_1` varchar(100) DEFAULT NULL,
  `kelas_pihak_1` varchar(10) DEFAULT NULL,
  `masalah_pihak_1` text DEFAULT NULL,
  `nama_pihak_2` varchar(100) DEFAULT NULL,
  `kelas_pihak_2` varchar(10) DEFAULT NULL,
  `masalah_pihak_2` text DEFAULT NULL,
  `hasil_mediasi` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `dibuat_pada` timestamp DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mediasi`),
  KEY `id_guru_bk` (`id_guru_bk`),
  KEY `tanggal` (`tanggal`),
  CONSTRAINT `layanan_mediasi_ibfk_1` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
