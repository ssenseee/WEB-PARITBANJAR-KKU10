<?php
session_start();
require_once 'back-end/config.php';

$profile_pic_path = 'img/default-avatar.png';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT profile_picture FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        if ($user && !empty($user['profile_picture'])) {
            $profile_pic_path = $user['profile_picture'];
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - Desa Parit Banjar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" href="img/logo-mempawah.png" type="image/png">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/halaman-depan.JPG') center center/cover no-repeat;
            color: #fff;
            padding: 10rem 0;
            border-radius: 0 0 2rem 2rem;
            margin-bottom: 2rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
        }

        header h1 {
            font-size: 2.8rem;
            font-weight: 700;
        }

        header p {
            font-size: 1.2rem;
        }

        .contact-card {
            border: none;
            border-radius: 1rem;
            padding: 2rem;
            background: #fff;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #f1f3f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #0d6efd;
            margin-right: 15px;
            transition: all .3s ease;
        }

        .d-flex:hover .icon-circle {
            background: #0d6efd;
            color: #fff;
            transform: scale(1.1);
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 12px rgba(13, 110, 253, 0.25);
        }

        .btn-kontak {
            background: linear-gradient(45deg, #0d6efd, #6610f2);
            color: #fff;
            font-weight: bold;
            border-radius: 50px;
            padding: 0.9rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-kontak:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .social-icons a {
            display: inline-block;
            margin-right: 15px;
            font-size: 1.6rem;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            transform: scale(1.2) rotate(5deg);
        }

        .social-icons .bi-facebook {
            color: #1877f2;
        }

        .social-icons .bi-instagram {
            color: #e1306c;
        }

        .social-icons .bi-youtube {
            color: #ff0000;
        }

        footer {
            background: #111;
            color: #aaa;
            font-size: 0.9rem;
        }

        footer p {
            margin: 0;
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
                    <li class="nav-item"><a class="nav-link active" href="kontak.php">Kontak</a></li>
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

    <main>
        <header class="text-center" data-aos="fade-up">
            <div class="container">
                <h1 class="fw-bold">📞 Hubungi Kami</h1>
                <p class="lead">Silakan tinggalkan pesan atau pertanyaan melalui form di bawah.</p>
            </div>
        </header>
        <section class="py-5">
            <div class="container">

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert" data-aos="fade-left">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" data-aos="fade-left">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-lg-5" data-aos="fade-right">
                        <div class="contact-card mb-4">
                            <h4 class="fw-bold mb-4">Informasi Kontak</h4>
                            <div class="d-flex mb-4">
                                <div class="icon-circle"><i class="bi bi-geo-alt-fill"></i></div>
                                <div>
                                    <h6 class="fw-semibold mb-0">Alamat Kantor</h6><small class="text-muted">Jl. Raya
                                        Desa Parit Banjar No. 123, Mempawah Hilir</small>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="icon-circle"><i class="bi bi-envelope-fill"></i></div>
                                <div>
                                    <h6 class="fw-semibold mb-0">Email</h6><small
                                        class="text-muted">kantordesa.paritbanjar@gmail.com</small>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="icon-circle"><i class="bi bi-telephone-fill"></i></div>
                                <div>
                                    <h6 class="fw-semibold mb-0">Telepon</h6><small class="text-muted">(0561)
                                        123-456</small>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="icon-circle"><i class="bi bi-calendar-week-fill"></i></div>
                                <div>
                                    <h6 class="fw-semibold mb-0">Jam Pelayanan</h6><small class="text-muted">Senin -
                                        Jumat: 08:00 - 12:00 WIB</small>
                                </div>
                            </div>
                            <div class="mt-4 social-icons">
                                <h6 class="fw-semibold mb-3">Media Sosial</h6>
                                <a href="#"><i class="bi bi-facebook"></i></a>
                                <a href="#"><i class="bi bi-instagram"></i></a>
                                <a href="#"><i class="bi bi-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7" data-aos="fade-left">
                        <div class="contact-card">
                            <h4 class="fw-bold mb-4">💬 Kirim Pesan</h4>
                            <form action="back-end/proses_kontak.php" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6"><input type="text" class="form-control" name="nama"
                                            placeholder="👤 Nama Anda" required></div>
                                    <div class="col-md-6"><input type="email" class="form-control" name="email"
                                            placeholder="📧 Email Anda" required></div>
                                    <div class="col-12"><input type="text" class="form-control" name="subjek"
                                            placeholder="✏️ Subjek Pesan" required></div>
                                    <div class="col-12"><textarea class="form-control" name="pesan" rows="5"
                                            placeholder="📝 Tulis pesan Anda..." required></textarea></div>
                                    <div class="col-12"><button type="submit" class="btn btn-kontak w-100">🚀 Kirim
                                            Pesan</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-4 text-center text-white-50">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Desa Parit Banjar. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });
        window.addEventListener("scroll", function () {
            const navbar = document.querySelector(".navbar");
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    </script>
</body>

</html>