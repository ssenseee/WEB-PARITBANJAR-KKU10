<?php
session_start();
require_once '../back-end/config.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../masuk.php");
    exit;
}

// === HITUNG JUMLAH PESAN & PENGADUAN BARU UNTUK NOTIFIKASI ===
$jumlah_pesan_baru = 0;
$sql_pesan = "SELECT COUNT(id) as total FROM kontak_masuk WHERE status = 'Baru'";
$result_pesan = $conn->query($sql_pesan);
if ($result_pesan) {
    $data_pesan = $result_pesan->fetch_assoc();
    $jumlah_pesan_baru = $data_pesan['total'];
}

$jumlah_pengaduan_baru = 0;
$sql_pengaduan_notif = "SELECT COUNT(id) as total FROM pengaduan WHERE status = 'Diajukan'";
$result_pengaduan_notif = $conn->query($sql_pengaduan_notif);
if ($result_pengaduan_notif) {
    $data_pengaduan = $result_pengaduan_notif->fetch_assoc();
    $jumlah_pengaduan_baru = $data_pengaduan['total'];
}


// Logika untuk UPDATE status pengaduan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pengaduan_id = $_POST['pengaduan_id'];
    $new_status = $_POST['status'];

    $allowed_statuses = ['Diajukan', 'Diproses', 'Selesai', 'Ditolak'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $pengaduan_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Status pengaduan berhasil diperbarui.";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui status.";
        }
        $stmt->close();
    }
    header("Location: kelola-pengaduan.php");
    exit;
}



// Ambil semua data pengaduan dari database
$semua_pengaduan = [];
$sql = "SELECT id, nama_lengkap, nik, dusun, jenis_pengaduan, tanggal_pengaduan, status, isi_pengaduan, lampiran_path 
        FROM pengaduan 
        ORDER BY FIELD(status, 'Diajukan', 'Diproses', 'Selesai', 'Ditolak'), tanggal_pengaduan DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $semua_pengaduan[] = $row;
    }
}

// Fungsi untuk badge status
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
    <title>Kelola Pengaduan - Admin Panel</title>
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
            transition: all 0.3s;
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
            transition: all 0.3s;
        }

        .top-navbar {
            display: none;
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        @media (max-width: 992px) {
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

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .search-wrapper {
            position: relative;
        }

        .search-wrapper .form-control {
            padding-left: 2.375rem;
        }

        .search-wrapper .bi-search {
            position: absolute;
            top: 50%;
            left: 0.75rem;
            transform: translateY(-50%);
            color: #6c757d;
        }
    </style>
</head>

<body>
    <nav class="top-navbar navbar p-3">
        <div class="container-fluid">
            <button class="btn btn-dark" type="button" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <span class="navbar-brand mb-0 h1 fw-bold text-dark">Admin Panel</span>
        </div>
    </nav>
    <div class="wrapper">
        <nav class="sidebar p-3">
            <div class="sidebar-header d-flex align-items-center mb-3">
                <img src="../img/logo-mempawah.png" alt="Logo" width="45" height="45" class="me-2">
                <span class="fw-bold fs-5 text-white">Desa Parit Banjar</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-grid-fill"></i>
                        Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="kelola-pengguna.php"><i class="bi bi-people-fill"></i>
                        Kelola Pengguna</a></li>
                <li class="nav-item">
                    <a class="nav-link active d-flex justify-content-between align-items-center"
                        href="kelola-pengaduan.php">
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
                <div class="mb-4">
                    <h1 class="h3 text-dark">Kelola Pengaduan</h1>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="m-0">Daftar Semua Pengaduan</h6>
                        <div class="search-wrapper w-auto"><i class="bi bi-search"></i><input type="text"
                                id="searchInput" class="form-control form-control-sm" placeholder="Cari data..."></div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="pengaduanTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pelapor</th>
                                        <th>Jenis Pengaduan</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($semua_pengaduan)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-626.jpg"
                                                    alt="Tidak ada data" style="max-width: 180px;">
                                                <h5 class="mt-3">Belum Ada Data Pengaduan</h5>
                                            </td>
                                        </tr>
                                    <?php else:
                                        foreach ($semua_pengaduan as $p): ?>
                                            <tr>
                                                <td><strong>PB-<?php echo $p['id']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($p['nama_lengkap']); ?></td>
                                                <td><?php echo htmlspecialchars($p['jenis_pengaduan']); ?></td>
                                                <td><?php echo date('d M Y, H:i', strtotime($p['tanggal_pengaduan'])); ?></td>
                                                <td><span
                                                        class="badge rounded-pill <?php echo getStatusBadgeClass($p['status']); ?>"><?php echo htmlspecialchars($p['status']); ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-info" title="Lihat Detail"
                                                            data-bs-toggle="modal" data-bs-target="#detailModal"
                                                            data-id="PB-<?php echo $p['id']; ?>"
                                                            data-nama="<?php echo htmlspecialchars($p['nama_lengkap']); ?>"
                                                            data-nik="<?php echo htmlspecialchars($p['nik']); ?>"
                                                            data-dusun="<?php echo htmlspecialchars(ucwords($p['dusun'])); ?>"
                                                            data-jenis="<?php echo htmlspecialchars($p['jenis_pengaduan']); ?>"
                                                            data-tanggal="<?php echo date('d F Y, H:i', strtotime($p['tanggal_pengaduan'])); ?>"
                                                            data-status="<?php echo htmlspecialchars($p['status']); ?>"
                                                            data-status-class="<?php echo getStatusBadgeClass($p['status']); ?>"
                                                            data-isi="<?php echo htmlspecialchars($p['isi_pengaduan']); ?>"
                                                            data-lampiran="<?php echo htmlspecialchars($p['lampiran_path'] ?? ''); ?>">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" title="Ubah Status"
                                                            data-bs-toggle="modal" data-bs-target="#updateStatusModal"
                                                            data-id="<?php echo $p['id']; ?>"
                                                            data-current-status="<?php echo htmlspecialchars($p['status']); ?>">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengaduan: <span id="modal-id" class="fw-bold"></span></h5><button
                        type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-primary">Data Pelapor</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Nama:</strong><br><span id="modal-nama"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>NIK:</strong><br><span id="modal-nik"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Dusun:</strong><br><span id="modal-dusun"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-primary mt-3">Detail Laporan</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Jenis:</strong><br><span id="modal-jenis"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Tanggal:</strong><br><span id="modal-tanggal"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Status:</strong><br><span id="modal-status" class="badge"></span>
                            </p>
                        </div>
                    </div>
                    <p class="mt-2 mb-1"><strong>Isi Pengaduan:</strong></p>
                    <p id="modal-isi" class="bg-light p-3 rounded" style="white-space: pre-wrap;"></p>
                    <p class="mb-1"><strong>Lampiran:</strong></p>
                    <div id="modal-lampiran-container"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status Pengaduan</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <form action="kelola-pengaduan.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="pengaduan_id" id="update-pengaduan-id">
                        <div class="mb-3">
                            <label for="status-select" class="form-label">Pilih Status Baru:</label>
                            <select class="form-select" name="status" id="status-select" required>
                                <option value="Diajukan">Diajukan</option>
                                <option value="Diproses">Diproses</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button><button type="submit" name="update_status"
                            class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            const sidebar = document.querySelector(".sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebarOverlay = document.getElementById("sidebarOverlay");
            if (sidebarToggle) {
                sidebarToggle.addEventListener("click", function () { sidebar.classList.toggle("active"); });
                sidebarOverlay.addEventListener("click", function () { sidebar.classList.remove("active"); });
            }

            const detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', e => {
                const data = e.relatedTarget.dataset;
                detailModal.querySelector('#modal-id').textContent = data.id;
                detailModal.querySelector('#modal-nama').textContent = data.nama;
                detailModal.querySelector('#modal-nik').textContent = data.nik;
                detailModal.querySelector('#modal-dusun').textContent = data.dusun;
                detailModal.querySelector('#modal-jenis').textContent = data.jenis;
                detailModal.querySelector('#modal-tanggal').textContent = data.tanggal;
                detailModal.querySelector('#modal-isi').textContent = data.isi;
                const statusBadge = detailModal.querySelector('#modal-status');
                statusBadge.textContent = data.status;
                statusBadge.className = 'badge rounded-pill ' + data.statusClass;
                const lampiranContainer = detailModal.querySelector('#modal-lampiran-container');
                lampiranContainer.innerHTML = data.lampiran && data.lampiran !== '' ? `<a href="../${data.lampiran}" target="_blank" class="btn btn-outline-success btn-sm"><i class="bi bi-paperclip"></i> Lihat Lampiran</a>` : '<p class="text-muted mb-0">Tidak ada lampiran.</p>';
            });

            const updateStatusModal = document.getElementById('updateStatusModal');
            updateStatusModal.addEventListener('show.bs.modal', e => {
                updateStatusModal.querySelector('#update-pengaduan-id').value = e.relatedTarget.dataset.id;
                updateStatusModal.querySelector('#status-select').value = e.relatedTarget.dataset.currentStatus;
            });

            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('#pengaduanTable tbody tr');
            searchInput.addEventListener('keyup', () => {
                const filter = searchInput.value.toLowerCase();
                tableRows.forEach(row => {
                    if (row.querySelector('td')) { // Pastikan bukan baris "tidak ada data"
                        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>