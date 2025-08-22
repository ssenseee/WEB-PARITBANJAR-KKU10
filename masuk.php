<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Desa Parit Banjar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="img/logo-mempawah.png" type="image/png">

    <style>
        .auth-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/halaman-depan.JPG') center center/cover no-repeat;
        }

        .auth-card {
            width: 100%;
            max-width: 680px;
            border: none;
            border-radius: 0.75rem;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
        }

        .btn-costum {
            background: #001BB7;
            color: white;
            border: none;
        }

        .btn-costum:hover {
            background: #2fa500ff;
            transition: background-color 0.6s ease;
            color: white;
            border: none;
        }
    </style>
</head>

<body>
    <div class="auth-section px-3">
        <div class="card shadow-lg auth-card">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    <a href="home.php">
                        <img src="img/logo-mempawah.png" alt="Logo" width="70" class="mb-3">
                    </a>
                    <h3 class="fw-bold">Masuk ke Akun Anda</h3>
                    <p class="text-muted">Selamat datang kembali!</p>
                </div>

                <?php
                // Periksa apakah ada pesan error login di sesi
                if (isset($_SESSION['login_error'])) {
                    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['login_error'] . '</div>';
                    unset($_SESSION['login_error']); // Hapus pesan setelah ditampilkan
                }
                ?>

                <form action="back-end/proses_masuk.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Ingat Saya
                            </label>
                        </div>
                        <a href="lupakatasandi.php" class="form-text text-decoration-none">Lupa Kata Sandi?</a>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-costum">Masuk</button>
                    </div>
                </form>

                <p class="mt-4 text-center text-muted">Belum punya akun? <a href="daftar.php">Daftar di sini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>