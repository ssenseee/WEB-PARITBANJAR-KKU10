<?php
session_start();
require_once 'config.php'; // Memanggil file koneksi database

// 1. Pastikan data dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, kembalikan ke halaman kontak
    header('Location: ../kontak.php');
    exit;
}

// 2. Ambil dan bersihkan data dari form
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$subjek = trim($_POST['subjek'] ?? '');
$pesan = trim($_POST['pesan'] ?? '');

// 3. Validasi dasar: pastikan tidak ada yang kosong
if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
    // Jika ada yang kosong, simpan pesan error dan kembalikan
    $_SESSION['error_message'] = "Semua kolom wajib diisi.";
    header('Location: ../kontak.php');
    exit;
}

// 4. Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Format email tidak valid.";
    header('Location: ../kontak.php');
    exit;
}

// 5. Simpan ke database menggunakan prepared statement (aman dari SQL Injection)
$sql = "INSERT INTO kontak_masuk (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $nama, $email, $subjek, $pesan);

    if ($stmt->execute()) {
        // Jika berhasil, simpan pesan sukses
        $_SESSION['success_message'] = "Pesan Anda telah berhasil dikirim. Terima kasih!";
    } else {
        // Jika gagal, simpan pesan error
        $_SESSION['error_message'] = "Terjadi kesalahan. Pesan Anda gagal dikirim.";
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
}

$conn->close();

// 6. Kembalikan pengguna ke halaman kontak
header('Location: ../kontak.php');
exit;