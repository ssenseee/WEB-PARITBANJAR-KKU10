-- Membuat tabel untuk menyimpan data pengguna yang sudah terverifikasi
-- Pastikan Anda menggunakan engine InnoDB untuk mendukung relasi dan transaksi di masa depan.

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nama lengkap pengguna',
  `nik` varchar(16) NOT NULL COMMENT 'Nomor Induk Kependudukan, harus unik',
  `email` varchar(255) NOT NULL COMMENT 'Alamat email pengguna, harus unik',
  `no_hp` varchar(20) NOT NULL COMMENT 'Nomor Handphone/WhatsApp, harus unik',
  `password` varchar(255) NOT NULL COMMENT 'Password yang sudah di-hash',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Status verifikasi, 1=terverifikasi, 0=belum',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu pendaftaran akun',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik` (`nik`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `no_hp` (`no_hp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Membuat tabel untuk menyimpan kode OTP sementara
-- Tabel ini bersifat sementara, data akan dihapus setelah verifikasi berhasil.

CREATE TABLE `otp` (
  `nomor` varchar(20) NOT NULL COMMENT 'Nomor HP yang dikirimi OTP',
  `otp` varchar(6) NOT NULL COMMENT 'Kode OTP 6 digit',
  `waktu` int(11) NOT NULL COMMENT 'Waktu pengiriman OTP dalam format UNIX Timestamp',
  PRIMARY KEY (`nomor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users` ADD `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user' AFTER `password`;
ALTER TABLE `users` ADD `profile_picture` VARCHAR(255) NULL DEFAULT NULL AFTER `role`;

CREATE TABLE `pengaduan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `dusun` varchar(50) NOT NULL,
  `jenis_pengaduan` varchar(50) NOT NULL,
  `isi_pengaduan` text NOT NULL,
  `lampiran_path` varchar(255) DEFAULT NULL,
  `status` enum('Diajukan','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Diajukan',
  `tanggal_pengaduan` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `kontak_masuk` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subjek` VARCHAR(255) NOT NULL,
  `pesan` TEXT NOT NULL,
  `tanggal_kirim` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Baru','Dibaca') NOT NULL DEFAULT 'Baru',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE pengaduan
ADD COLUMN last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;