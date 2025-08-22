<?php
session_start();
require_once 'back-end/config.php';

// --- Logika Keamanan dan Pengambilan Data ---
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['login_error'] = "Anda harus login terlebih dahulu untuk mengakses halaman pelayanan.";
    header("Location: masuk.php");
    exit;
}

// Ambil data pengguna yang sedang login
$user_id = $_SESSION['user_id'];
$nama_user = '';
$nik_user = '';
$telepon_user = '';
$profile_pic_path = 'img/default-avatar.png'; // Default avatar

// Menggunakan prepared statement untuk keamanan
$stmt = mysqli_prepare($conn, "SELECT name, nik, no_hp, profile_picture FROM users WHERE id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($user = mysqli_fetch_assoc($result)) {
        $nama_user = $user['name'];
        $nik_user = $user['nik'];
        $telepon_user = $user['no_hp'];
        if (!empty($user['profile_picture'])) {
            $profile_pic_path = $user['profile_picture'];
        }
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Formulir pengaduan pelayanan desa secara online untuk warga Desa Parit Banjar.">
    <meta name="keywords" content="Desa Parit Banjar, Formulir Pelayanan, Pengaduan Online">
    <title>Formulir Pengaduan Pelayanan Desa - Desa Parit Banjar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="img/logo-mempawah.png" type="image/png">

    <style>
        body {
            background: #f7f8fa;
            font-family: 'Poppins', sans-serif;
        }

        #form-pelayanan {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('img/halaman-depan.JPG') center center/cover no-repeat;
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 40px;
            backdrop-filter: blur(10px);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 600px;
        }

        .modern-input {
            background: rgba(255, 255, 255, 0.15) !important;
            color: #fff;
            border: none;
            border-radius: 8px;
        }

        .modern-input:focus {
            background: rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.4);
            color: #fff;
        }

        .modern-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-modern {
            background: linear-gradient(135deg, #007bff, #0056d2);
            color: white;
            font-size: 0.95rem;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            box-shadow: 0 4px 10px rgba(0, 91, 187, 0.4);
            transition: all 0.25s ease;
        }

        .btn-modern:hover {
            background: linear-gradient(135deg, #0056d2, #0041a8);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 86, 210, 0.55);
        }

        .loading-spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid #fff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .toast-container {
            position: fixed;
            bottom: 16px;
            right: 16px;
            z-index: 1100;
        }

        .toast-modern {
            background: #007bff;
            color: #fff;
            border-radius: 10px;
            font-size: 0.9rem;
            padding: 10px 16px;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.5);
        }

        .toast-modern.bg-danger {
            background: #dc3545 !important;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.5);
        }

        /* === PENYESUAIAN UNTUK TAMPILAN MOBILE (HP) === */
        @media (max-width: 768px) {
            #form-pelayanan {
                padding: 1rem;
                /* Memberi spasi di sekeliling section */
            }

            .glass-card {
                padding: 30px;
                /* Mengurangi padding di dalam card agar tidak terlalu sempit */
            }
        }

        /* ============================================== */
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
                    <li class="nav-item"><a class="nav-link active" href="pengaduan.php">Pengaduan</a></li>
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

    <main>
        <section id="form-pelayanan" class="min-vh-100 d-flex align-items-center justify-content-center">
            <div class="glass-card">
                <h2 class="text-center mb-3 fw-bold text-white">Form Pengaduan Desa</h2>
                <p class="text-center text-light mb-4">Isi data berikut untuk menyampaikan pengaduan Anda</p>

                <form action="back-end/proses_pengaduan.php" method="POST" enctype="multipart/form-data"
                    id="pengaduanForm">
                    <div class="mb-3 input-group">
                        <span class="input-group-text bg-transparent text-white"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control modern-input" name="namaLengkap"
                            placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($nama_user); ?>" required
                            readonly>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text bg-transparent text-white"><i
                                class="bi bi-credit-card-2-front"></i></span>
                        <input type="text" class="form-control modern-input" name="nik" placeholder="NIK (16 digit)"
                            value="<?php echo htmlspecialchars($nik_user); ?>" required readonly>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text bg-transparent text-white"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="form-control modern-input" name="telepon" placeholder="Nomor WhatsApp"
                            value="<?php echo htmlspecialchars($telepon_user); ?>" required readonly>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text bg-transparent text-white"><i
                                class="bi bi-geo-alt-fill"></i></span>
                        <select class="form-select modern-input" name="dusun" style="color:white;" required>
                            <option value="" disabled selected style="color:black;">Pilih Nama Dusun...</option>
                            <option value="parit banjar laut" style="color:black;">Parit Banjar Laut</option>
                            <option value="parit banjar tengah" style="color:black;">Parit Banjar Tengah</option>
                            <option value="parit banjar darat" style="color:black;">Parit Banjar Darat</option>
                            <option value="ampulur" style="color:black;">Ampulor</option>
                        </select>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text bg-transparent text-white"><i
                                class="bi bi-exclamation-triangle"></i></span>
                        <select class="form-select modern-input" name="jenisPengaduan" style="color:white;" required>
                            <option value="" disabled selected style="color:black;">Pilih jenis pengaduan...</option>
                            <option value="Administrasi" style="color:black;">Pelayanan Administrasi</option>
                            <option value="Fasilitas Umum" style="color:black;">Fasilitas Umum</option>
                            <option value="Keamanan" style="color:black;">Keamanan & Ketertiban</option>
                            <option value="Sosial" style="color:black;">Masalah Sosial</option>
                            <option value="Lainnya" style="color:black;">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control modern-input" name="isiPengaduan" rows="4"
                            placeholder="Tuliskan pengaduan Anda..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="lampiran" class="form-label text-white">📎 Lampiran (opsional)</label>
                        <input class="form-control modern-input" type="file" id="lampiran" name="lampiran">
                        <div class="form-text text-light">Format: .pdf, .jpg, .png. Maks: 5MB</div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit"
                            class="btn btn-modern py-2 fw-semibold d-flex align-items-center justify-content-center">
                            <span>Kirim Pengaduan</span>
                            <div class="loading-spinner" id="loadingSpinner"></div>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <div class="toast-container">
        <div id="statusToast" class="toast toast-modern align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <footer class="py-4 text-center text-white-50" style="background-color: #1a1e21;">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Desa Parit Banjar. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById("pengaduanForm");
            const spinner = document.getElementById("loadingSpinner");
            const submitButton = form.querySelector('button[type="submit"]');
            const toastEl = document.getElementById("statusToast");
            const toastMessage = document.getElementById("toastMessage");
            const toast = new bootstrap.Toast(toastEl);

            form.addEventListener("submit", function (e) {
                e.preventDefault();
                spinner.style.display = "inline-block";
                submitButton.disabled = true;
                const formData = new FormData(form);
                fetch('back-end/proses_pengaduan.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        toastEl.classList.remove('bg-danger');
                        if (data.status === 'success') {
                            toastMessage.innerText = data.message;
                            form.reset();
                        } else {
                            toastEl.classList.add('bg-danger');
                            toastMessage.innerText = data.message || 'Terjadi kesalahan.';
                        }
                        toast.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastEl.classList.add('bg-danger');
                        toastMessage.innerText = 'Tidak dapat terhubung ke server.';
                        toast.show();
                    })
                    .finally(() => {
                        spinner.style.display = "none";
                        submitButton.disabled = false;
                    });
            });
        });
    </script>
</body>

</html>