-- =====================================================
-- JogjaYuk! - Database lengkap (versi gabungan)
-- Cara pakai: di phpMyAdmin KLIK dulu database yang dipakai project
-- (sama dengan nama database di config/koneksi.php), lalu Import > pilih file ini.
-- File ini TIDAK membuat database baru.
-- PERHATIAN: file ini membuat ulang semua tabel. Data lama di tabel
-- dengan nama yang sama akan DIGANTI oleh data di bawah.
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `booking_rental`;
DROP TABLE IF EXISTS `booking_paket`;
DROP TABLE IF EXISTS `booking`;
DROP TABLE IF EXISTS `rental`;
DROP TABLE IF EXISTS `paket_wisata`;
DROP TABLE IF EXISTS `homestay`;
DROP TABLE IF EXISTS `users`;

-- -----------------------------------------------------
-- users
-- -----------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Anindya', 'anind0784@gmail.com', '$2y$10$1DkDMnHLobRk/9cZ7oRRfeAwfGlyT8hAEIyTc2Pw2izQYQLc3wrTW', 'user', '2026-11-09 00:34:02');

-- -----------------------------------------------------
-- homestay (kategori: Pusat Kota, Budaya & Heritage, Keluarga, Tenang & Santai)
-- -----------------------------------------------------
CREATE TABLE `homestay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_homestay` varchar(150) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_per_malam` decimal(12,2) NOT NULL,
  `fasilitas` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','tidak tersedia') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_homestay_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `homestay` (`id`, `nama_homestay`, `lokasi`, `kategori`, `deskripsi`, `harga_per_malam`, `fasilitas`, `gambar`, `status`, `created_at`) VALUES
(1, 'Homestay Prawirotaman', 'Kawasan Prawirotaman, Yogyakarta', 'Tenang & Santai', 'Homestay nyaman dengan suasana tenang dan cocok untuk wisatawan.', 275000.00, 'Wi-Fi gratis, AC, kamar mandi pribadi, tempat parkir', 'prawirotaman.jpeg', 'tersedia', '2026-11-07 07:10:12'),
(2, 'Homestay Sleman', 'Kabupaten Sleman, Yogyakarta', 'Keluarga', 'Penginapan nyaman untuk liburan bersama keluarga.', 400000.00, 'Wi-Fi gratis, AC, dapur, tempat parkir', 'sleman.jpg', 'tersedia', '2026-11-07 07:10:12'),
(3, 'Homestay Malioboro', 'Kawasan Malioboro, Yogyakarta', 'Pusat Kota', 'Penginapan strategis dekat pusat perbelanjaan dan wisata Malioboro.', 350000.00, 'Wi-Fi gratis, AC, kamar mandi pribadi, TV', 'malioboro.jpg', 'tersedia', '2026-11-07 07:10:12'),
(4, 'Homestay Kotagede', 'Kawasan Kotagede, Yogyakarta', 'Budaya & Heritage', 'Penginapan dengan suasana khas Yogyakarta dan dekat wisata budaya.', 300000.00, 'Wi-Fi gratis, AC, tempat parkir, kamar mandi pribadi', 'kotagede.jpg', 'tersedia', '2026-11-07 07:10:12');

-- -----------------------------------------------------
-- paket_wisata (kategori: Candi & Budaya, Alam & Pemandangan, Pantai)
-- -----------------------------------------------------
CREATE TABLE `paket_wisata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_paket` varchar(150) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_per_orang` decimal(12,2) NOT NULL,
  `durasi` varchar(100) DEFAULT NULL,
  `fasilitas` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','tidak tersedia') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_paket_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `paket_wisata` (`id`, `nama_paket`, `lokasi`, `kategori`, `deskripsi`, `harga_per_orang`, `status`) VALUES
(1, 'Paket Wisata Candi Borobudur', 'Magelang, Jawa Tengah', 'Candi & Budaya', 'Nikmati pengalaman mengunjungi salah satu candi paling terkenal di Indonesia.', 250000.00, 'tersedia'),
(2, 'Paket Wisata HeHa Sky View', 'Gunungkidul, Yogyakarta', 'Alam & Pemandangan', 'Nikmati pemandangan alam Jogja dari tempat wisata dengan suasana menarik.', 150000.00, 'tersedia'),
(3, 'Paket Wisata Pantai Parangtritis', 'Bantul, Yogyakarta', 'Pantai', 'Jelajahi keindahan pantai dan nikmati suasana liburan bersama teman.', 175000.00, 'tersedia');

-- -----------------------------------------------------
-- rental (kategori = jenis_kendaraan: Motor, Mobil, Jeep)
-- -----------------------------------------------------
CREATE TABLE `rental` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kendaraan` varchar(150) NOT NULL,
  `jenis_kendaraan` varchar(100) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_per_hari` decimal(12,2) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','tidak tersedia') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rental_jenis` (`jenis_kendaraan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rental` (`id`, `nama_kendaraan`, `jenis_kendaraan`, `lokasi`, `deskripsi`, `harga_per_hari`, `status`) VALUES
(1, 'Honda Scoopy', 'Motor', 'Kota Yogyakarta', 'Motor praktis dan nyaman untuk berkeliling kota Jogja.', 85000.00, 'tersedia'),
(2, 'Toyota Avanza', 'Mobil', 'Kota Yogyakarta', 'Mobil keluarga yang cocok untuk perjalanan bersama.', 350000.00, 'tersedia'),
(3, 'Jeep Wisata', 'Jeep', 'Kawasan Kaliurang', 'Nikmati pengalaman perjalanan wisata dengan kendaraan Jeep.', 500000.00, 'tersedia');

-- -----------------------------------------------------
-- booking (homestay)
-- -----------------------------------------------------
CREATE TABLE `booking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `homestay_id` int(11) NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `tanggal_checkin` date NOT NULL,
  `tanggal_checkout` date NOT NULL,
  `jumlah_tamu` int(11) NOT NULL,
  `jumlah_malam` int(11) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status` enum('menunggu','dikonfirmasi','dibatalkan') DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `homestay_id` (`homestay_id`),
  KEY `idx_booking_tanggal` (`homestay_id`,`tanggal_checkin`,`tanggal_checkout`),
  CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`homestay_id`) REFERENCES `homestay` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Booking lama atas nama Anindya, kini dikaitkan ke akunnya (user_id = 1)
INSERT INTO `booking` (`id`, `user_id`, `homestay_id`, `nama_pemesan`, `tanggal_checkin`, `tanggal_checkout`, `jumlah_tamu`, `jumlah_malam`, `total_harga`, `status`, `created_at`) VALUES
(1, 1, 4, 'Anindya', '2026-11-10', '2026-11-12', 1, 2, 600000.00, 'menunggu', '2026-11-09 00:13:50');

-- -----------------------------------------------------
-- booking_paket
-- -----------------------------------------------------
CREATE TABLE `booking_paket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `paket_id` int(11) NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jumlah_orang` int(11) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status` enum('menunggu','dikonfirmasi','dibatalkan') DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `paket_id` (`paket_id`),
  CONSTRAINT `booking_paket_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `booking_paket_ibfk_2` FOREIGN KEY (`paket_id`) REFERENCES `paket_wisata` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- booking_rental
-- -----------------------------------------------------
CREATE TABLE `booking_rental` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jumlah_hari` int(11) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status` enum('menunggu','dikonfirmasi','dibatalkan') DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `rental_id` (`rental_id`),
  KEY `idx_rental_tanggal` (`rental_id`,`tanggal_mulai`,`tanggal_selesai`),
  CONSTRAINT `booking_rental_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `booking_rental_ibfk_2` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- wishlist (favorit)
-- -----------------------------------------------------
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tipe` enum('homestay','paket','rental') NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_wishlist` (`user_id`,`tipe`,`item_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
