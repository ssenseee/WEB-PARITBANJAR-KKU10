<?php
// back-end/proses_masuk.php

// Selalu mulai session di awal
session_start();

// Hubungkan ke database
require 'config.php';

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // DIUBAH: Tambahkan kolom 'role' ke dalam query SQL
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    // Pastikan statement berhasil dibuat sebelum melanjutkan
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $login_sukses = false;
        $user_role = 'user'; // Default role jika login gagal

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $login_sukses = true;
                $user_role = $user['role']; // Ambil role dari database

                // Atur variabel session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['logged_in'] = true;
                $_SESSION['user_role'] = $user['role']; // DITAMBAHKAN: Simpan role ke session
            }
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        if ($login_sukses) {
            // DITAMBAHKAN: Logika pengecekan peran untuk redirect
            if ($user_role == 'admin') {
                // Jika peran adalah admin, arahkan ke dashboard
                header("Location: ../admin/dashboard.php");
            } else {
                // Jika bukan admin (peran adalah user), arahkan ke home
                header("Location: ../home.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Email atau password yang Anda masukkan salah.";
            header("Location: ../masuk.php");
            exit();
        }

    } else {
        $_SESSION['login_error'] = "Terjadi kesalahan pada sistem. Silakan coba lagi nanti.";
        header("Location: ../masuk.php");
        exit();
    }
} else {
    header("Location: ../masuk.php");
    exit();
}
?>