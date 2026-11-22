-- =====================================================
-- JogjaYuk! - Update database (AMAN: tidak menghapus tabel lama)
-- Jalankan di phpMyAdmin > database `jogjayuk` > tab SQL
-- =====================================================

-- 1. Wishlist / favorit (homestay, paket wisata, rental)
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tipe` enum('homestay','paket','rental') NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_wishlist` (`user_id`,`tipe`,`item_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Booking paket wisata
CREATE TABLE IF NOT EXISTS `booking_paket` (
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

-- 3. Booking rental kendaraan
CREATE TABLE IF NOT EXISTS `booking_rental` (
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
  CONSTRAINT `booking_rental_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `booking_rental_ibfk_2` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Memindahkan data paket wisata & rental yang sebelumnya hardcode di
--    paketWisata.php dan rental.php ke database (tabelnya masih kosong).
--    JALANKAN SEKALI SAJA.
INSERT INTO `paket_wisata` (`nama_paket`,`lokasi`,`deskripsi`,`harga_per_orang`,`status`) VALUES
('Paket Wisata Candi Borobudur','Magelang, Jawa Tengah','Nikmati pengalaman mengunjungi salah satu candi paling terkenal di Indonesia.',250000.00,'tersedia'),
('Paket Wisata HeHa Sky View','Gunungkidul, Yogyakarta','Nikmati pemandangan alam Jogja dari tempat wisata dengan suasana menarik.',150000.00,'tersedia'),
('Paket Wisata Pantai Parangtritis','Bantul, Yogyakarta','Jelajahi keindahan pantai dan nikmati suasana liburan bersama teman.',175000.00,'tersedia');

INSERT INTO `rental` (`nama_kendaraan`,`jenis_kendaraan`,`lokasi`,`deskripsi`,`harga_per_hari`,`status`) VALUES
('Honda Scoopy','Motor','Kota Yogyakarta','Motor praktis dan nyaman untuk berkeliling kota Jogja.',85000.00,'tersedia'),
('Toyota Avanza','Mobil','Kota Yogyakarta','Mobil keluarga yang cocok untuk perjalanan bersama.',350000.00,'tersedia'),
('Jeep Wisata','Jeep','Kawasan Kaliurang','Nikmati pengalaman perjalanan wisata dengan kendaraan Jeep.',500000.00,'tersedia');

-- 5. (OPSIONAL) Booking lama (id 1) belum punya user_id, sehingga tidak muncul
--    di riwayat siapa pun. Hapus tanda komentar jika ingin dikaitkan ke Anindya:
-- UPDATE `booking` SET `user_id` = 1 WHERE `id` = 1 AND `user_id` IS NULL;
