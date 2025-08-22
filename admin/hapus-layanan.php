<?php
session_start();
require_once '../back-end/config.php';

// Keamanan
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../masuk.php");
    exit;
}

// Cek ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: kelola-layanan.php");
    exit;
}

$id = $_GET['id'];

// Hapus pengajuan
$stmt = $conn->prepare("DELETE FROM pengajuan_layanan WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Pengajuan layanan berhasil dihapus.";
} else {
    $_SESSION['error_message'] = "Gagal menghapus pengajuan layanan.";
}

$stmt->close();
$conn->close();

header("Location: kelola-layanan.php");
exit;
?>