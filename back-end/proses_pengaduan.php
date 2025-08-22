<?php
session_start();
// Memanggil file koneksi database
require_once 'config.php';

// Mengatur header respons sebagai JSON untuk komunikasi dengan JavaScript
header('Content-Type: application/json');

// Fungsi untuk mengirim respons JSON terstruktur dan menghentikan eksekusi
function send_json_response($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// 1. VALIDASI KEAMANAN: Pastikan pengguna sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    send_json_response('error', 'Akses ditolak. Anda harus login terlebih dahulu.');
}

// 2. VALIDASI METODE REQUEST: Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Metode request tidak valid.');
}

// 3. AMBIL DATA DARI FORM (Menggunakan Null Coalescing Operator '??' untuk keamanan)
$user_id = $_SESSION['user_id'];
$nama_lengkap = trim($_POST['namaLengkap'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');
$dusun = trim($_POST['dusun'] ?? '');
$jenis_pengaduan = trim($_POST['jenisPengaduan'] ?? '');
$isi_pengaduan = trim($_POST['isiPengaduan'] ?? '');

// 4. VALIDASI INPUT: Pastikan field yang wajib diisi tidak kosong
if (empty($nama_lengkap) || empty($nik) || empty($telepon) || empty($dusun) || empty($jenis_pengaduan) || empty($isi_pengaduan)) {
    send_json_response('error', 'Semua kolom wajib diisi, kecuali lampiran.');
}

// 5. PROSES UPLOAD FILE LAMPIRAN (jika ada)
$lampiran_path = NULL; // Default nilai jika tidak ada lampiran
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/'; // Path absolut ke folder uploads

    // Pastikan direktori uploads ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    $file_name = basename($_FILES['lampiran']['name']);
    $file_tmp = $_FILES['lampiran']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validasi tipe file
    if (!in_array($file_ext, $allowed_types)) {
        send_json_response('error', 'Format file tidak diizinkan. Hanya .jpg, .png, .pdf.');
    }

    // Validasi ukuran file (5MB)
    if ($_FILES['lampiran']['size'] > 5 * 1024 * 1024) {
        send_json_response('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
    }

    // Buat nama file yang unik untuk menghindari penimpaan file
    $new_file_name = uniqid('lampiran_', true) . '.' . $file_ext;
    $target_file = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        // Simpan path relatif untuk disimpan di database
        $lampiran_path = 'uploads/' . $new_file_name;
    } else {
        send_json_response('error', 'Gagal memindahkan file lampiran.');
    }
}

// 6. SIMPAN DATA KE DATABASE (Menggunakan Prepared Statements untuk mencegah SQL Injection)
$sql = "INSERT INTO pengaduan (user_id, nama_lengkap, nik, telepon, dusun, jenis_pengaduan, isi_pengaduan, lampiran_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    // 'isssssss' -> i: integer, s: string
    mysqli_stmt_bind_param(
        $stmt,
        "isssssss",
        $user_id,
        $nama_lengkap,
        $nik,
        $telepon,
        $dusun,
        $jenis_pengaduan,
        $isi_pengaduan,
        $lampiran_path
    );

    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, kirim respons sukses
        send_json_response('success', 'Pengaduan Anda berhasil dikirim!');
    } else {
        // Jika gagal eksekusi query
        send_json_response('error', 'Gagal menyimpan data ke database: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
} else {
    // Jika statement gagal disiapkan
    send_json_response('error', 'Terjadi kesalahan pada persiapan database: ' . mysqli_error($conn));
}

// Tutup koneksi database
mysqli_close($conn);

?>