<?php
// lupakatasandi.php (Versi Lengkap dalam Satu Halaman)
session_start();
require 'back-end/config.php';

// ... (Blok PHP Anda tidak ada perubahan, tetap sama seperti sebelumnya) ...
if (isset($_GET['action']) && $_GET['action'] == 'reset') {
    unset($_SESSION['reset_step']);
    unset($_SESSION['no_hp_reset']);
    header('Location: lupakatasandi.php');
    exit();
}
if (!isset($conn) || $conn->connect_error) {
    die("Koneksi ke database gagal. Periksa file config.php Anda. Error: " . (isset($conn) ? $conn->connect_error : 'Variabel $conn tidak ditemukan.'));
}
$error_message = '';
$info_message = '';
$success_message = '';
$step = 1;
if (isset($_SESSION['reset_step']) && $_SESSION['reset_step'] == 2) {
    $step = 2;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_otp'])) {
    $no_hp = $conn->real_escape_string($_POST['no_hp']);
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE no_hp = ?");
    $stmt_check->bind_param("s", $no_hp);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $otp = rand(100000, 999999);
        $waktu_sekarang = time();
        $stmt_otp = $conn->prepare("INSERT INTO otp (nomor, otp, waktu) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE otp = VALUES(otp), waktu = VALUES(waktu)");
        $stmt_otp->bind_param("ssi", $no_hp, $otp, $waktu_sekarang);
        if ($stmt_otp->execute()) {
            $target_no = $no_hp;
            if (substr($target_no, 0, 1) === '0') {
                $target_no = '62' . substr($target_no, 1);
            }
            $curl = curl_init();
            $data = ['target' => $target_no, 'message' => "Kode OTP untuk reset password Anda: " . $otp . "\nJangan berikan kode ini kepada siapapun.", 'countryCode' => '+62'];
            curl_setopt_array($curl, array(CURLOPT_URL => "https://api.fonnte.com/send", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => http_build_query($data), CURLOPT_HTTPHEADER => array("Authorization: " . FONNTE_TOKEN), ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                $error_message = "Gagal mengirim OTP. cURL Error: " . $err;
            } else {
                $info_message = "OTP telah dikirim ke nomor WhatsApp Anda. Silakan periksa pesan masuk.";
                $_SESSION['no_hp_reset'] = $no_hp;
                $_SESSION['reset_step'] = 2;
                $step = 2;
            }
        } else {
            $error_message = "Gagal menyimpan OTP di database. Silakan coba lagi.";
        }
        $stmt_otp->close();
    } else {
        $error_message = "Nomor HP tidak terdaftar.";
    }
    $stmt_check->close();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    if (!isset($_SESSION['no_hp_reset'])) {
        $error_message = "Sesi Anda telah berakhir. Silakan ulangi dari awal.";
        $step = 1;
        session_destroy();
    } else {
        $no_hp = $_SESSION['no_hp_reset'];
        $otp_input = $conn->real_escape_string($_POST['otp']);
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];
        if ($password_baru !== $konfirmasi_password) {
            $error_message = "Konfirmasi password tidak cocok.";
            $step = 2;
        } elseif (strlen($password_baru) < 8) {
            $error_message = "Password minimal harus 8 karakter.";
            $step = 2;
        } else {
            $stmt_check_otp = $conn->prepare("SELECT otp, waktu FROM otp WHERE nomor = ?");
            $stmt_check_otp->bind_param("s", $no_hp);
            $stmt_check_otp->execute();
            $result_otp = $stmt_check_otp->get_result();
            if ($result_otp->num_rows > 0) {
                $row = $result_otp->fetch_assoc();
                if ($otp_input == $row['otp'] && (time() - $row['waktu']) <= 300) {
                    $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                    $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE no_hp = ?");
                    $stmt_update->bind_param("ss", $hashed_password, $no_hp);
                    if ($stmt_update->execute()) {
                        $conn->query("DELETE FROM otp WHERE nomor = '$no_hp'");
                        session_unset();
                        session_destroy();
                        session_start();
                        $_SESSION['login_success'] = "Password berhasil diubah! Silakan login dengan password baru Anda.";
                        header("Location: masuk.php");
                        exit();
                    } else {
                        $error_message = "Gagal mengubah password.";
                        $step = 2;
                    }
                    $stmt_update->close();
                } else {
                    $error_message = "Kode OTP salah atau sudah kedaluwarsa.";
                    $step = 2;
                }
            } else {
                $error_message = "Terjadi kesalahan. Silakan ulangi dari awal.";
                $step = 1;
                session_destroy();
            }
            $stmt_check_otp->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desa Parit Banjar | Lupa Kata Sandi</title>
    <link rel="icon" href="img/logo-mempawah.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('img/halaman-depan.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }

        .form-container {
            max-width: 450px;
            width: 100%;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            animation: fadeInSlideUp 0.8s ease-out forwards;
        }

        @keyframes fadeInSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container h2 {
            color: #fff;
            font-weight: 600;
        }

        .form-container p {
            color: #e9ecef;
        }

        .form-container a {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }

        .form-container a:hover {
            text-decoration: underline;
        }

        .form-container label {
            color: #f8f9fa;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .icon {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 45px;
            display: flex !important;
            /* Perbaikan */
            align-items: center !important;
            /* Perbaikan */
            justify-content: center !important;
            /* Perbaikan */
            color: #6c757d;
            pointer-events: none;
        }

        .input-with-icon {
            padding-left: 45px !important;
        }

        .btn-primary {
            background-image: linear-gradient(to right, #007bff, #0056b3);
            border: none;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
            padding: 0.75rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <div class="text-center mb-4">
                <img src="img/logo-mempawah.png" alt="Logo Desa" width="70">
            </div>
            <h2 class="text-center mb-4">Lupa Kata Sandi</h2>

            <?php if (!empty($error_message))
                echo "<div class='alert alert-danger'>$error_message</div>"; ?>
            <?php if (!empty($info_message))
                echo "<div class='alert alert-info'>$info_message</div>"; ?>

            <?php if ($step == 1): ?>
                <p class="text-center mb-4">Masukkan nomor Handphone Anda yang terdaftar untuk menerima kode OTP.</p>
                <form action="lupakatasandi.php" method="POST">
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Nomor Handphone</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-phone"></i>
                            </span>
                            <input type="text" class="form-control" id="no_hp" name="no_hp"
                                placeholder="Contoh: 08123456789" required>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" name="kirim_otp" class="btn btn-primary">Kirim OTP</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="masuk.php">Kembali ke Login</a>
                </div>

            <?php elseif ($step == 2): ?>
                <p class="text-center mb-4">Masukkan OTP yang dikirim ke
                    <strong><?php echo htmlspecialchars($_SESSION['no_hp_reset']); ?></strong>.
                </p>
                <form action="lupakatasandi.php" method="POST">
                    <div class="mb-3">
                        <label for="otp" class="form-label">Kode OTP</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="text" class="form-control" id="otp" name="otp"
                                placeholder="Masukkan 6 digit kode OTP" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Kata Sandi Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password_baru" name="password_baru"
                                placeholder="Minimal 8 karakter" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordBaru">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password"
                                placeholder="Ulangi kata sandi baru" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleKonfirmasiPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="reset_password" class="btn btn-primary">Ubah Kata Sandi</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="lupakatasandi.php?action=reset">Kirim ulang OTP?</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mengubah visibilitas password
        function setupPasswordToggle(inputId, buttonId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = document.getElementById(buttonId);

            toggleButton.addEventListener('click', function () {
                // Ubah tipe input
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Ubah ikon mata
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }

        // Terapkan fungsi pada kedua input password
        setupPasswordToggle('password_baru', 'togglePasswordBaru');
        setupPasswordToggle('konfirmasi_password', 'toggleKonfirmasiPassword');
    </script>
</body>

</html>