<?php
session_start();
require_once 'back-end/config.php';

// --- Keamanan: Pastikan pengguna sudah login ---
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['login_error'] = "Anda harus login untuk melihat riwayat pengaduan.";
    header("Location: masuk.php");
    exit;
}

// --- Ambil data pengguna dan riwayat pengaduan ---
$user_id = $_SESSION['user_id'];
$profile_pic_path = 'img/default-avatar.png';
$pengaduan_list = [];

// Ambil path foto profil pengguna
$stmt_user = mysqli_prepare($conn, "SELECT profile_picture FROM users WHERE id = ?");
if ($stmt_user) {
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    if ($user = mysqli_fetch_assoc($result_user)) {
        if (!empty($user['profile_picture'])) {
            $profile_pic_path = $user['profile_picture'];
        }
    }
    mysqli_stmt_close($stmt_user);
}

// Ambil data pengaduan
$sql_pengaduan = "SELECT id, nama_lengkap, nik, dusun, jenis_pengaduan, tanggal_pengaduan, status, isi_pengaduan, lampiran_path 
                  FROM pengaduan 
                  WHERE user_id = ? 
                  ORDER BY tanggal_pengaduan DESC";

$stmt_pengaduan = mysqli_prepare($conn, $sql_pengaduan);
if ($stmt_pengaduan) {
    mysqli_stmt_bind_param($stmt_pengaduan, "i", $user_id);
    mysqli_stmt_execute($stmt_pengaduan);
    $result_pengaduan = mysqli_stmt_get_result($stmt_pengaduan);
    while ($row = mysqli_fetch_assoc($result_pengaduan)) {
        $pengaduan_list[] = $row;
    }
    mysqli_stmt_close($stmt_pengaduan);
}

// === AMBIL TIMESTAMP AWAL UNTUK AUTO-REFRESH ===
$initial_timestamp = '1970-01-01 00:00:00';
$result_ts = $conn->query("SELECT MAX(last_updated) as latest FROM pengaduan");
if ($result_ts && $result_ts->num_rows > 0) {
    $row_ts = $result_ts->fetch_assoc();
    if ($row_ts['latest']) {
        $initial_timestamp = $row_ts['latest'];
    }
}


// Fungsi untuk menentukan warna badge status
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Diajukan':
            return 'bg-primary';
        case 'Diproses':
            return 'bg-warning text-dark';
        case 'Selesai':
            return 'bg-success';
        case 'Ditolak':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengaduan - Desa Parit Banjar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="img/logo-mempawah.png" type="image/png">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex-grow: 1;
        }

        .card-pengaduan {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out, border-left-color 0.5s ease;
            border-left: 5px solid #0d6efd;
        }

        .card-pengaduan:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-pengaduan.status-diproses {
            border-left-color: #ffc107;
        }

        .card-pengaduan.status-selesai {
            border-left-color: #198754;
        }

        .card-pengaduan.status-ditolak {
            border-left-color: #dc3545;
        }

        .no-data-card {
            background: url('img/no-data.svg') no-repeat center center;
            background-size: contain;
            min-height: 400px;
        }

        .detail-item {
            margin-bottom: 0.75rem;
        }

        .detail-item strong {
            display: block;
            color: #6c757d;
            font-size: 0.85rem;
        }

        @media (max-width: 767px) {
            .btn-pengaduan-baru {
                position: fixed;
                bottom: 90px;
                right: 20px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                z-index: 1050;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body data-timestamp="<?php echo htmlspecialchars($initial_timestamp); ?>">

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
                    <li class="nav-item"><a class="nav-link active" href="riwayatpengaduan.php">Riwayat Pengaduan</a>
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

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4 page-header">
            <h1 class="h2 fw-bold">Riwayat Pengaduan Saya</h1>
            <a href="pengaduan.php" class="btn btn-primary btn-pengaduan-baru">
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-md-inline ms-2">Buat Pengaduan Baru</span>
            </a>
        </div>

        <?php if (empty($pengaduan_list)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5 no-data-card">
                    <h3 class="mt-4">Belum Ada Riwayat</h3>
                    <p class="text-muted">Anda belum pernah membuat pengaduan. Silakan buat pengaduan baru.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($pengaduan_list as $pengaduan): ?>
                    <?php $status_class = strtolower(str_replace(' ', '-', $pengaduan['status'])); ?>
                    <div class="col-md-6 col-lg-4">
                        <div id="pengaduan-<?php echo $pengaduan['id']; ?>"
                            class="card h-100 shadow-sm border-0 card-pengaduan status-<?php echo $status_class; ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <span id="status-badge-<?php echo $pengaduan['id']; ?>"
                                        class="badge <?php echo getStatusBadgeClass($pengaduan['status']); ?>"><?php echo htmlspecialchars($pengaduan['status']); ?></span>
                                    <small
                                        class="text-muted float-end"><?php echo date('d M Y, H:i', strtotime($pengaduan['tanggal_pengaduan'])); ?></small>
                                </div>
                                <h5 class="card-title fw-semibold">
                                    <?php echo htmlspecialchars($pengaduan['jenis_pengaduan']); ?>
                                </h5>
                                <p class="card-text text-muted small flex-grow-1">
                                    <?php echo htmlspecialchars(substr($pengaduan['isi_pengaduan'], 0, 100)) . (strlen($pengaduan['isi_pengaduan']) > 100 ? '...' : ''); ?>
                                </p>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-auto" data-bs-toggle="modal"
                                    data-bs-target="#detailModal" data-id="PB-<?php echo htmlspecialchars($pengaduan['id']); ?>"
                                    data-nama="<?php echo htmlspecialchars($pengaduan['nama_lengkap']); ?>"
                                    data-nik="<?php echo htmlspecialchars($pengaduan['nik']); ?>"
                                    data-dusun="<?php echo htmlspecialchars(ucwords($pengaduan['dusun'])); ?>"
                                    data-jenis="<?php echo htmlspecialchars($pengaduan['jenis_pengaduan']); ?>"
                                    data-tanggal="<?php echo date('d F Y, H:i', strtotime($pengaduan['tanggal_pengaduan'])); ?>"
                                    data-status="<?php echo htmlspecialchars($pengaduan['status']); ?>"
                                    data-status-class="<?php echo getStatusBadgeClass($pengaduan['status']); ?>"
                                    data-isi="<?php echo htmlspecialchars($pengaduan['isi_pengaduan']); ?>"
                                    data-lampiran="<?php echo htmlspecialchars($pengaduan['lampiran_path'] ?? ''); ?>">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pengaduan: <span id="modal-id"
                            class="fw-bold"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-primary">Data Pelapor</h6>
                    <div class="row">
                        <div class="col-md-4 detail-item"><strong>Nama Lengkap</strong><span id="modal-nama"></span>
                        </div>
                        <div class="col-md-4 detail-item"><strong>NIK</strong><span id="modal-nik"></span></div>
                        <div class="col-md-4 detail-item"><strong>Dusun</strong><span id="modal-dusun"></span></div>
                    </div>
                    <hr>
                    <h6 class="text-primary mt-3">Detail Laporan</h6>
                    <div class="row">
                        <div class="col-md-4 detail-item"><strong>Jenis Pengaduan</strong><span id="modal-jenis"></span>
                        </div>
                        <div class="col-md-4 detail-item"><strong>Tanggal Lapor</strong><span id="modal-tanggal"></span>
                        </div>
                        <div class="col-md-4 detail-item"><strong>Status</strong><span id="modal-status"
                                class="badge"></span></div>
                    </div>
                    <div class="detail-item mt-2"><strong>Isi Pengaduan</strong>
                        <p id="modal-isi" class="bg-light p-3 rounded mt-1" style="white-space: pre-wrap;"></p>
                    </div>
                    <div class="detail-item mt-2"><strong>Lampiran</strong>
                        <div id="modal-lampiran-container" class="mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button></div>
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
            const detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const data = button.dataset;

                detailModal.querySelector('#modal-id').textContent = data.id;
                detailModal.querySelector('#modal-jenis').textContent = data.jenis;
                detailModal.querySelector('#modal-tanggal').textContent = data.tanggal;
                const modalStatus = detailModal.querySelector('#modal-status');
                modalStatus.textContent = data.status;
                modalStatus.className = 'badge ' + data.statusClass;
                detailModal.querySelector('#modal-isi').textContent = data.isi;
                detailModal.querySelector('#modal-nama').textContent = data.nama;
                detailModal.querySelector('#modal-nik').textContent = data.nik;
                detailModal.querySelector('#modal-dusun').textContent = data.dusun;

                const lampiranContainer = detailModal.querySelector('#modal-lampiran-container');
                if (data.lampiran) {
                    lampiranContainer.innerHTML = `<a href="${data.lampiran}" target="_blank" class="btn btn-outline-success btn-sm"><i class="bi bi-paperclip"></i> Lihat Lampiran</a>`;
                } else {
                    lampiranContainer.innerHTML = '<p class="text-muted mb-0">Tidak ada lampiran.</p>';
                }
            });

            // --- KODE AUTO REFRESH HALAMAN ---

            function cekPerubahanDatabase() {
                const body = document.body;
                const lastKnownTimestamp = body.dataset.timestamp;

                // Arahkan fetch ke folder back-end
                fetch(`back-end/cek_perubahan.php?timestamp=${encodeURIComponent(lastKnownTimestamp)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.refresh) {
                            console.log('Perubahan terdeteksi di database. Me-refresh halaman...');
                            location.reload();
                        } else {
                            body.dataset.timestamp = data.new_timestamp;
                        }
                    })
                    .catch(error => console.error('Gagal memeriksa perubahan:', error));
            }

            // Jalankan pengecekan setiap 7 detik (7000 milidetik)
            setInterval(cekPerubahanDatabase, 7000);
        });
    </script>
</body>

</html>