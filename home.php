<?php
session_start();
// Sertakan file config untuk koneksi database jika diperlukan untuk mengambil foto profil
require_once 'back-end/config.php';

$profile_pic_path = 'img/default-avatar.png'; // Gambar default

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
    <meta name="description"
        content="Website pelayanan terpadu untuk Desa Parit Banjar. Dapatkan layanan administrasi, informasi, dan pengaduan dengan mudah dan cepat.">
    <meta name="keywords" content="Desa Parit Banjar, Pelayanan Desa, Bootstrap 5, Mempawah">

    <title>Desa Parit Banjar - Home</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

    <link rel="icon" href="img/logo-mempawah.png" type="image/png">
</head>

<body data-bs-spy="scroll" data-bs-target="#mainNavbar">

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
                    <li class="nav-item"><a class="nav-link active" href="home.php">Beranda</a></li>
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

    <main>
        <section id="home" class="hero d-flex align-items-center text-white text-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h1 class="display-4 fw-bold animated-element">Selamat Datang Di Website Resmi Desa Parit Banjar
                        </h1>
                        <p class="lead my-4 animated-element">Pelayanan Untuk Kesejahteraan Masyarakat Desa Parit Banjar
                        </p>
                        <a href="#layanan" class="btn btn-cta btn-lg rounded-pill px-4 animated-element">Jelajahi
                            Layanan Kami</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="tentang-desa" class="py-5">
            <div class="container">
                <div class="text-center mb-5 animated-element">
                    <h2 class="fw-bold">Tentang Desa Parit Banjar</h2>
                    <p class="text-muted">Mengenal Lebih Dekat Desa Kami di Jantung Mempawah Hilir.</p>
                </div>
                <div class="row align-items-center">
                    <p>
                        Selamat datang di Desa Parit Banjar, sebuah permata di Kecamatan Mempawah Hilir, Kabupaten
                        Mempawah. Di sini, kehangatan komunitas berpadu dengan kekayaan potensi alam dan budaya.
                        Kami mewarisi semangat gotong royong yang tak pernah padam, menjadi fondasi dari setiap
                        langkah pembangunan dan kebersamaan kami.
                        <br><br>
                        Kisah desa kami terjalin di empat wilayah dusun yang unik: Parit Banjar Laut dengan
                        pesonanya, Parit Banjar Tengah sebagai pusat kegiatan, Parit Banjar Darat yang asri, serta
                        Dusun Ampulor yang penuh kebersamaan.
                    </p>
                    <div class="col-lg-5 mb-4 mb-lg-0 animated-element">
                        <img src="img/kantor-desa-pelayanan.jpg" class="img-fluid rounded-3 shadow"
                            alt="Kantor Desa Parit Banjar">
                    </div>

                    <div class="col-lg-7 animated-element">

                        <div class="mt-4">
                            <h5 class="fw-bold">Visi Kami</h5>
                            <p>
                                "Mewujudkan Desa Parit Banjar yang Maju, Mandiri, Sejahtera, dan Berbudaya Berlandaskan
                                Iman dan Taqwa."
                            </p>
                            <h5 class="fw-bold mt-3">Misi Kami</h5>
                            <ul>
                                <li>Meningkatkan kualitas pelayanan publik yang cepat, tepat, dan transparan.</li>
                                <li>Mengembangkan potensi ekonomi lokal melalui pertanian, perikanan, dan UMKM.</li>
                                <li>Membangun infrastruktur desa yang merata dan berkelanjutan.</li>
                                <li>Meningkatkan kualitas sumber daya manusia melalui pendidikan dan kesehatan.</li>
                                <li>Melestarikan nilai-nilai budaya dan kearifan lokal di tengah masyarakat.</li>
                            </ul>
                        </div>
                        <p class="mt-4">
                            Dengan potensi utama di sektor pertanian dan perkebunan, kami terus berupaya untuk
                            berinovasi demi meningkatkan kesejahteraan seluruh warga. Kami mengundang Anda untuk
                            mengenal lebih jauh tentang Desa Parit Banjar.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section id="layanan" class="services py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Layanan Unggulan Kami</h2>
                    <p class="text-muted">Proses cepat, mudah, dan dapat diandalkan.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 animated-element">
                        <div class="card service-card h-100 text-center border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="service-icon mx-auto mb-3"><i class="bi bi-file-earmark-text"></i></div>
                                <h3 class="h5 fw-bold mb-2">Surat Keterangan</h3>
                                <p class="card-text text-muted">Pengurusan berbagai surat keterangan (KTP, KK, Domisili,
                                    dll) dengan proses yang cepat.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 animated-element">
                        <div class="card service-card h-100 text-center border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="service-icon mx-auto mb-3"><i class="bi bi-house-door"></i></div>
                                <h3 class="h5 fw-bold mb-2">Administrasi Desa</h3>
                                <p class="card-text text-muted">Pelayanan administrasi kependudukan, data desa, dan
                                    keperluan lainnya secara online & offline.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 animated-element">
                        <div class="card service-card h-100 text-center border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="service-icon mx-auto mb-3"><i class="bi bi-briefcase"></i></div>
                                <h3 class="h5 fw-bold mb-2">Perizinan Usaha</h3>
                                <p class="card-text text-muted">Bantuan pengurusan izin usaha mikro & kecil (UMK) untuk
                                    mendukung ekonomi masyarakat.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="info" class="info py-5 bg-light">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 animated-element">
                        <h2 class="fw-bold text-start">Komitmen Pelayanan Terbaik</h2>
                        <p class="text-muted">Kami berkomitmen memberikan pelayanan publik yang unggul dengan
                            memanfaatkan teknologi modern untuk efisiensi dan transparansi.</p>
                        <p class="text-muted">Tim kami terdiri dari aparatur desa yang berdedikasi dan terlatih untuk
                            memberikan pelayanan yang ramah, profesional, dan solutif.</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-6 mb-4 animated-element">
                                <div class="stat-item p-3 bg-white rounded shadow-sm text-center"><span
                                        class="stat-number d-block fs-2 fw-bold">1,245</span><span
                                        class="text-muted">Warga Terlayani</span></div>
                            </div>
                            <div class="col-6 mb-4 animated-element">
                                <div class="stat-item p-3 bg-white rounded shadow-sm text-center"><span
                                        class="stat-number d-block fs-2 fw-bold">15</span><span class="text-muted">Jenis
                                        Layanan</span></div>
                            </div>
                            <div class="col-6 animated-element">
                                <div class="stat-item p-3 bg-white rounded shadow-sm text-center"><span
                                        class="stat-number d-block fs-2 fw-bold">24/7</span><span
                                        class="text-muted">Dukungan Online</span></div>
                            </div>
                            <div class="col-6 animated-element">
                                <div class="stat-item p-3 bg-white rounded shadow-sm text-center"><span
                                        class="stat-number d-block fs-2 fw-bold">98%</span><span
                                        class="text-muted">Tingkat Kepuasan</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="galleryModalLabel">Galeri Kegiatan Desa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="" class="img-fluid" alt="Gambar Galeri Diperbesar" id="modalImage">
                    </div>
                </div>
            </div>
        </div>

        <section id="galeri" class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5 animated-element">
                    <h2 class="fw-bold">Galeri Kegiatan Desa</h2>
                    <p class="text-muted">Momen kebersamaan dan pembangunan di Desa Parit Banjar.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4 animated-element">
                        <div class="gallery-item">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal" data-src="img/123.jpg">
                                <img src="img/bantuan-langsung-tunai.jpg" class="img-fluid"
                                    alt="Bantuan Langsung Tunai">
                                <div class="gallery-overlay">
                                    <div class="overlay-content text-center">
                                        <h5 class="overlay-title">Bantuan Langsung Tunai</h5>
                                        <p class="overlay-text">Pemerintah memberikan bantuan uang tunai kepada
                                            masyarakat kurang mampu.</p>
                                        <i class="bi bi-arrows-fullscreen mt-2"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 animated-element">
                        <div class="gallery-item">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal"
                                data-src="img/gotong-royong.jpg">
                                <img src="img/gotong-royong.jpg" class="img-fluid" alt="Gotong Royong">
                                <div class="gallery-overlay">
                                    <div class="overlay-content text-center">
                                        <h5 class="overlay-title">Kerja Bakti Desa</h5>
                                        <p class="overlay-text">Warga bergotong-royong membersihkan lingkungan.</p>
                                        <i class="bi bi-arrows-fullscreen mt-2"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 animated-element">
                        <div class="gallery-item">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal"
                                data-src="img/beras-miskin.jpg">
                                <img src="img/beras-miskin.jpg" class="img-fluid" alt="Beras Miskin">
                                <div class="gallery-overlay">
                                    <div class="overlay-content text-center">
                                        <h5 class="overlay-title">Penyaluran Beras Miskin</h5>
                                        <p class="overlay-text">Pemerintah menyalurkan bantuan beras bersubsidi bagi
                                            masyarakat prasejahtera.</p>
                                        <i class="bi bi-arrows-fullscreen mt-2"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 animated-element">
                        <div class="gallery-item">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal"
                                data-src="img/panen-raya-jagung.jpg">
                                <img src="img/panen-raya-jagung.jpg" class="img-fluid" alt="Panen Raya Jagung">
                                <div class="gallery-overlay">
                                    <div class="overlay-content text-center">
                                        <h5 class="overlay-title">Panen Raya Jagung</h5>
                                        <p class="overlay-text">Para petani memanen hasil tanaman jagung secara serentak
                                            di waktu yang sama.</p>
                                        <i class="bi bi-arrows-fullscreen mt-2"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 animated-element">
                        <div class="gallery-item">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal"
                                data-src="img/panen-semangka.jpg">
                                <img src="img/panen-semangka.jpg" class="img-fluid" alt="Panen Semangka">
                                <div class="gallery-overlay">
                                    <div class="overlay-content text-center">
                                        <h5 class="overlay-title">Panen Semangka</h5>
                                        <p class="overlay-text">Petani memetik buah semangka yang telah matang dari
                                            kebunnya.</p>
                                        <i class="bi bi-arrows-fullscreen mt-2"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 animated-element">
                        <div class="gallery-item">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal"
                                data-src="img/penyuluhan-b2sa.jpg">
                                <img src="img/penyuluhan-b2sa.jpg" class="img-fluid" alt="Penyuluhan B2SA">
                                <div class="gallery-overlay">
                                    <div class="overlay-content text-center">
                                        <h5 class="overlay-title">Penyuluhan B2SA</h5>
                                        <p class="overlay-text">Edukasi kepada masyarakat tentang pentingnya pola makan
                                            Beragam, Bergizi Seimbang, dan Aman (B2SA).</p>
                                        <i class="bi bi-arrows-fullscreen mt-2"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>


        <!-- <section id="testimoni" class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5 animated-element">
                    <h2 class="fw-bold">Apa Kata Warga Kami</h2>
                    <p class="text-muted">Kepuasan Anda adalah prioritas utama kami.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-6 animated-element">
                        <div class="card testimonial-card h-100">
                            <div class="card-body">
                                <p class="fst-italic">"Pelayanan surat keterangan domisili sekarang jauh lebih cepat dan
                                    tidak berbelit-belit. Sangat membantu kami yang punya banyak kesibukan."</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 d-flex align-items-center">
                                <img src="img/IMG_1006.JPG" class="rounded-circle me-3" alt="Foto Warga">
                                <div>
                                    <h6 class="mb-0 fw-bold">Budi Santoso</h6>
                                    <small class="text-muted">Warga RT 02/RW 01</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 animated-element">
                        <div class="card testimonial-card h-100">
                            <div class="card-body">
                                <p class="fst-italic">"Website desa ini sangat informatif. Semua pengumuman penting jadi
                                    mudah diakses. Terima kasih kepada aparatur desa yang sudah bekerja keras."</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 d-flex align-items-center">
                                <img src="img/IMG_1006.JPG" class="rounded-circle me-3" alt="Foto Warga">
                                <div>
                                    <h6 class="mb-0 fw-bold">Siti Aminah</h6>
                                    <small class="text-muted">Pengurus PKK Desa</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->

        <section class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Lokasi Kantor Desa</h2>
                    <p class="text-muted">Temukan kami dengan mudah melalui peta di bawah ini.</p>
                </div>
                <div class="ratio ratio-16x9 shadow-lg" style="border-radius: 0.5rem;">
                    <iframe src="https://maps.google.com/maps?q=0.3056276509430253,109.01770062412&z=17&output=embed"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </section>

        <section id="kontak" class="contact py-5 bg-dark text-white">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-white">Hubungi Kami</h2>
                    <p class="text-white-50">Kami siap membantu Anda. Kunjungi kami atau hubungi melalui kontak di
                        bawah.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h3 class="h5 mb-3">Informasi Kontak</h3>
                        <ul class="list-unstyled">
                            <li class="d-flex mb-3"><i
                                    class="bi bi-geo-alt-fill fs-4 me-3 text-primary"></i><span><strong>Alamat:</strong><br>Jl.
                                    Raya Desa Parit Banjar No. 123, Kec. Mempawah Hilir, Kab. Mempawah</span></li>
                            <li class="d-flex mb-3"><i
                                    class="bi bi-telephone-fill fs-4 me-3 text-primary"></i><span><strong>Telepon:</strong><br>(0561)
                                    123-456 / 0812-3456-7890</span></li>
                            <li class="d-flex"><i
                                    class="bi bi-envelope-fill fs-4 me-3 text-primary"></i><span><strong>Email:</strong><br>kontak@paritbanjar.desa.id</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <h3 class="h5 mb-3">Jam Pelayanan Kantor</h3>
                        <ul class="list-unstyled">
                            <li class="d-flex mb-3"><i
                                    class="bi bi-calendar-week-fill fs-4 me-3 text-primary"></i><span><strong>Senin -
                                        Jumat:</strong><br>08:00 - 15:00 WIB</span></li>
                            <li class="d-flex mb-3"><i
                                    class="bi bi-calendar-x-fill fs-4 me-3 text-primary"></i><span><strong>Sabtu &
                                        Minggu:</strong><br>Tutup (Layanan online tetap tersedia)</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-4 text-center text-white-50">
        <div class="container">
            <p class="mb-0">&copy; 2025 Desa Parit Banjar. All rights reserved.</p>
        </div>
    </footer>

    <a href="#home" class="floating-btn btn btn-primary rounded-circle shadow-lg" title="Kembali ke Atas">
        <i class="bi bi-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- Penanganan Modal Galeri ---
        const galleryModal = document.getElementById('galleryModal');
        if (galleryModal) {
            galleryModal.addEventListener('show.bs.modal', function (event) {
                // Tombol/link yang memicu modal
                const triggerLink = event.relatedTarget;

                // Ekstrak URL gambar dari atribut data-src
                const imageUrl = triggerLink.getAttribute('data-src');

                // Dapatkan elemen gambar di dalam modal
                const modalImage = galleryModal.querySelector('#modalImage');

                // Perbarui atribut src dari gambar modal
                modalImage.src = imageUrl;
            });
        }
    </script>
    <script src="script.js"></script>
</body>

</html>