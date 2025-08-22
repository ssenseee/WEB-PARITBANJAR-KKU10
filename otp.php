<?php
session_start();
require 'back-end/config.php';

$pesan_error = '';
$pesan_sukses = '';
$nomor_telepon = '';

if (isset($_POST['submit-otp'])) {
    $nomor_telepon = $_POST['nomor'];

    if (is_numeric($nomor_telepon) && strlen($nomor_telepon) > 9) {
        $otp = rand(100000, 999999);
        $waktu = time();

        $stmt = mysqli_prepare($conn, "INSERT INTO otp (nomor, otp, waktu) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE otp = ?, waktu = ?");
        mysqli_stmt_bind_param($stmt, "sssss", $nomor_telepon, $otp, $waktu, $otp, $waktu);

        if (mysqli_stmt_execute($stmt)) {
            // Kirim OTP via Fonnte
            $curl = curl_init();
            $data = [
                'target' => $nomor_telepon,
                'message' => "Kode OTP Anda: " . $otp . "\nJangan berikan kepada siapapun.",
                'countryCode' => '+62'
            ];

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: " . FONNTE_TOKEN
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            $pesan_sukses = "OTP telah dikirim ke nomor " . htmlspecialchars($nomor_telepon);
        } else {
            $pesan_error = "Gagal memproses permintaan di database.";
        }
        mysqli_stmt_close($stmt);

    } else {
        $pesan_error = "Format nomor telepon tidak valid.";
    }
} elseif (isset($_POST['submit-login'])) {
    $otp_input = $_POST['otp'];
    $nomor_telepon = $_POST['nomor'];

    $stmt = mysqli_prepare($conn, "SELECT otp, waktu FROM otp WHERE nomor = ?");
    mysqli_stmt_bind_param($stmt, "s", $nomor_telepon);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $data_otp = mysqli_fetch_assoc($result);

        if ($otp_input == $data_otp['otp']) {
            // Cek apakah OTP kedaluwarsa (misal: 5 menit atau 300 detik)
            if (time() - $data_otp['waktu'] <= 300) {
                $pesan_sukses = "Login Berhasil!";
            } else {
                $pesan_error = "Kode OTP sudah kedaluwarsa. Silakan minta ulang.";
            }
        } else {
            $pesan_error = "Kode OTP yang Anda masukkan salah.";
        }
    } else {
        $pesan_error = "Nomor telepon tidak ditemukan atau belum pernah meminta OTP.";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login via OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        .form-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-request {
            background-color: #ff9800;
        }

        .btn-login {
            background-color: #17a2b8;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Verifikasi OTP</h1>

        <?php if ($pesan_sukses): ?>
            <div class="message success"><?php echo htmlspecialchars($pesan_sukses); ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="message error"><?php echo htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php if (empty($pesan_sukses) || !empty($pesan_error)): // Sembunyikan form jika sudah berhasil login ?>
                <div class="form-group">
                    <label for="nomor">Nomor WhatsApp</label>
                    <input type="text" id="nomor" name="nomor" placeholder="Contoh: 6281234567890"
                        value="<?php echo htmlspecialchars($nomor_telepon); ?>" <?php if (isset($_POST['submit-otp']))
                               echo 'readonly'; ?> required>
                </div>

                <?php if (isset($_POST['submit-otp'])): ?>
                    <div class="form-group">
                        <label for="otp">Kode OTP</label>
                        <input type="text" id="otp" name="otp" placeholder="Masukkan 6 digit OTP" required autofocus>
                    </div>
                    <button type="submit" name="submit-login" class="btn btn-login">Login</button>
                <?php else: ?>
                    <button type="submit" name="submit-otp" class="btn btn-request">Kirim OTP</button>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>