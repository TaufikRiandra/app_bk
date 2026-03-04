-- ============================================================
-- DATABASE: bk_app
-- Versi baru: relasi users → guru_bk, aktivasi akun, tanpa redundansi
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------
-- 1. USERS  (akun login)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id_user`    INT          NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(100) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,           -- password_hash()
  `role`       ENUM('admin','guru_bk')          NOT NULL DEFAULT 'guru_bk',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 0, -- 0 = menunggu aktivasi
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 2. GURU_BK  (profil / data pribadi guru BK)
--    Terhubung 1-to-1 dengan users via id_user
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `guru_bk` (
  `id_guru_bk` INT          NOT NULL AUTO_INCREMENT,
  `id_user`    INT          NOT NULL UNIQUE,    -- FK ke users
  `nip`        VARCHAR(50)  NOT NULL UNIQUE,
  `nama`       VARCHAR(150) NOT NULL,
  `no_telp`    VARCHAR(20)  DEFAULT NULL,
  `foto`       VARCHAR(255) DEFAULT NULL,       -- path relatif
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_guru_bk`),
  CONSTRAINT `fk_guru_bk_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 3. SEKOLAH
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sekolah` (
  `id_sekolah`   INT          NOT NULL AUTO_INCREMENT,
  `nama_sekolah` VARCHAR(200) NOT NULL,
  `alamat`       TEXT         DEFAULT NULL,
  `tahun_ajaran` VARCHAR(20)  DEFAULT NULL,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sekolah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 4. KELAS  (referensi kelas, di-assign ke guru_bk)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kelas` (
  `id_kelas`   INT         NOT NULL AUTO_INCREMENT,
  `nama_kelas` VARCHAR(50) NOT NULL,
  `id_guru_bk` INT         DEFAULT NULL,        -- wali kelas BK
  `id_sekolah` INT         DEFAULT NULL,
  PRIMARY KEY (`id_kelas`),
  CONSTRAINT `fk_kelas_guru`
    FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE SET NULL,
  CONSTRAINT `fk_kelas_sekolah`
    FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 5. SISWA
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa`   INT          NOT NULL AUTO_INCREMENT,
  `nama_siswa` VARCHAR(150) NOT NULL,
  `nisn`       VARCHAR(20)  DEFAULT NULL,
  `kelas`      VARCHAR(50)  DEFAULT NULL,
  `jenis_kelamin` ENUM('L','P') DEFAULT NULL,
  `tanggal_lahir` DATE       DEFAULT NULL,
  `alamat`     TEXT         DEFAULT NULL,
  `no_telp`    VARCHAR(20)  DEFAULT NULL,
  `id_guru_bk` INT          DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_siswa`),
  CONSTRAINT `fk_siswa_guru`
    FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 6. ABSENSI
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `absensi` (
  `id_absensi` INT  NOT NULL AUTO_INCREMENT,
  `id_siswa`   INT  NOT NULL,
  `tanggal`    DATE NOT NULL,
  `status`     ENUM('hadir','sakit','izin','alpha') NOT NULL DEFAULT 'hadir',
  `keterangan` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_absensi`),
  UNIQUE KEY `uq_absensi` (`id_siswa`, `tanggal`),
  CONSTRAINT `fk_absensi_siswa`
    FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 7. KEGIATAN_HARIAN
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kegiatan_harian` (
  `id_kegiatan` INT          NOT NULL AUTO_INCREMENT,
  `id_guru_bk`  INT          NOT NULL,
  `tanggal`     DATE         NOT NULL,
  `jenis`       VARCHAR(100) DEFAULT NULL,
  `kelas`       VARCHAR(50)  DEFAULT NULL,
  `materi`      TEXT         DEFAULT NULL,
  `keterangan`  TEXT         DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kegiatan`),
  CONSTRAINT `fk_kegiatan_guru`
    FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 8. LAYANAN_MEDIASI
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `layanan_mediasi` (
  `id_mediasi`     INT          NOT NULL AUTO_INCREMENT,
  `id_guru_bk`     INT          NOT NULL,
  `tanggal`        DATE         NOT NULL,
  `nama_pihak_1`   VARCHAR(100) DEFAULT NULL,
  `kelas_pihak_1`  VARCHAR(10)  DEFAULT NULL,
  `masalah_pihak_1` TEXT        DEFAULT NULL,
  `nama_pihak_2`   VARCHAR(100) DEFAULT NULL,
  `kelas_pihak_2`  VARCHAR(10)  DEFAULT NULL,
  `masalah_pihak_2` TEXT        DEFAULT NULL,
  `hasil_mediasi`  VARCHAR(255) DEFAULT NULL,
  `keterangan`     TEXT         DEFAULT NULL,
  `foto`           VARCHAR(255) DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mediasi`),
  CONSTRAINT `fk_mediasi_guru`
    FOREIGN KEY (`id_guru_bk`) REFERENCES `guru_bk` (`id_guru_bk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 9. PENILAIAN
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `penilaian` (
  `id_penilaian` INT  NOT NULL AUTO_INCREMENT,
  `id_siswa`     INT  NOT NULL,
  `scores`       JSON DEFAULT NULL,
  `jumlah_tugas` INT  DEFAULT NULL,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_penilaian`),
  UNIQUE KEY `uq_penilaian_siswa` (`id_siswa`),
  CONSTRAINT `fk_penilaian_siswa`
    FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 10. DATA AWAL: satu akun admin default
-- ---------------------------------------------------------------
INSERT IGNORE INTO `users` (`username`, `password`, `role`, `is_active`)
VALUES ('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- password default: "password"  → ganti setelah install!

SET FOREIGN_KEY_CHECKS = 1;
