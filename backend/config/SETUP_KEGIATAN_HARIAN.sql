-- ============================================
-- KEGIATAN HARIAN MANUAL - DATABASE SETUP
-- ============================================

-- LANGKAH 1: Jalankan SQL ini di phpMyAdmin atau command line
-- untuk update struktur database

-- Update table guru_bk untuk menambahkan user_id
ALTER TABLE guru_bk 
ADD COLUMN user_id INT AFTER id_guru_bk,
ADD CONSTRAINT fk_guru_bk_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE SET NULL;

-- Update table kegiatan_harian untuk menambahkan field waktu dan guru BK
ALTER TABLE kegiatan_harian 
ADD COLUMN id_guru_bk INT AFTER id_kegiatan,
ADD COLUMN waktu_mulai TIME AFTER tanggal,
ADD COLUMN waktu_selesai TIME AFTER waktu_mulai,
ADD COLUMN keterangan VARCHAR(255) AFTER hasil,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Tambahkan foreign key untuk kegiatan_harian
ALTER TABLE kegiatan_harian 
ADD CONSTRAINT fk_kegiatan_guru_bk FOREIGN KEY (id_guru_bk) REFERENCES guru_bk(id_guru_bk) ON DELETE CASCADE;

-- ============================================
-- LANGKAH 2: Update data guru_bk dengan user_id yang sesuai
-- ============================================

-- Contoh: Update guru_bk dengan id_guru_bk = 1 menjadi terhubung ke users dengan id_user = 2
-- UPDATE guru_bk SET user_id = 2 WHERE id_guru_bk = 1;

-- ============================================
-- VERIFIKASI
-- ============================================

-- Check struktur kegiatan_harian
DESCRIBE kegiatan_harian;

-- Check struktur guru_bk
DESCRIBE guru_bk;

-- Check data kegiatan_harian
SELECT * FROM kegiatan_harian;
