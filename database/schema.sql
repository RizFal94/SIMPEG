
CREATE DATABASE IF NOT EXISTS `simpeg_ujikom`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `simpeg_ujikom`;

CREATE TABLE IF NOT EXISTS `employees` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nip`                 VARCHAR(20)  NOT NULL,
    `nama`                VARCHAR(100) NOT NULL,
    `jenis_kelamin`       ENUM('L', 'P') NOT NULL,
    `tanggal_lahir`       DATE NOT NULL,
    `pendidikan_terakhir` ENUM('SMA/SMK', 'D3', 'S1', 'S2', 'S3') NOT NULL,
    `jabatan`             VARCHAR(100) NOT NULL,
    `departemen`          VARCHAR(50)  NOT NULL,
    `email`               VARCHAR(100) NOT NULL,
    `telepon`             VARCHAR(20)  NOT NULL,
    `alamat`              VARCHAR(255) NULL,
    `tanggal_masuk`       DATE NOT NULL,
    `status`              ENUM('Aktif', 'Cuti', 'Nonaktif') NOT NULL DEFAULT 'Aktif',
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_employees_nip` (`nip`),
    UNIQUE KEY `uq_employees_email` (`email`),
    KEY `idx_employees_nama` (`nama`),
    KEY `idx_employees_gender` (`jenis_kelamin`),
    KEY `idx_employees_education` (`pendidikan_terakhir`),
    KEY `idx_employees_status` (`status`),
    KEY `idx_employees_masuk` (`tanggal_masuk`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Data master pegawai';
