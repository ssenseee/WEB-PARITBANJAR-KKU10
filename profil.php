<?php
session_start();
require 'back-end/config.php';

// Keamanan: Pastikan pengguna sudah login untuk mengakses halaman ini
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: masuk.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$pesan = '';

// --- LOGIKA UPLOAD FOTO PROFIL ---
if (isset($_POST['upload_foto'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        $image_name = uniqid() . '-' . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                // Update path gambar di database
                $stmt = mysqli_prepare($conn, "UPDATE users SET profile_picture = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "si", $target_file, $user_id);
                if (mysqli_stmt_execute($stmt)) {
                    $pesan = '<div class="alert alert-success">Foto profil berhasil diperbarui.</div>';
                } else {
                    $pesan = '<div class="alert alert-danger">Gagal menyimpan path foto ke database.</div>';
                }
                mysqli_stmt_close($stmt);
            } else {
                $pesan = '<div class="alert alert-danger">Gagal mengunggah file.</div>';
            }
        } else {
            $pesan = '<div class="alert alert-danger">Hanya file JPG, JPEG, PNG, & GIF yang diizinkan.</div>';
        }
    } else {
        $pesan = '<div class="alert alert-danger">Terjadi kesalahan saat mengunggah file.</div>';
    }
}

// --- LOGIKA UBAH PASSWORD ---
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if ($password_baru !== $konfirmasi_password) {
        $pesan = '<div class="alert alert-danger">Konfirmasi password baru tidak cocok.</div>';
    } else {
        // Ambil hash password lama dari DB
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Verifikasi password lama
        if (password_verify($password_lama, $user['password'])) {
            // Jika valid, hash password baru dan update DB
            $hash_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $hash_password_baru, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $pesan = '<div class="alert alert-success">Password berhasil diubah.</div>';
            } else {
                $pesan = '<div class="alert alert-danger">Gagal mengubah password di database.</div>';
            }
            mysqli_stmt_close($stmt);
        } else {
            $pesan = '<div class="alert alert-danger">Password lama yang Anda masukkan salah.</div>';
        }
    }
}

// Ambil data terbaru pengguna untuk ditampilkan
$stmt = mysqli_prepare($conn, "SELECT name, nik, email, no_hp, profile_picture FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$profile_pic_path = !empty($user_data['profile_picture']) ? $user_data['profile_picture'] : 'img/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?php echo htmlspecialchars($user_data['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
        }

        .navbar {
            transition: background 0.3s ease, box-shadow 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .nav-link {
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: rgb(var(--bs-primary-rgb)) !important;
        }
    </style>
</head>

<body>
    <nav id="mainNavbar" class="navbar navbar-expand-lg sticky-top navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <img src="img/logo-mempawah.png" alt="Logo Kabupaten Mempawah" width="45" height="45" class="me-2">
                <span class="fw-bold">Desa Parit Banjar</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLinks"
                aria-controls="navLinks" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navLinks">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="home.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengaduan.php">Pengaduan</a></li>
                    <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayatpengaduan.php">Riwayat Pengaduan</a>
                </ul>

                <div class="d-flex align-items-center mt-3 mt-lg-0 ms-lg-3">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark"
                                id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Foto Profil" width="32"
                                    height="32" class="rounded-circle me-2">
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                                <li><a class="dropdown-item" href="profil.php" target="_blank"><i
                                            class="bi bi-person-fill me-2"></i>Profil Saya</a></li>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php"><i
                                                class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="back-end/logout.php"><i
                                            class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="masuk.php" class="btn btn-outline-primary w-100 w-lg-auto mb-2 mb-lg-0">Masuk</a>
                        <a href="daftar.php" class="btn btn-primary w-100 w-lg-auto ms-lg-2">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4 text-center">
                    <div class="card-body">
                        <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Foto Profil"
                            class="rounded-circle img-fluid mb-3"
                            style="width: 150px; height: 150px; object-fit: cover;">
                        <h5 class="card-title"><?php echo htmlspecialchars($user_data['name']); ?></h5>
                        <p class="text-muted mb-4"><?php echo htmlspecialchars($user_data['email']); ?></p>

                        <form action="profil.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="profile_pic" class="form-label">Ganti Foto Profil</label>
                                <input class="form-control" type="file" id="profile_pic" name="profile_pic" required>
                            </div>
                            <button type="submit" name="upload_foto" class="btn btn-primary">Unggah Foto</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <?php echo $pesan; // Tampilkan pesan sukses/error di sini ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Data Diri</h5>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <p class="mb-0">Nama Lengkap</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($user_data['name']); ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <p class="mb-0">Email</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($user_data['email']); ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <p class="mb-0">NIK</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($user_data['nik']); ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <p class="mb-0">No. WhatsApp</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($user_data['no_hp']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Ubah Password</h5>
                        <form action="profil.php" method="post">
                            <div class="mb-3">
                                <label for="password_lama" class="form-label">Password Lama</label>
                                <input type="password" class="form-control" id="password_lama" name="password_lama"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="password_baru" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="password_baru" name="password_baru"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="konfirmasi_password"
                                    name="konfirmasi_password" required>
                            </div>
                            <button type="submit" name="ganti_password" class="btn btn-primary">Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>