<?php
session_start();
require_once '../back-end/config.php';

// Keamanan
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../masuk.php");
    exit;
}

$jumlah_pengaduan_baru = 0;
$sql_pengaduan = "SELECT COUNT(id) as total FROM pengaduan WHERE status = 'Diajukan'";
$result_pengaduan = $conn->query($sql_pengaduan);
if ($result_pengaduan) {
    $data = $result_pengaduan->fetch_assoc();
    $jumlah_pengaduan_baru = $data['total'];
}

// Ambil semua data pesan, urutkan pesan 'Baru' di atas
$semua_pesan = [];
$jumlah_pesan_baru = 0;
$sql = "SELECT id, nama, email, subjek, pesan, tanggal_kirim, status 
        FROM kontak_masuk 
        ORDER BY status ASC, tanggal_kirim DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $semua_pesan[] = $row;
        if ($row['status'] == 'Baru') {
            $jumlah_pesan_baru++;
        }
    }
}

// Pengambilan Data Statistik
$result_total_pengguna = $conn->query("SELECT COUNT(id) AS total FROM users");
$total_pengguna = $result_total_pengguna->fetch_assoc()['total'];

$result_total_layanan = $conn->query("SELECT COUNT(id) AS total FROM pengaduan");
$total_layanan = $result_total_layanan->fetch_assoc()['total'];

$result_layanan_diproses = $conn->query("SELECT COUNT(id) AS total FROM pengaduan WHERE status = 'Diajukan' OR status = 'Diproses'");
$layanan_diproses = $result_layanan_diproses->fetch_assoc()['total'];

$result_layanan_selesai = $conn->query("SELECT COUNT(id) AS total FROM pengaduan WHERE status = 'Selesai'");
$layanan_selesai = $result_layanan_selesai->fetch_assoc()['total'];

// Pengambilan Data Aktivitas Terbaru
$layanan_terbaru = $conn->query("SELECT nama_lengkap, jenis_pengaduan, tanggal_pengaduan, status FROM pengaduan ORDER BY tanggal_pengaduan DESC LIMIT 5");
$pengguna_baru = $conn->query("SELECT name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Desa Parit Banjar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="icon" href="../img/logo-mempawah.png" type="image/png">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
        }

        .wrapper {
            display: flex;
            width: 100%;
        }

        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1020;
            background: #212529;
            transition: all 0.3s ease-in-out;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1010;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.2rem;
            color: #adb5bd;
            font-weight: 500;
        }

        .sidebar .nav-link .bi {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }

        .main-content {
            width: 100%;
            padding: 24px;
            margin-left: 250px;
            transition: all 0.3s ease-in-out;
        }

        .top-navbar {
            display: none;
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            }

            .sidebar.active~.sidebar-overlay {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding-top: 80px;
            }

            .top-navbar {
                display: flex;
                position: fixed;
                top: 0;
                width: 100%;
                z-index: 1000;
            }
        }

        .card-header {
            font-weight: 600;
        }

        .stat-card {
            border: none;
            border-left: 4px solid;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        .stat-card.border-primary {
            border-color: #4e73df;
        }

        .stat-card.border-info {
            border-color: #36b9cc;
        }

        .stat-card.border-warning {
            border-color: #f6c23e;
        }

        .stat-card.border-success {
            border-color: #1cc88a;
        }
    </style>
</head>

<body>

    <nav class="top-navbar navbar p-3">
        <div class="container-fluid">
            <button class="btn btn-dark" type="button" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <span class="navbar-brand mb-0 h1 fw-bold">Admin Panel</span>
        </div>
    </nav>

    <div class="wrapper">
        <nav class="sidebar p-3">
            <div class="sidebar-header d-flex align-items-center mb-3">
                <img src="../img/logo-mempawah.png" alt="Logo" width="45" height="45" class="me-2">
                <span class="fw-bold fs-5 text-white">Desa Parit Banjar</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-grid-fill"></i>
                        Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="kelola-pengguna.php"><i class="bi bi-people-fill"></i>
                        Kelola Pengguna</a></li>

                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="kelola-pengaduan.php">
                        <span><i class="bi bi-file-earmark-text-fill"></i> Kelola Pengaduan</span>
                        <?php if ($jumlah_pengaduan_baru > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $jumlah_pengaduan_baru; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="lihat-pesan.php">
                        <span><i class="bi bi-envelope-fill"></i> Pesan Masuk</span>
                        <?php if ($jumlah_pesan_baru > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $jumlah_pesan_baru; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item mt-auto">
                    <hr class="text-secondary"><a class="nav-link" href="../home.php" target="_blank"><i
                            class="bi bi-house-door-fill"></i> Lihat Situs</a>
                </li>
                <li class="nav-item"><a class="nav-link text-danger" href="../back-end/logout.php"><i
                            class="bi bi-box-arrow-left"></i> Logout</a></li>
            </ul>
        </nav>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="main-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4 page-header">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <span class="d-none d-md-inline">Selamat datang,
                            <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</span>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-primary">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="card-title text-primary text-uppercase small">Total Pengguna</div>
                                    <div class="h4 mb-0 fw-bold"><?php echo $total_pengguna; ?></div>
                                </div><i class="bi bi-people-fill h2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-info">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="card-title text-info text-uppercase small">Total Layanan</div>
                                    <div class="h4 mb-0 fw-bold"><?php echo $total_layanan; ?></div>
                                </div><i class="bi bi-file-earmark-text-fill h2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-warning">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="card-title text-warning text-uppercase small">Perlu Diproses</div>
                                    <div class="h4 mb-0 fw-bold"><?php echo $layanan_diproses; ?></div>
                                </div><i class="bi bi-inbox-fill h2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-success">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="card-title text-success text-uppercase small">Layanan Selesai</div>
                                    <div class="h4 mb-0 fw-bold"><?php echo $layanan_selesai; ?></div>
                                </div><i class="bi bi-check2-circle h2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white"><i class="bi bi-file-earmark-text me-2"></i> Pengajuan
                                Layanan Terbaru</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Pemohon</th>
                                                <th>Jenis Layanan</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($layanan_terbaru->num_rows > 0) {
                                                while ($row = $layanan_terbaru->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['jenis_pengaduan']); ?></td>
                                                        <td><?php echo date('d M Y', strtotime($row['tanggal_pengaduan'])); ?>
                                                        </td>
                                                        <td><?php $status = htmlspecialchars($row['status']);
                                                        $badge_class = 'bg-secondary';
                                                        if ($status == 'Diajukan')
                                                            $badge_class = 'bg-primary';
                                                        if ($status == 'Diproses')
                                                            $badge_class = 'bg-warning text-dark';
                                                        if ($status == 'Selesai')
                                                            $badge_class = 'bg-success';
                                                        if ($status == 'Ditolak')
                                                            $badge_class = 'bg-danger';
                                                        echo "<span class='badge {$badge_class}'>{$status}</span>"; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile;
                                            } else {
                                                echo '<tr><td colspan="4" class="text-center">Belum ada pengajuan layanan.</td></tr>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white"><i class="bi bi-person-plus-fill me-2"></i> Pengguna Baru
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Bergabung</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($pengguna_baru->num_rows > 0) {
                                                while ($row = $pengguna_baru->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                                    </tr>
                                                <?php endwhile;
                                            } else {
                                                echo '<tr><td colspan="3" class="text-center">Belum ada pengguna baru.</td></tr>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector(".sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebarOverlay = document.getElementById("sidebarOverlay");

            sidebarToggle.addEventListener("click", function () {
                sidebar.classList.toggle("active");
            });

            sidebarOverlay.addEventListener("click", function () {
                sidebar.classList.remove("active");
            });
        });
    </script>

</body>

</html>
<?php
$conn->close();
?>