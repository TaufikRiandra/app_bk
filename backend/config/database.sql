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
  `id_siswa` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` enum('Hadir','Izin','Sakit','Alfa','Cabut','Terlambat') DEFAULT NULL,
  PRIMARY KEY (`id_absen`),
  KEY `id_siswa` (`id_siswa`),
  CONSTRAINT `absen_siswa_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.absen_siswa: ~5 rows (approximately)
INSERT INTO `absen_siswa` (`id_absen`, `id_siswa`, `tanggal`, `keterangan`) VALUES
	(55, 30, '2026-01-01', 'Hadir'),
	(56, 31, '2026-01-01', 'Hadir'),
	(57, 32, '2026-01-01', 'Hadir'),
	(58, 33, '2026-01-01', 'Hadir'),
	(59, 34, '2026-01-01', 'Hadir'),
	(60, 35, '2026-01-01', 'Hadir'),
	(61, 36, '2026-01-01', 'Hadir'),
	(62, 37, '2026-01-01', 'Hadir'),
	(63, 38, '2026-01-01', 'Hadir'),
	(64, 39, '2026-01-01', 'Hadir'),
	(65, 40, '2026-01-01', 'Hadir'),
	(66, 41, '2026-01-01', 'Hadir'),
	(67, 42, '2026-01-01', 'Hadir'),
	(68, 43, '2026-01-01', 'Hadir'),
	(69, 44, '2026-01-01', 'Hadir'),
	(70, 45, '2026-01-01', 'Hadir'),
	(71, 46, '2026-01-01', 'Hadir'),
	(72, 47, '2026-01-01', 'Hadir'),
	(73, 48, '2026-01-01', 'Hadir'),
	(74, 49, '2026-01-01', 'Hadir'),
	(75, 50, '2026-01-01', 'Hadir'),
	(76, 51, '2026-01-01', 'Hadir'),
	(77, 52, '2026-01-01', 'Hadir'),
	(78, 53, '2026-01-01', 'Hadir'),
	(79, 54, '2026-01-01', 'Hadir'),
	(80, 55, '2026-01-01', 'Hadir'),
	(81, 56, '2026-01-01', 'Hadir'),
	(82, 57, '2026-01-01', 'Hadir'),
	(85, 30, '2026-01-02', 'Hadir'),
	(86, 31, '2026-01-02', 'Hadir'),
	(87, 32, '2026-01-02', 'Hadir'),
	(88, 33, '2026-01-02', 'Hadir'),
	(89, 34, '2026-01-02', 'Hadir'),
	(90, 35, '2026-01-02', 'Hadir'),
	(91, 36, '2026-01-02', 'Hadir'),
	(92, 37, '2026-01-02', 'Hadir'),
	(93, 38, '2026-01-02', 'Hadir'),
	(94, 39, '2026-01-02', 'Hadir'),
	(95, 40, '2026-01-02', 'Hadir'),
	(96, 41, '2026-01-02', 'Hadir'),
	(97, 42, '2026-01-02', 'Hadir'),
	(98, 43, '2026-01-02', 'Hadir'),
	(99, 44, '2026-01-02', 'Hadir'),
	(100, 45, '2026-01-02', 'Hadir'),
	(101, 46, '2026-01-02', 'Hadir'),
	(102, 47, '2026-01-02', 'Hadir'),
	(103, 48, '2026-01-02', 'Hadir'),
	(104, 49, '2026-01-02', 'Hadir'),
	(105, 50, '2026-01-02', 'Hadir'),
	(106, 51, '2026-01-02', 'Hadir'),
	(107, 52, '2026-01-02', 'Hadir'),
	(108, 53, '2026-01-02', 'Hadir'),
	(109, 54, '2026-01-02', 'Hadir'),
	(110, 55, '2026-01-02', 'Hadir'),
	(111, 56, '2026-01-02', 'Hadir'),
	(112, 57, '2026-01-02', 'Hadir');

-- Dumping structure for table bk_app.guru_bk
CREATE TABLE IF NOT EXISTS `guru_bk` (
  `id_guru_bk` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nip` varchar(25) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_guru_bk`),
  UNIQUE KEY `nip` (`nip`),
  KEY `fk_guru_bk_user` (`user_id`),
  CONSTRAINT `fk_guru_bk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.guru_bk: ~0 rows (approximately)
INSERT INTO `guru_bk` (`id_guru_bk`, `user_id`, `nip`, `nama`, `no_telp`, `foto`, `created_at`) VALUES
	(1, NULL, '111', 'test_bk', '08111', '/frontend/assets/uploads/guru_bk/guru_bk_1771912729_3f8edca4.png', '2026-02-24 05:58:49');

-- Dumping structure for table bk_app.guru_bk_kelas
CREATE TABLE IF NOT EXISTS `guru_bk_kelas` (
  `id_bk` int NOT NULL AUTO_INCREMENT,
  `id_sekolah` int NOT NULL,
  `id_guru_bk` int NOT NULL,
  `kelas` varchar(10) NOT NULL,
  PRIMARY KEY (`id_bk`),
  UNIQUE KEY `unique_sekolah_kelas` (`id_sekolah`,`kelas`),
  KEY `guru_bk_kelas_ibfk_2` (`id_guru_bk`),
  CONSTRAINT `guru_bk_kelas_ibfk_1` FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`) ON DELETE CASCADE,
  CONSTRAINT `guru_bk_kelas_ibfk_2` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.guru_bk_kelas: ~0 rows (approximately)
INSERT INTO `guru_bk_kelas` (`id_bk`, `id_sekolah`, `id_guru_bk`, `kelas`) VALUES
	(1, 2, 1, '7A');

-- Dumping structure for table bk_app.jenis_layanan
CREATE TABLE IF NOT EXISTS `jenis_layanan` (
  `id_layanan` int NOT NULL AUTO_INCREMENT,
  `nama_layanan` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_layanan`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.jenis_layanan: ~8 rows (approximately)
INSERT INTO `jenis_layanan` (`id_layanan`, `nama_layanan`) VALUES
	(1, 'Konseling Individu'),
	(2, 'Konseling Kelompok'),
	(3, 'Alih Tangan Kasus'),
	(4, 'Layanan Konsultasi'),
	(5, 'Layanan Mediasi'),
	(6, 'Konferensi Kasus'),
	(7, 'Layanan Home Visit'),
	(8, 'Layanan Manual');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.kegiatan_harian: ~3 rows (approximately)
INSERT INTO `kegiatan_harian` (`id_kegiatan`, `id_guru_bk`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `uraian_kegiatan`, `jenis_layanan`, `sasaran_layanan`, `bidang_kode_layanan`, `hasil`, `keterangan`, `created_at`, `updated_at`) VALUES
	(1, 1, '2026-02-27', '09:00:00', '10:00:00', 'test', NULL, NULL, NULL, 'test', 'test', '2026-02-27 08:42:50', '2026-02-27 08:42:50'),
	(2, 1, '2026-02-27', '10:00:00', '11:00:00', 'test', NULL, NULL, NULL, 'test', 'test', '2026-02-27 08:42:50', '2026-02-27 08:42:50'),
	(4, 1, '2026-03-02', '11:11:00', '11:11:00', 'test', 'test', 'test', 'test', '', '', '2026-03-02 03:40:02', '2026-03-02 03:41:53');

-- Dumping structure for table bk_app.layanan_mediasi
CREATE TABLE IF NOT EXISTS `layanan_mediasi` (
  `id_mediasi` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_guru_bk` int NOT NULL,
  `nama_pihak_1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas_pihak_1` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `masalah_pihak_1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nama_pihak_2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas_pihak_2` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `masalah_pihak_2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `hasil_mediasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mediasi`),
  KEY `id_guru_bk` (`id_guru_bk`),
  KEY `tanggal` (`tanggal`),
  CONSTRAINT `layanan_mediasi_ibfk_1` FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bk_app.layanan_mediasi: ~0 rows (approximately)
INSERT INTO `layanan_mediasi` (`id_mediasi`, `tanggal`, `id_guru_bk`, `nama_pihak_1`, `kelas_pihak_1`, `masalah_pihak_1`, `nama_pihak_2`, `kelas_pihak_2`, `masalah_pihak_2`, `hasil_mediasi`, `keterangan`, `foto`, `dibuat_pada`, `diubah_pada`) VALUES
	(1, '2026-03-03', 1, 'test', 'test', 'test', 'test', 'test', 'test', 'test', '', '69a675412e10b_1772516673.jpg', '2026-03-03 05:44:33', '2026-03-03 05:44:33'),
	(2, '2026-03-03', 1, 'a', 'a', 'a', 'a', 'a', 'a', 'a', '', '', '2026-03-03 06:14:52', '2026-03-03 06:14:52'),
	(3, '2026-03-03', 1, 'b', 'b', 'b', 'b', 'b', 'b', 'b', '', '', '2026-03-03 06:16:30', '2026-03-03 06:17:07');

-- Dumping structure for table bk_app.penilaian
CREATE TABLE IF NOT EXISTS `penilaian` (
  `id_penilaian` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `scores` json DEFAULT NULL,
  `jumlah_tugas` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_penilaian`),
  UNIQUE KEY `unique_siswa` (`id_siswa`),
  CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.penilaian: ~28 rows (approximately)
INSERT INTO `penilaian` (`id_penilaian`, `id_siswa`, `scores`, `jumlah_tugas`, `updated_at`) VALUES
	(1, 31, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(2, 35, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(3, 30, '[1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(4, 32, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(5, 34, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(6, 33, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(7, 36, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(8, 37, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(9, 38, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(10, 39, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(11, 40, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(12, 41, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(13, 42, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(14, 43, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(15, 44, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(16, 45, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(17, 46, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(18, 47, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(19, 48, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(20, 49, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(21, 50, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(22, 51, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(23, 52, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(24, 53, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(25, 54, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(26, 55, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(27, 56, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01'),
	(28, 57, '[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]', 15, '2026-03-02 08:05:01');

-- Dumping structure for table bk_app.rekap_layanan
CREATE TABLE IF NOT EXISTS `rekap_layanan` (
  `id_rekap` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int DEFAULT NULL,
  `id_layanan` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `permasalahan` text,
  `tindak_lanjut` text,
  `hasil` text,
  PRIMARY KEY (`id_rekap`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_layanan` (`id_layanan`),
  CONSTRAINT `rekap_layanan_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  CONSTRAINT `rekap_layanan_ibfk_2` FOREIGN KEY (`id_layanan`) REFERENCES `jenis_layanan` (`id_layanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.rekap_layanan: ~0 rows (approximately)

-- Dumping structure for table bk_app.sekolah
CREATE TABLE IF NOT EXISTS `sekolah` (
  `id_sekolah` int NOT NULL AUTO_INCREMENT,
  `pemerintah` varchar(100) DEFAULT NULL,
  `dinas` varchar(100) DEFAULT NULL,
  `nama_sekolah` varchar(100) DEFAULT NULL,
  `alamat` text,
  `jalan` varchar(150) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `tahun_pelajaran` varchar(20) DEFAULT NULL,
  `kepala_sekolah` varchar(100) DEFAULT NULL,
  `nip_kepala_sekolah` varchar(25) DEFAULT NULL,
  `guru_bk` varchar(100) DEFAULT NULL,
  `nip_guru_bk` varchar(25) DEFAULT NULL,
  `tahun_ajaran` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_sekolah`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.sekolah: ~0 rows (approximately)
INSERT INTO `sekolah` (`id_sekolah`, `pemerintah`, `dinas`, `nama_sekolah`, `alamat`, `jalan`, `kelas`, `tahun_pelajaran`, `kepala_sekolah`, `nip_kepala_sekolah`, `guru_bk`, `nip_guru_bk`, `tahun_ajaran`) VALUES
	(2, 'test_p', 'test_d', 'test_s', 'test_a', 'test_jln', '7, 8, 9', '2026/02/28', 'test_kpsek', '111', NULL, NULL, '');

-- Dumping structure for table bk_app.siswa
CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa` int NOT NULL AUTO_INCREMENT,
  `nis` varchar(20) DEFAULT NULL,
  `nama_siswa` varchar(100) DEFAULT NULL,
  `jk` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `sekolah_asal` varchar(100) DEFAULT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text,
  `nama_ortu` varchar(100) DEFAULT NULL,
  `no_hp_ortu` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_siswa`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.siswa: ~0 rows (approximately)
INSERT INTO `siswa` (`id_siswa`, `nis`, `nama_siswa`, `jk`, `tempat_lahir`, `tgl_lahir`, `agama`, `sekolah_asal`, `kelas`, `jurusan`, `no_hp`, `alamat`, `nama_ortu`, `no_hp_ortu`) VALUES
	(30, '7A-001', 'a', 'L', 'test', '2007-02-02', 'Islam', 'test', '7A', NULL, '08123', 'test', 'test', '08123'),
	(31, '7A-002', 'b', 'L', '', '2024-09-02', '', '', '7A', NULL, '', '', '', ''),
	(32, '7A-003', 'c', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(33, '7A-004', 'd', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(34, '7A-005', 'e', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(35, '7A-006', 'f', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(36, '7A-007', 'g', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(37, '7A-008', 'h', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(38, '7A-009', 'i', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(39, '7A-010', 'j', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(40, '7A-011', 'k', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(41, '7A-012', 'l', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(42, '7A-013', 'm', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(43, '7A-014', 'n', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(44, '7A-015', 'o', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(45, '7A-016', 'p', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(46, '7A-017', 'q', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(47, '7A-018', 'r', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(48, '7A-019', 's', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(49, '7A-020', 't', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(50, '7A-021', 'u', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(51, '7A-022', 'v', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(52, '7A-023', 'w', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(53, '7A-024', 'x', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(54, '7A-025', 'y', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(55, '7A-026', 'z', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(56, '7A-027', 'aa', 'L', '', NULL, '', '', '7A', NULL, '', '', '', ''),
	(57, '7A-028', 'aa', 'L', '', NULL, '', '', '7A', NULL, '', '', '', '');

-- Dumping structure for table bk_app.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','guru_bk') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'admin',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table bk_app.users: ~2 rows (approximately)
INSERT INTO `users` (`id_user`, `username`, `password`, `role`) VALUES
	(3, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin'),
	(4, 'gurubk', 'e10adc3949ba59abbe56e057f20f883e', 'guru_bk');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
