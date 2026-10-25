-- ============================================================
-- Database: db_mahasiswa_advance
-- Proyek: Sistem Informasi Biodata Mahasiswa Advance (FIKOM UDB)
-- Arsitektur: Native PHP, MySQLi Prepared Statement, Soft Delete & Audit Logs
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_mahasiswa_advance` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `db_mahasiswa_advance`;

-- 1. TABEL USERS (Autentikasi & Hak Akses)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'mahasiswa') NOT NULL DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABEL PRODI (Program Studi)
CREATE TABLE IF NOT EXISTS `prodi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_prodi` VARCHAR(100) NOT NULL,
  `fakultas` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABEL MAHASISWA (Biodata Utama, Koordinat Spasial & Soft Delete)
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `id_prodi` INT NOT NULL,
  `nim` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(150) NOT NULL,
  `jenis_kelamin` ENUM('Laki-laki', 'Perempuan') NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `alamat` TEXT NOT NULL,
  `latitude` DECIMAL(10, 8) DEFAULT NULL,
  `longitude` DECIMAL(11, 8) DEFAULT NULL,
  `angkatan` YEAR DEFAULT 2026,
  `status_akademik` ENUM('Aktif', 'Cuti', 'Lulus', 'DO') NOT NULL DEFAULT 'Aktif',
  `dpa` VARCHAR(150) DEFAULT 'Dr. Muhtarom, M.Kom',
  `foto` VARCHAR(255) DEFAULT NULL,
  `dokumen` VARCHAR(255) DEFAULT NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_is_deleted` (`is_deleted`),
  INDEX `idx_nim` (`nim`),
  CONSTRAINT `fk_mhs_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `prodi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mhs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABEL AUDIT_LOGS (Pencatatan Riwayat Aktivitas & Perubahan Data)
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `table_name` VARCHAR(50) NOT NULL,
  `record_id` INT NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_action` (`action`),
  INDEX `idx_record` (`table_name`, `record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA AWAL
-- ============================================================

-- A. Data Akun Pengguna Awal (Password default: 'admin123' bcrypt hash)
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2y$10$wT0l8oVf3Zl5VdZ9eYwX9.y8V4U9i5tJ6P1bS1k3yG1.YQe1d1iXe', 'Administrator Akademik FIKOM', 'admin'),
(2, 'arjuna', '$2y$10$wT0l8oVf3Zl5VdZ9eYwX9.y8V4U9i5tJ6P1bS1k3yG1.YQe1d1iXe', 'Arjuna', 'mahasiswa')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- B. Data Program Studi
INSERT INTO `prodi` (`id`, `nama_prodi`, `fakultas`) VALUES
(1, 'S1 Teknik Informatika', 'Fakultas Ilmu Komputer'),
(2, 'S1 Sistem Informasi', 'Fakultas Ilmu Komputer')
ON DUPLICATE KEY UPDATE `nama_prodi` = VALUES(`nama_prodi`);

-- C. Data Mahasiswa Awal (Arjuna, Bima, Drupadi, Yudistira)
INSERT INTO `mahasiswa` (`id`, `user_id`, `id_prodi`, `nim`, `nama`, `jenis_kelamin`, `email`, `no_hp`, `alamat`, `latitude`, `longitude`, `angkatan`, `status_akademik`, `dpa`, `foto`, `dokumen`, `is_deleted`) VALUES
(1, 2, 1, '26', 'Arjuna', 'Laki-laki', 'arjuna@email.com', '081234567890', 'Jl. Merdeka No. 10, Surakarta', -7.55800000, 110.82700000, 2026, 'Aktif', 'Dr. Muhtarom, M.Kom', NULL, NULL, 0),
(2, NULL, 2, '27', 'Bima', 'Laki-laki', 'bima@email.com', '081234567891', 'Jl. Slamet Riyadi No. 112, Surakarta', -7.56500000, 110.81500000, 2026, 'Aktif', 'Dr. Muhtarom, M.Kom', NULL, NULL, 0),
(3, NULL, 2, '28', 'Drupadi', 'Perempuan', 'drupadi@email.com', '081234567892', 'Jl. Adi Sucipto No. 45, Surakarta', -7.54200000, 110.78000000, 2026, 'Aktif', 'Dr. Muhtarom, M.Kom', NULL, NULL, 0),
(4, NULL, 1, '29', 'Yudistira', 'Laki-laki', 'yudistira@email.com', '081234567893', 'Jl. Ir. Sutami No. 88, Surakarta', -7.55900000, 110.85500000, 2026, 'Aktif', 'Dr. Muhtarom, M.Kom', NULL, NULL, 0)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- D. Initial Audit Log
INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `description`) VALUES
(1, 'INIT', 'database', 1, 'Inisialisasi sistem basis data db_mahasiswa_advance dengan 4 data mahasiswa awal.');

