<?php
date_default_timezone_set('Asia/Jakarta');

$servername = "localhost";
$username = "root";
$password = "";
$database = "parit-banjar";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

define('FONNTE_TOKEN', 'f6KMqJfw7qUHCo8Rgy48');
?>