<?php
session_start();
// Karena file ini ada di dalam folder back-end, path-nya menjadi 'config.php'
require_once 'config.php';

// Keamanan
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

header('Content-Type: application/json');

// Ambil timestamp terakhir yang diketahui oleh browser klien dari parameter URL
$last_known_timestamp = $_GET['timestamp'] ?? '1970-01-01 00:00:00';

// Cek timestamp terbaru di database dari kolom 'last_updated'
$latest_timestamp = $last_known_timestamp;
$result = $conn->query("SELECT MAX(last_updated) as latest FROM pengaduan");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Pastikan ada nilai sebelum menimpanya
    if ($row['latest']) {
        $latest_timestamp = $row['latest'];
    }
}

// Bandingkan timestamp
// Jika timestamp di DB lebih baru, kirim sinyal untuk refresh
$response = [
    'refresh' => strtotime($latest_timestamp) > strtotime($last_known_timestamp),
    'new_timestamp' => $latest_timestamp
];

echo json_encode($response);

$conn->close();
?>