-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for bk_app
CREATE DATABASE IF NOT EXISTS `bk_app` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `bk_app`;

-- Dumping structure for table bk_app.absen_siswa
CREATE TABLE IF NOT EXISTS `absen_siswa` (
  `id_absen` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_absen`),
  UNIQUE KEY `uq_absen` (`id_siswa`,`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.absen_siswa: ~12 rows (approximately)

-- Dumping structure for table bk_app.guru_bk
CREATE TABLE IF NOT EXISTS `guru_bk` (
  `id_guru_bk` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `nip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_guru_bk`),
  UNIQUE KEY `id_user` (`id_user`),
  UNIQUE KEY `nip` (`nip`),
  CONSTRAINT `fk_guru_bk_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.guru_bk: ~1 rows (approximately)
INSERT INTO `guru_bk` (`id_guru_bk`, `id_user`, `nip`, `nama`, `no_telp`, `foto`, `created_at`, `updated_at`) VALUES
	(1, 2, '111', 'taufik', '08111', '/frontend/assets/uploads/guru_bk/guru_bk_1772681146_cafe801d.png', '2026-03-04 07:56:47', '2026-03-05 03:25:46'),
	(2, 3, '222', 'topik', '08222', '/frontend/assets/uploads/guru_bk/guru_bk_1772683311_4102d270.jpg', '2026-03-05 04:01:51', '2026-03-05 04:01:51');

-- Dumping structure for table bk_app.guru_bk_jadwal
CREATE TABLE IF NOT EXISTS `guru_bk_jadwal` (
  `id_jadwal` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_guru_bk` int DEFAULT NULL COMMENT 'NULL = tidak ada guru yang bertugas',
  `ditetapkan_oleh` int DEFAULT NULL COMMENT 'id_user admin yang menetapkan',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_jadwal`),
  UNIQUE KEY `uq_jadwal_tanggal` (`tanggal`),
  KEY `fk_jadwal_guru` (`id_guru_bk`),
  CONSTRAINT `fk_jadwal_guru` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.guru_bk_jadwal: ~0 rows (approximately)
INSERT INTO `guru_bk_jadwal` (`id_jadwal`, `tanggal`, `id_guru_bk`, `ditetapkan_oleh`, `created_at`, `updated_at`) VALUES
	(1, '2026-03-06', NULL, 1, '2026-03-06 04:03:17', '2026-03-06 04:03:17');

-- Dumping structure for table bk_app.kegiatan_harian
CREATE TABLE IF NOT EXISTS `kegiatan_harian` (
  `id_kegiatan` int NOT NULL AUTO_INCREMENT,
  `id_guru_bk` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `uraian_kegiatan` text,
  `jenis_layanan` varchar(255) DEFAULT NULL,
  `sasaran_layanan` varchar(255) DEFAULT NULL,
  `bidang_kode_layanan` varchar(255) DEFAULT NULL,
  `hasil` text,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kegiatan`),
  KEY `fk_kegiatan_guru_bk` (`id_guru_bk`),
  CONSTRAINT `fk_kegiatan_guru_bk` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.kegiatan_harian: ~3 rows (approximately)
INSERT INTO `kegiatan_harian` (`id_kegiatan`, `id_guru_bk`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `uraian_kegiatan`, `jenis_layanan`, `sasaran_layanan`, `bidang_kode_layanan`, `hasil`, `keterangan`, `created_at`, `updated_at`) VALUES
	(1, 1, '2026-02-27', '09:00:00', '10:00:00', 'test', NULL, NULL, NULL, 'test', 'test', '2026-02-27 01:42:50', '2026-02-27 01:42:50'),
	(2, 1, '2026-02-27', '10:00:00', '11:00:00', 'test', NULL, NULL, NULL, 'test', 'test', '2026-02-27 01:42:50', '2026-02-27 01:42:50'),
	(4, 1, '2026-03-02', '11:11:00', '11:11:00', 'test', 'test', 'test', 'test', '', '', '2026-03-01 20:40:02', '2026-03-01 20:41:53'),
	(5, 1, '2026-03-05', '11:11:00', '11:11:00', 't', 't', 't', 't', '', '', '2026-03-05 03:03:16', '2026-03-05 03:03:16'),
	(6, 2, '2026-03-06', '13:01:00', '15:02:00', 'belajar', '', '', '', '', '', '2026-03-06 04:57:45', '2026-03-06 04:57:45');

-- Dumping structure for table bk_app.kelas
CREATE TABLE IF NOT EXISTS `kelas` (
  `id_kelas` int NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_guru_bk` int DEFAULT NULL,
  `id_sekolah` int DEFAULT NULL,
  PRIMARY KEY (`id_kelas`),
  KEY `fk_kelas_guru` (`id_guru_bk`),
  KEY `fk_kelas_sekolah` (`id_sekolah`),
  CONSTRAINT `fk_kelas_guru` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE SET NULL,
  CONSTRAINT `fk_kelas_sekolah` FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.kelas: ~0 rows (approximately)
INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `id_guru_bk`, `id_sekolah`) VALUES
	(1, '7A', 1, 1),
	(2, '7B', 2, 1);

-- Dumping structure for table bk_app.layanan_mediasi
CREATE TABLE IF NOT EXISTS `layanan_mediasi` (
  `id_mediasi` int NOT NULL AUTO_INCREMENT,
  `id_guru_bk` int NOT NULL,
  `tanggal` date NOT NULL,
  `nama_pihak_1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas_pihak_1` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `masalah_pihak_1` text COLLATE utf8mb4_unicode_ci,
  `nama_pihak_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas_pihak_2` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `masalah_pihak_2` text COLLATE utf8mb4_unicode_ci,
  `hasil_mediasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mediasi`),
  KEY `fk_mediasi_guru` (`id_guru_bk`),
  CONSTRAINT `fk_mediasi_guru` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.layanan_mediasi: ~0 rows (approximately)
INSERT INTO `layanan_mediasi` (`id_mediasi`, `id_guru_bk`, `tanggal`, `nama_pihak_1`, `kelas_pihak_1`, `masalah_pihak_1`, `nama_pihak_2`, `kelas_pihak_2`, `masalah_pihak_2`, `hasil_mediasi`, `keterangan`, `foto`, `created_at`, `updated_at`) VALUES
	(1, 1, '2026-03-06', 'test', 'test', 'test', 'test', 'test', 'test', 'test', '', '69aa519b0dc2a_1772769691.jpg', '2026-03-06 04:01:31', '2026-03-06 04:01:31');

-- Dumping structure for table bk_app.penilaian
CREATE TABLE IF NOT EXISTS `penilaian` (
  `id_penilaian` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `scores` json DEFAULT NULL,
  `jumlah_tugas` int DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_penilaian`),
  UNIQUE KEY `uq_penilaian_siswa` (`id_siswa`),
  CONSTRAINT `fk_penilaian_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.penilaian: ~4 rows (approximately)

-- Dumping structure for table bk_app.sekolah
CREATE TABLE IF NOT EXISTS `sekolah` (
  `id_sekolah` int NOT NULL AUTO_INCREMENT,
  `nama_sekolah` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pemerintah` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dinas` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jalan` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kepala_sekolah` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip_kepala_sekolah` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_ajaran` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_sekolah`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.sekolah: ~1 rows (approximately)
INSERT INTO `sekolah` (`id_sekolah`, `nama_sekolah`, `alamat`, `created_at`, `kelas`, `pemerintah`, `dinas`, `jalan`, `kepala_sekolah`, `nip_kepala_sekolah`, `tahun_ajaran`) VALUES
	(1, 'test_s', 'test_almt', '2026-03-05 03:32:10', '7, 8, 9', 'test_p', 'test_d', 'test_jln', 'test_kpsek', '111', '2026/2027');

-- Dumping structure for table bk_app.siswa
CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa` int NOT NULL AUTO_INCREMENT,
  `nama_siswa` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_guru_bk` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nis` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jk` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jurusan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `agama` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sekolah_asal` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ortu` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_ortu` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_siswa`),
  KEY `fk_siswa_guru` (`id_guru_bk`),
  CONSTRAINT `fk_siswa_guru` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.siswa: ~9 rows (approximately)

-- Dumping structure for table bk_app.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','guru_bk') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guru_bk',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.users: ~3 rows (approximately)
INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `is_active`, `created_at`) VALUES
	(1, 'admin', '$2y$10$X/gGiG11K6uvMcudi7vEXe2dudOvE9aVW.P91YOHWyfztiDQkVkIK', 'admin', 1, '2026-03-04 07:18:52'),
	(2, 'taufik', '$2y$10$CAnFKbCflgPpFe83oZj44.2oP0WXESbS357eW.QRNzzhBYEIuY3.C', 'guru_bk', 1, '2026-03-04 07:56:47'),
	(3, 'topik', '$2y$10$rbe4UdMP0YkaetGF.fgF7OTICMpKo4wrBAWNLKpBnPgNDfWB.zBEu', 'guru_bk', 1, '2026-03-05 04:01:51');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
