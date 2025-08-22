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
    header("Location: kelola-pengguna.php");
    exit;
}

$id = $_GET['id'];

// Hapus pengguna
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Pengguna berhasil dihapus.";
} else {
    $_SESSION['error_message'] = "Gagal menghapus pengguna.";
}

$stmt->close();
$conn->close();

header("Location: kelola-pengguna.php");
exit;
?>