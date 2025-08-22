<?php
session_start();
require 'back-end/config.php';

$signup_error = '';
$pesan_sukses = '';
$tahap_verifikasi = false;

// Blok 1: Logika saat tombol "Lanjutkan & Kirim OTP" diklik
if (isset($_POST['kirim_otp'])) {
    // Mengambil data dari form desain baru
    $namaLengkap = $_POST['namaLengkap'];
    $nik = $_POST['nik'];
    $nomorWa = $_POST['nomorWa'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $konfirmasiPassword = $_POST['konfirmasiPassword'];

    if ($password !== $konfirmasiPassword) {
        $signup_error = "Konfirmasi password tidak cocok.";
    } else {
        // Cek apakah NIK, email, atau nomor WA sudah terdaftar
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE nik = ? OR email = ? OR no_hp = ?");
        mysqli_stmt_bind_param($stmt_check, "sss", $nik, $email, $nomorWa);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            $signup_error = "NIK, Email atau Nomor WhatsApp sudah terdaftar.";
        } else {
            $otp = rand(100000, 999999);
            $waktu = time();

            // Simpan atau update OTP di database
            $stmt_otp = mysqli_prepare($conn, "INSERT INTO otp (nomor, otp, waktu) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE otp = ?, waktu = ?");
            mysqli_stmt_bind_param($stmt_otp, "sssss", $nomorWa, $otp, $waktu, $otp, $waktu);
            mysqli_stmt_execute($stmt_otp);

            // Simpan data registrasi sementara di session
            $_SESSION['registrasi_pending'] = [
                'namaLengkap' => $namaLengkap,
                'nik' => $nik,
                'email' => $email,
                'no_hp' => $nomorWa,
                'password' => $password // Simpan password asli sementara
            ];

            // Kirim OTP via Fonnte
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(['target' => $nomorWa, 'message' => "Kode Registrasi Akun Pelayanan Desa Parit Banjar Anda: $otp"]),
                CURLOPT_HTTPHEADER => ["Authorization: " . FONNTE_TOKEN],
            ]);
            curl_exec($curl);
            curl_close($curl);

            $pesan_sukses = "Kode OTP telah dikirim ke nomor WhatsApp Anda.";
            $tahap_verifikasi = true; // Pindah ke tahap verifikasi
        }
        mysqli_stmt_close($stmt_check);
    }
}
// Blok 2: Logika saat tombol "Verifikasi & Daftar" diklik
elseif (isset($_POST['verifikasi_otp'])) {
    $otp_input = $_POST['otp'];
    $data_pending = $_SESSION['registrasi_pending'];
    $tahap_verifikasi = true; // Tetap di tahap verifikasi jika ada error

    $stmt_verify = mysqli_prepare($conn, "SELECT * FROM otp WHERE nomor = ? AND otp = ?");
    mysqli_stmt_bind_param($stmt_verify, "ss", $data_pending['no_hp'], $otp_input);
    mysqli_stmt_execute($stmt_verify);
    $result_verify = mysqli_stmt_get_result($stmt_verify);

    if ($row = mysqli_fetch_assoc($result_verify)) {
        if (time() - $row['waktu'] <= 300) { // OTP valid selama 5 menit
            $hashed_password = password_hash($data_pending['password'], PASSWORD_DEFAULT);

            // Masukkan data ke tabel users
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (name, nik, email, no_hp, password, is_verified) VALUES (?, ?, ?, ?, ?, TRUE)");
            mysqli_stmt_bind_param($stmt_insert, "sssss", $data_pending['namaLengkap'], $data_pending['nik'], $data_pending['email'], $data_pending['no_hp'], $hashed_password);

            if (mysqli_stmt_execute($stmt_insert)) {
                unset($_SESSION['registrasi_pending']);
                mysqli_query($conn, "DELETE FROM otp WHERE nomor = '{$data_pending['no_hp']}'");
                $_SESSION['signup_success'] = "Pendaftaran berhasil! Silakan login.";
                header("Location: masuk.php"); // Ganti ke halaman login Anda
                exit;
            } else {
                $signup_error = "Gagal menyimpan data pendaftaran. Silakan coba lagi.";
            }
        } else {
            $signup_error = "Kode OTP sudah kedaluwarsa. Silakan minta OTP baru.";
        }
    } else {
        $signup_error = "Kode OTP yang Anda masukkan salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Desa Parit Banjar</title>
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
                    <h3 class="fw-bold"><?= $tahap_verifikasi ? 'Verifikasi Kode OTP' : 'Buat Akun Baru' ?></h3>
                    <p class="text-muted">
                        <?= $tahap_verifikasi ? 'Masukkan kode yang kami kirim ke WhatsApp Anda.' : 'Isi data di bawah untuk mendaftar.' ?>
                    </p>
                </div>

                <?php if ($signup_error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($signup_error) ?></div>
                <?php endif; ?>
                <?php if ($pesan_sukses): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
                <?php endif; ?>

                <form action="daftar.php" method="POST" id="daftarForm">
                    <div class="row g-3">
                        <?php if ($tahap_verifikasi): ?>
                            <div class="col-12">
                                <label for="otp" class="form-label">Kode OTP</label>
                                <input type="text" class="form-control" id="otp" name="otp" pattern="\d{6}"
                                    title="OTP harus 6 digit angka" maxlength="6" required autofocus>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" name="verifikasi_otp" class="btn btn-costum">Verifikasi &
                                    Daftar</button>
                            </div>
                        <?php else: ?>
                            <div class="col-12">
                                <label for="namaLengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="namaLengkap" name="namaLengkap" required
                                    value="<?= htmlspecialchars($_POST['namaLengkap'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label for="nik" class="form-label">NIK (Nomor Induk Kependudukan)</label>
                                <input type="text" class="form-control" id="nik" name="nik" pattern="\d{16}"
                                    title="NIK harus 16 digit angka" maxlength="16" required
                                    value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label for="nomorWa" class="form-label">Nomor WhatsApp Aktif</label>
                                <input type="tel" class="form-control" id="nomorWa" name="nomorWa"
                                    placeholder="Contoh: 081234567890" pattern="^08\d{8,11}$"
                                    title="Gunakan format 08xxxxxxxxxx" required
                                    value="<?= htmlspecialchars($_POST['nomorWa'] ?? '') ?>">
                                <div class="form-text">Pastikan nomor ini aktif di WhatsApp untuk menerima kode verifikasi.
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Alamat Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="konfirmasiPassword" class="form-label">Konfirmasi Kata Sandi</label>
                                <input type="password" class="form-control" id="konfirmasiPassword"
                                    name="konfirmasiPassword" required>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" name="kirim_otp" class="btn btn-costum">Lanjutkan & Kirim OTP</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <p class="mt-4 text-center text-muted">Sudah punya akun? <a href="masuk.php">Masuk di sini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // DIUBAH: Menyesuaikan dengan id form yang baru
        const daftarForm = document.getElementById('daftarForm');
        daftarForm.addEventListener('submit', function (event) {
            // Cek hanya jika sedang tidak di tahap verifikasi
            const isVerificationStage = document.getElementById('otp') !== null;
            if (!isVerificationStage) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('konfirmasiPassword').value;
                if (password !== confirmPassword) {
                    alert('Konfirmasi password tidak cocok!');
                    event.preventDefault(); // Mencegah form untuk submit
                }
            }
        });
    </script>
</body>

</html>