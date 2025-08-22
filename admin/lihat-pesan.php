<?php
session_start();
require_once '../back-end/config.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../masuk.php");
    exit;
}

// === LOGIKA AKSI (TANDAI BACA & HAPUS) ===
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    if ($action == 'baca') {
        $stmt = $conn->prepare("UPDATE kontak_masuk SET status = 'Dibaca' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Pesan telah ditandai sebagai dibaca.";
        }
        $stmt->close();
    }

    if ($action == 'hapus') {
        $stmt = $conn->prepare("DELETE FROM kontak_masuk WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Pesan berhasil dihapus.";
        }
        $stmt->close();
    }

    header("Location: lihat-pesan.php");
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Masuk - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            transition: all 0.3s;
        }

        @media (max-width: 992px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
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
                background: #fff;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            }
        }

        /* Tampilan Layout Email */
        .inbox-wrapper {
            display: grid;
            grid-template-columns: 350px 1fr;
            /* Sedikit lebih lebar untuk daftar */
            gap: 1.5rem;
            height: calc(100vh - 200px);
            /* Ketinggian disesuaikan */
        }

        .message-list-pane,
        .message-detail-pane {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #e3e6f0;
            display: flex;
            flex-direction: column;
        }

        .pane-header {
            padding: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }

        .message-list {
            overflow-y: auto;
            padding: 0.5rem;
        }

        .message-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            border-left: 4px solid transparent;
            transition: background-color 0.2s ease;
            margin: 0.25rem;
        }

        .message-item:hover {
            background-color: #f8f9fa;
        }

        .message-item.active {
            background-color: #e9f2ff;
            border-left-color: #0d6efd;
        }

        .message-item.status-baru .fw-bold,
        .message-item.status-baru .text-truncate {
            font-weight: 600 !important;
            color: #212529 !important;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }

        .detail-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e3e6f0;
        }

        .detail-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex-grow: 1;
            line-height: 1.8;
        }

        .detail-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e3e6f0;
            background-color: #f8f9fa;
        }

        .empty-pane {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            flex-direction: column;
            color: #6c757d;
        }

        @media (max-width: 992px) {
            .inbox-wrapper {
                grid-template-columns: 1fr;
                height: auto;
            }

            .message-list-pane {
                height: auto;
                margin-bottom: 1.5rem;
            }

            .message-detail-pane {
                min-height: 300px;
            }
        }
    </style>
</head>

<body>
    <nav class="top-navbar navbar p-3 d-lg-none">
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
                    <a class="nav-link active d-flex justify-content-between align-items-center" href="lihat-pesan.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h1 class="h3 text-dark mb-1">Kotak Masuk</h1>
                        <p class="text-muted mb-0">Anda memiliki <?php echo $jumlah_pesan_baru; ?> pesan belum dibaca.
                        </p>
                    </div>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($semua_pesan)): ?>
                    <div class="text-center p-5 bg-white rounded mt-4 border">
                        <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-626.jpg"
                            alt="Tidak ada data" class="img-fluid" style="max-width: 250px;">
                        <h4 class="mt-4 fw-bold">Kotak Masuk Anda Kosong</h4>
                        <p class="text-muted">Tidak ada pesan yang perlu dilihat saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="inbox-wrapper">
                        <div class="message-list-pane">
                            <div class="pane-header">
                                <h6 class="mb-0">Semua Pesan (<?php echo count($semua_pesan); ?>)</h6>
                            </div>
                            <div class="message-list">
                                <?php foreach ($semua_pesan as $pesan): ?>
                                    <div class="message-item <?php echo strtolower($pesan['status']); ?>"
                                        data-id="<?php echo $pesan['id']; ?>"
                                        data-subjek="<?php echo htmlspecialchars($pesan['subjek']); ?>"
                                        data-nama="<?php echo htmlspecialchars($pesan['nama']); ?>"
                                        data-email="<?php echo htmlspecialchars($pesan['email']); ?>"
                                        data-tanggal="<?php echo date('d F Y, H:i', strtotime($pesan['tanggal_kirim'])); ?>"
                                        data-pesan="<?php echo htmlspecialchars($pesan['pesan']); ?>"
                                        data-status="<?php echo $pesan['status']; ?>">
                                        <div class="avatar me-3 bg-primary-subtle text-primary">
                                            <?php echo strtoupper(substr($pesan['nama'], 0, 1)); ?>
                                        </div>
                                        <div class="w-100 overflow-hidden">
                                            <div class="d-flex justify-content-between">
                                                <div class="fw-bold text-truncate">
                                                    <?php echo htmlspecialchars($pesan['nama']); ?>
                                                </div>
                                                <small
                                                    class="text-muted flex-shrink-0 ms-2 small"><?php echo date('d M', strtotime($pesan['tanggal_kirim'])); ?></small>
                                            </div>
                                            <div class="text-truncate small"><?php echo htmlspecialchars($pesan['subjek']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="message-detail-pane">
                            <div id="detail-view">
                                <div class="detail-header">
                                    <h5 id="detail-subjek" class="mb-1"></h5>
                                    <p class="mb-0 text-muted small">
                                        Dari: <strong id="detail-nama"></strong> (<span id="detail-email"></span>)
                                    </p>
                                    <p class="mb-0 text-muted small" id="detail-tanggal"></p>
                                </div>
                                <div class="detail-body" id="detail-pesan"></div>
                                <div class="detail-footer text-end" id="detail-actions"></div>
                            </div>
                            <div id="empty-view" class="empty-pane d-none">
                                <i class="bi bi-inbox fs-1"></i>
                                <h5 class="mt-3">Pilih pesan untuk dibaca</h5>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector(".sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebarOverlay = document.getElementById("sidebarOverlay");

            if (sidebarToggle) {
                sidebarToggle.addEventListener("click", () => sidebar.classList.toggle("active"));
                sidebarOverlay.addEventListener("click", () => sidebar.classList.remove("active"));
            }

            const messageItems = document.querySelectorAll('.message-item');
            const detailView = document.getElementById('detail-view');
            const emptyView = document.getElementById('empty-view');

            if (messageItems.length > 0) {
                const showMessageDetails = (item) => {
                    messageItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    const data = item.dataset;
                    document.getElementById('detail-subjek').textContent = data.subjek;
                    document.getElementById('detail-nama').textContent = data.nama;
                    document.getElementById('detail-email').textContent = data.email;
                    document.getElementById('detail-tanggal').textContent = `Pada: ${data.tanggal}`;
                    document.getElementById('detail-pesan').textContent = data.pesan;

                    const actionsContainer = document.getElementById('detail-actions');
                    let actionsHTML = '';
                    if (data.status === 'Baru') {
                        actionsHTML += `<a href="lihat-pesan.php?action=baca&id=${data.id}" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg me-1"></i> Tandai Dibaca</a> `;
                    }
                    actionsHTML += `<a href="lihat-pesan.php?action=hapus&id=${data.id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pesan ini?');"><i class="bi bi-trash me-1"></i> Hapus</a>`;
                    actionsContainer.innerHTML = actionsHTML;
                };

                messageItems.forEach(item => {
                    item.addEventListener('click', () => showMessageDetails(item));
                });

                showMessageDetails(messageItems[0]);
            } else {
                if (detailView) detailView.classList.add('d-none');
                if (emptyView) emptyView.classList.remove('d-none');
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector(".sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebarOverlay = document.getElementById("sidebarOverlay");

            if (sidebarToggle) {
                sidebarToggle.addEventListener("click", () => sidebar.classList.toggle("active"));
                sidebarOverlay.addEventListener("click", () => sidebar.classList.remove("active"));
            }

            const messageItems = document.querySelectorAll('.message-item');
            const detailView = document.getElementById('detail-view');
            const emptyView = document.getElementById('empty-view');
            const actionsContainer = document.getElementById('detail-actions'); // Definisikan di sini

            if (messageItems.length > 0) {
                const showMessageDetails = (item) => {
                    messageItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    const data = item.dataset;
                    document.getElementById('detail-subjek').textContent = data.subjek;
                    document.getElementById('detail-nama').textContent = data.nama;
                    document.getElementById('detail-email').textContent = data.email;
                    document.getElementById('detail-tanggal').textContent = `Pada: ${data.tanggal}`;
                    document.getElementById('detail-pesan').textContent = data.pesan;

                    let actionsHTML = '';
                    if (data.status === 'Baru') {
                        actionsHTML += `<a href="lihat-pesan.php?action=baca&id=${data.id}" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg me-1"></i> Tandai Dibaca</a> `;
                    }

                    // UBAH: Tombol hapus sekarang menjadi <button> dengan data-id
                    // href dan onclick dihapus dari sini.
                    actionsHTML += `<button type="button" class="btn btn-sm btn-outline-danger btn-hapus" data-id="${data.id}"><i class="bi bi-trash me-1"></i> Hapus</button>`;

                    actionsContainer.innerHTML = actionsHTML;
                };

                messageItems.forEach(item => {
                    item.addEventListener('click', () => showMessageDetails(item));
                });

                showMessageDetails(messageItems[0]);

            } else {
                if (detailView) detailView.classList.add('d-none');
                if (emptyView) emptyView.classList.remove('d-none');
            }

            // BARU: Event listener untuk menangani klik tombol hapus dengan SweetAlert
            actionsContainer.addEventListener('click', function (event) {
                // Cek apakah yang diklik adalah tombol hapus atau elemen di dalamnya
                const deleteButton = event.target.closest('.btn-hapus');

                if (deleteButton) {
                    event.preventDefault(); // Mencegah aksi default
                    const messageId = deleteButton.dataset.id;

                    Swal.fire({
                        title: 'Anda Yakin?',
                        text: "Pesan yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Jika admin mengklik "Ya, hapus!", arahkan ke URL penghapusan
                            window.location.href = `lihat-pesan.php?action=hapus&id=${messageId}`;
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>