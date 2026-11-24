-- ====================================================================
-- SISTEM PENGELOLAAN LAUNDRY (APSI KELOMPOK 2)
-- Database: db_laundry_apsi
-- Memenuhi 7 Data Store (D1 - D7) Sesuai Spesifikasi DFD
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `db_laundry_apsi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_laundry_apsi`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `riwayat_status`;
DROP TABLE IF EXISTS `antar_jemput`;
DROP TABLE IF EXISTS `rincian_item`;
DROP TABLE IF EXISTS `pesanan`;
DROP TABLE IF EXISTS `cucian_tanpa_pemilik`;
DROP TABLE IF EXISTS `akun_pengguna`;
DROP TABLE IF EXISTS `pelanggan`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- D1: Pelanggan
-- Menyimpan: nama, no_hp, alamat (diinput oleh Pemilik)
-- --------------------------------------------------------------------
CREATE TABLE `pelanggan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(25) NOT NULL UNIQUE,
  `alamat` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- D6: Akun Pengguna
-- Menyimpan: username, password (bcrypt), peran (Pemilik / Petugas Operasional)
-- --------------------------------------------------------------------
CREATE TABLE `akun_pengguna` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `peran` ENUM('Pemilik', 'Petugas Operasional') NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- D2: Pesanan
-- Menyimpan: no_pesanan, tanggal_titip, berat, jenis_layanan,
-- lama_pengerjaan, total_harga, status_pengerjaan, waktu_pembaruan
-- --------------------------------------------------------------------
CREATE TABLE `pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_pesanan` VARCHAR(50) NOT NULL UNIQUE,
  `pelanggan_id` INT NOT NULL,
  `tanggal_titip` DATE NOT NULL,
  `berat` DECIMAL(6,2) NOT NULL,
  `jenis_layanan` VARCHAR(50) NOT NULL,
  `lama_pengerjaan` VARCHAR(50) NOT NULL,
  `total_harga` DECIMAL(12,2) NOT NULL,
  `status_pengerjaan` ENUM('Diterima', 'Dicuci', 'Disetrika', 'Siap Diambil/Diantar', 'Selesai') NOT NULL DEFAULT 'Diterima',
  `waktu_pembaruan` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pesanan_pelanggan` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- D3: Rincian Item Cucian
-- Menyimpan: jenis, jumlah, ciri_barang (warna/merek)
-- --------------------------------------------------------------------
CREATE TABLE `rincian_item` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` INT NOT NULL,
  `jenis` VARCHAR(100) NOT NULL,
  `jumlah` INT NOT NULL DEFAULT 1,
  `ciri_barang` VARCHAR(255) NOT NULL,
  CONSTRAINT `fk_rincian_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- D4: Cucian Tanpa Pemilik
-- Menyimpan: deskripsi_barang, foto, status (Ditemukan / Diserahkan)
-- --------------------------------------------------------------------
CREATE TABLE `cucian_tanpa_pemilik` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `deskripsi_barang` TEXT NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Ditemukan', 'Diserahkan') NOT NULL DEFAULT 'Ditemukan',
  `waktu_ditemukan` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `waktu_diserahkan` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- D5: Antar-Jemput
-- Menyimpan: jenis_layanan, alamat, berat_cucian, status (Dijadwalkan / Dijemput / Diantar / Selesai)
-- --------------------------------------------------------------------
CREATE TABLE `antar_jemput` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` INT NOT NULL,
  `jenis_layanan` VARCHAR(50) NOT NULL,
  `alamat` TEXT NOT NULL,
  `berat_cucian` DECIMAL(6,2) NOT NULL,
  `status` ENUM('Dijadwalkan', 'Dijemput', 'Diantar', 'Selesai') NOT NULL DEFAULT 'Dijadwalkan',
  `waktu_pembaruan` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_antar_jemput_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- D7: Riwayat Status
-- Menyimpan: status, waktu_pembaruan, diubah_oleh
-- --------------------------------------------------------------------
CREATE TABLE `riwayat_status` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` INT NOT NULL,
  `status` VARCHAR(100) NOT NULL,
  `waktu_pembaruan` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `diubah_oleh` VARCHAR(100) NOT NULL,
  CONSTRAINT `fk_riwayat_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- DATA AWAL (DEFAULT SEEDER)
-- --------------------------------------------------------------------

-- Akun Default (D6)
-- Password untuk keduanya: password123
INSERT INTO `akun_pengguna` (`username`, `password`, `peran`) VALUES
('pemilik', '$2y$10$C5TcdLwAe1pBwoo0dqh65ekLwt63hgiAGIbwU60k6AkjCosNDUMse', 'Pemilik'),
('petugas', '$2y$10$C5TcdLwAe1pBwoo0dqh65ekLwt63hgiAGIbwU60k6AkjCosNDUMse', 'Petugas Operasional');

-- Pelanggan Contoh (D1)
INSERT INTO `pelanggan` (`id`, `nama`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'Budi Santoso', '081234567890', 'Jl. Margonda Raya No. 45, Depok', '2026-11-20 09:00:00'),
(2, 'Siti Aminah', '085678901234', 'Jl. Akses UI No. 12, Kelapa Dua', '2026-11-21 10:15:00');

-- Pesanan Contoh (D2)
INSERT INTO `pesanan` (`id`, `no_pesanan`, `pelanggan_id`, `tanggal_titip`, `berat`, `jenis_layanan`, `lama_pengerjaan`, `total_harga`, `status_pengerjaan`, `waktu_pembaruan`) VALUES
(1, 'LND-20261120-001', 1, '2026-11-20', 3.50, 'Cuci Komplit', 'Reguler (2 Hari)', 24500.00, 'Dicuci', '2026-11-21 11:00:00'),
(2, 'LND-20261121-002', 2, '2026-11-21', 2.00, 'Cuci Kering', 'Kilat (1 Hari)', 16000.00, 'Diterima', '2026-11-21 10:15:00');

-- Rincian Item Cucian Contoh (D3)
INSERT INTO `rincian_item` (`pesanan_id`, `jenis`, `jumlah`, `ciri_barang`) VALUES
(1, 'Kemeja Polos', 2, 'Warna Biru Navy merk Uniqlo, Putih Zara'),
(1, 'Celana Jeans', 1, 'Warna Hitam merk Levis'),
(2, 'Jaket Hoodie', 1, 'Warna Abu-abu merk Erigo');

-- Antar-Jemput Contoh (D5)
INSERT INTO `antar_jemput` (`pesanan_id`, `jenis_layanan`, `alamat`, `berat_cucian`, `status`, `waktu_pembaruan`) VALUES
(1, 'Antar', 'Jl. Margonda Raya No. 45, Depok', 3.50, 'Dijadwalkan', '2026-11-20 09:00:00');

-- Riwayat Status Contoh (D7)
INSERT INTO `riwayat_status` (`pesanan_id`, `status`, `waktu_pembaruan`, `diubah_oleh`) VALUES
(1, 'Status pengerjaan diatur ke: Diterima', '2026-11-20 09:00:00', 'pemilik (Pemilik)'),
(1, 'Status pengerjaan diubah ke: Dicuci', '2026-11-21 11:00:00', 'petugas (Petugas Operasional)'),
(2, 'Status pengerjaan diatur ke: Diterima', '2026-11-21 10:15:00', 'pemilik (Pemilik)');

-- Cucian Tanpa Pemilik Contoh (D4)
INSERT INTO `cucian_tanpa_pemilik` (`id`, `deskripsi_barang`, `foto`, `status`, `waktu_ditemukan`, `waktu_diserahkan`) VALUES
(1, 'Sajadah motif kubah warna hijau tua tertinggal di keranjang pengeringan.', NULL, 'Ditemukan', '2026-11-19 14:20:00', NULL);
