<?php
session_start();
require_once '../back-end/config.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../masuk.php");
    exit;
}

// === LOGIKA UNTUK TAMBAH PENGGUNA (DARI MODAL) ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_pengguna'])) {
    $name = trim($_POST['name']);
    $nik = trim($_POST['nik']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($nik) || empty($email) || empty($no_hp) || empty($password) || empty($role)) {
        $_SESSION['error_message'] = "Semua kolom pada form tambah wajib diisi.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, nik, email, no_hp, password, role, is_verified) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssss", $name, $nik, $email, $no_hp, $hashed_password, $role);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Pengguna baru berhasil ditambahkan.";
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan. NIK, Email, atau No. HP mungkin sudah terdaftar.";
        }
        $stmt->close();
    }
    header("Location: kelola-pengguna.php");
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


// === LOGIKA UNTUK EDIT PENGGUNA (DARI MODAL) ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_pengguna'])) {
    $id = $_POST['user_id'];
    $name = trim($_POST['name']);
    $nik = trim($_POST['nik']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($nik) || empty($email) || empty($no_hp) || empty($role)) {
        $_SESSION['error_message'] = "Semua kolom pada form edit wajib diisi.";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, nik=?, email=?, no_hp=?, password=?, role=? WHERE id=?");
            $stmt->bind_param("ssssssi", $name, $nik, $email, $no_hp, $hashed_password, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, nik=?, email=?, no_hp=?, role=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $nik, $email, $no_hp, $role, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data pengguna berhasil diperbarui.";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui. NIK, Email, atau No. HP mungkin sudah terdaftar.";
        }
        $stmt->close();
    }
    header("Location: kelola-pengguna.php");
    exit;
}


// Ambil semua data pengguna untuk ditampilkan di tabel
$query = "SELECT id, name, nik, email, no_hp, role FROM users ORDER BY name ASC";
$result_pengguna = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin Panel</title>
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

        /* --- KODE CSS BARU DITAMBAHKAN --- */
        .search-wrapper {
            position: relative;
            display: flex;
            flex: 1 1 auto;
        }

        .search-wrapper .form-control {
            padding-left: 2.375rem;
            width: 100%;
        }

        /* --- AKHIR DARI KODE CSS BARU --- */

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
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-grid-fill"></i>
                        Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="kelola-pengguna.php"><i
                            class="bi bi-people-fill"></i>
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
                <div class="mb-4">
                    <h1 class="h3 text-dark">Kelola Pengguna</h1>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h6 class="m-0">Daftar Semua Pengguna</h6>

                        <div
                            class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 w-100 w-md-auto">
                            <div class="search-wrapper flex-grow-1">
                                <i class="bi bi-search"></i>
                                <input type="text" id="searchInput" class="form-control form-control-sm"
                                    placeholder="Cari...">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#tambahPenggunaModal">
                                <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baru
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="penggunaTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Lengkap</th>
                                        <th>NIK</th>
                                        <th>Kontak</th>
                                        <th>Role</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_pengguna->num_rows > 0):
                                        $nomor = 1;
                                        while ($user = $result_pengguna->fetch_assoc()): ?>
                                            <tr>
                                                <th><?php echo $nomor++; ?></th>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['nik']); ?></td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <small><?php echo htmlspecialchars($user['email']); ?></small>
                                                        <small
                                                            class="text-muted"><?php echo htmlspecialchars($user['no_hp']); ?></small>
                                                    </div>
                                                </td>
                                                <td><span
                                                        class="badge rounded-pill <?php echo $user['role'] == 'admin' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo ucfirst($user['role']); ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-warning edit-btn"
                                                            title="Edit Pengguna" data-bs-toggle="modal"
                                                            data-bs-target="#editPenggunaModal"
                                                            data-id="<?php echo $user['id']; ?>">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                        <a href="hapus-pengguna.php?id=<?php echo $user['id']; ?>"
                                                            class="btn btn-outline-danger" title="Hapus Pengguna"
                                                            onclick="return confirm('PERINGATAN: Menghapus pengguna juga akan menghapus semua data pengaduan yang terkait dengannya. Apakah Anda yakin?');">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <img src="https://static.vecteezy.com/system/resources/previews/005/006/031/original/no-data-or-data-not-found-concept-illustration-for-website-and-mobile-app-design-empty-state-and-landing-page-ui-element-vector.jpg"
                                                    alt="Tidak ada data" style="max-width: 180px;">
                                                <h5 class="mt-3">Tidak Ada Data Pengguna</h5>
                                                <p class="text-muted">Silakan tambahkan pengguna baru untuk memulai.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="tambahPenggunaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="kelola-pengguna.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Formulir Tambah Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text"
                                class="form-control" name="name" required></div>
                        <div class="mb-3"><label class="form-label">NIK (16 Digit)</label><input type="text"
                                class="form-control" name="nik" required minlength="16" maxlength="16" pattern="\d{16}">
                        </div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email"
                                class="form-control" name="email" required></div>
                        <div class="mb-3"><label class="form-label">No. Handphone</label><input type="tel"
                                class="form-control" name="no_hp" required></div>
                        <div class="mb-3"><label class="form-label">Password</label><input type="password"
                                class="form-control" name="password" required></div>
                        <div class="mb-3"><label class="form-label">Role</label><select class="form-select" name="role"
                                required>
                                <option value="user" selected>User</option>
                                <option value="admin">Admin</option>
                            </select></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_pengguna" class="btn btn-primary">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPenggunaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="kelola-pengguna.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Formulir Edit Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text"
                                class="form-control" id="edit_name" name="name" required></div>
                        <div class="mb-3"><label class="form-label">NIK (16 Digit)</label><input type="text"
                                class="form-control" id="edit_nik" name="nik" required minlength="16" maxlength="16"
                                pattern="\d{16}"></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email"
                                class="form-control" id="edit_email" name="email" required></div>
                        <div class="mb-3"><label class="form-label">No. Handphone</label><input type="tel"
                                class="form-control" id="edit_no_hp" name="no_hp" required></div>
                        <div class="mb-3"><label class="form-label">Password Baru</label><input type="password"
                                class="form-control" id="edit_password" name="password">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                        </div>
                        <div class="mb-3"><label class="form-label">Role</label><select class="form-select"
                                id="edit_role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_pengguna" class="btn btn-primary">Update Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Sidebar Toggle
            const sidebar = document.querySelector(".sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebarOverlay = document.getElementById("sidebarOverlay");
            if (sidebarToggle) {
                sidebarToggle.addEventListener("click", () => sidebar.classList.toggle("active"));
                sidebarOverlay.addEventListener("click", () => sidebar.classList.remove("active"));
            }

            // AJAX untuk mengisi form edit
            $('.edit-btn').on('click', function () {
                var userId = $(this).data('id');
                $.ajax({
                    url: 'get_pengguna_detail.php', // Pastikan file ini ada untuk mengambil data
                    type: 'GET',
                    data: { id: userId },
                    dataType: 'json',
                    success: function (response) {
                        $('#edit_user_id').val(response.id);
                        $('#edit_name').val(response.name);
                        $('#edit_nik').val(response.nik);
                        $('#edit_email').val(response.email);
                        $('#edit_no_hp').val(response.no_hp);
                        $('#edit_role').val(response.role);
                        $('#edit_password').val('');
                    },
                    error: function () {
                        alert('Gagal memuat data pengguna.');
                    }
                });
            });

            // Live Search
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.querySelector('#penggunaTable tbody');
            const tableRows = tableBody.querySelectorAll('tr');
            const noDataRow = tableBody.querySelector('td[colspan="6"]'); // Ambil baris 'tidak ada data'

            searchInput.addEventListener('keyup', () => {
                const filter = searchInput.value.toLowerCase();
                let visibleRows = 0;

                tableRows.forEach(row => {
                    // Pastikan tidak memfilter baris "tidak ada data"
                    if (row.querySelectorAll('th').length > 0) {
                        const isVisible = row.textContent.toLowerCase().includes(filter);
                        row.style.display = isVisible ? '' : 'none';
                        if (isVisible) {
                            visibleRows++;
                        }
                    }
                });

                // Tampilkan atau sembunyikan pesan 'tidak ada data' berdasarkan hasil filter
                if (noDataRow) {
                    noDataRow.parentElement.style.display = (visibleRows === 0) ? '' : 'none';
                }
            });
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>