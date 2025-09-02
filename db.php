<?php
// Konfigurasi database
$host = "localhost";      // biasanya localhost kalau pakai XAMPP
$user = "root";           // default user XAMPP
$pass = "";               // default password XAMPP kosong
$db   = "daily_trading";  // nama database yang sudah kita buat

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
