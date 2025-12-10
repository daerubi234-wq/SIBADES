-- Skema Database: SI_PUSBAN
-- Created: 2025-12-09
-- Database untuk Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa

CREATE DATABASE IF NOT EXISTS `si_pusban` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `si_pusban`;

-- ====================================
-- Tabel Konfigurasi Desa
-- ====================================
CREATE TABLE `config_desa` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nama_desa` VARCHAR(100) NOT NULL,
  `kecamatan` VARCHAR(100) NOT NULL,
  `kabupaten` VARCHAR(100) NOT NULL,
  `provinsi` VARCHAR(100) NOT NULL,
  `tahun_anggaran` YEAR NOT NULL,
  `logo_url` VARCHAR(255) NULL,
  `deskripsi` TEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_desa` (`nama_desa`, `kecamatan`, `kabupaten`)
);

-- ====================================
-- Tabel Pengguna (Users)
-- ====================================
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `no_wa` VARCHAR(15) UNIQUE NOT NULL,
  `role` ENUM('user', 'operator', 'admin') NOT NULL DEFAULT 'user',
  `is_active` BOOLEAN DEFAULT FALSE,
  `last_login` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_role` (`role`),
  INDEX `idx_is_active` (`is_active`)
);

-- ====================================
-- Tabel Usulan Bantuan
-- ====================================
CREATE TABLE `usulan` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `nik` VARCHAR(16) UNIQUE NOT NULL,
  `no_kk` VARCHAR(16) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `jenis_kelamin` ENUM('Laki-laki', 'Perempuan') NOT NULL,
  `alamat_lengkap` TEXT NOT NULL,
  `jenis_bantuan` VARCHAR(100) NOT NULL,
  `deskripsi_bantuan` TEXT NULL,
  `status` ENUM('Antri', 'Ditinjau', 'Disetujui', 'Ditolak') DEFAULT 'Antri',
  `alasan_penolakan` TEXT NULL,
  `catatan_admin` TEXT NULL,
  `tgl_usulan` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `tgl_diproses` DATETIME NULL,
  `verified_by` INT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_nik` (`nik`),
  INDEX `idx_status` (`status`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_tgl_usulan` (`tgl_usulan`)
);

-- ====================================
-- Tabel Dokumen Usulan
-- ====================================
CREATE TABLE `dokumen_usulan` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `usulan_id` INT NOT NULL,
  `tipe_dokumen` VARCHAR(50) NOT NULL, -- KTP, KK, RMH_DEPAN, RMH_SAMPING, RMH_DALAM
  `cloud_url` TEXT NOT NULL, -- URL file di Google Drive/Mega
  `nama_file_asli` VARCHAR(255) NOT NULL, -- Nama file original
  `nama_file_di_cloud` VARCHAR(255) NOT NULL, -- NIK_NamaLengkap_KTP.jpg
  `ukuran_file` INT NULL, -- Ukuran file dalam bytes
  `tipe_file` VARCHAR(20) NOT NULL, -- jpg, png, pdf, etc.
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usulan_id`) REFERENCES `usulan`(`id`) ON DELETE CASCADE,
  INDEX `idx_usulan_id` (`usulan_id`),
  INDEX `idx_tipe_dokumen` (`tipe_dokumen`)
);

-- ====================================
-- Tabel Verifikasi OTP (SMS/WhatsApp)
-- ====================================
CREATE TABLE `otp_verification` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NULL,
  `no_wa` VARCHAR(15) NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `is_verified` BOOLEAN DEFAULT FALSE,
  `attempt_count` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME,
  INDEX `idx_no_wa` (`no_wa`),
  INDEX `idx_user_id` (`user_id`)
);

-- ====================================
-- Tabel Captcha Session (Security)
-- ====================================
CREATE TABLE `captcha_session` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `session_id` VARCHAR(100) UNIQUE NOT NULL,
  `captcha_code` VARCHAR(6) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME,
  INDEX `idx_session_id` (`session_id`)
);

-- ====================================
-- Tabel Notifikasi & Komunikasi
-- ====================================
CREATE TABLE `notifikasi` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `isi` TEXT NOT NULL,
  `tipe_notifikasi` ENUM('Info', 'Warning', 'Success', 'Error') DEFAULT 'Info',
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_read` (`is_read`)
);

-- ====================================
-- Tabel Log Aktivitas
-- ====================================
CREATE TABLE `activity_log` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `action` VARCHAR(100) NOT NULL, -- Login, Create, Update, Delete, Approve, etc.
  `tabel_target` VARCHAR(100) NOT NULL,
  `record_id` INT NULL,
  `deskripsi` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_created_at` (`created_at`)
);

-- ====================================
-- Tabel Modul Pendataan Dasar
-- ====================================
CREATE TABLE `pendataan_jenis` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nama_jenis` VARCHAR(100) NOT NULL UNIQUE, -- Disabilitas, Lansia, Rumah, Guru Ngaji, etc.
  `deskripsi` TEXT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_nama` (`nama_jenis`)
);

CREATE TABLE `pendataan_data` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `usulan_id` INT NOT NULL,
  `jenis_id` INT NOT NULL,
  `detail_data` TEXT NOT NULL, -- JSON atau text details
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`usulan_id`) REFERENCES `usulan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`jenis_id`) REFERENCES `pendataan_jenis`(`id`) ON DELETE CASCADE,
  INDEX `idx_usulan_id` (`usulan_id`),
  INDEX `idx_jenis_id` (`jenis_id`)
);

-- ====================================
-- Data Seed: Konfigurasi Desa Awal
-- ====================================
INSERT INTO `config_desa` (`nama_desa`, `kecamatan`, `kabupaten`, `provinsi`, `tahun_anggaran`) 
VALUES ('Desa Contoh', 'Kecamatan Contoh', 'Kabupaten Contoh', 'Provinsi Contoh', 2025);

-- ====================================
-- Data Seed: Jenis Pendataan Dasar
-- ====================================
INSERT INTO `pendataan_jenis` (`nama_jenis`, `deskripsi`, `is_active`) VALUES
('Disabilitas', 'Data penduduk dengan kondisi disabilitas', TRUE),
('Lansia', 'Data penduduk lansia (usia 60+ tahun)', TRUE),
('Rumah dan Tempat Ibadah', 'Data kondisi rumah dan tempat ibadah', TRUE),
('Guru Ngaji', 'Data guru mengaji/tenaga pengajar', TRUE);

-- ====================================
-- Create Indexes untuk Performa
-- ====================================
CREATE INDEX `idx_users_role_active` ON `users` (`role`, `is_active`);
CREATE INDEX `idx_usulan_status_user` ON `usulan` (`status`, `user_id`);
CREATE INDEX `idx_notifikasi_user_read` ON `notifikasi` (`user_id`, `is_read`);
