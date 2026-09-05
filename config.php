<?php
// Kalau Railway (atau hosting lain) menyediakan environment variable
// database, pakai itu. Kalau tidak ada (misal di XAMPP lokal kamu),
// jatuh ke nilai default seperti biasa. Jadi file ini tetap jalan
// baik di lokal maupun setelah dideploy, tanpa perlu diubah manual.

$server        = getenv('MYSQLHOST') ?: "localhost";
$user          = getenv('MYSQLUSER') ?: "root";
$password      = getenv('MYSQLPASSWORD') ?: ""; // Sesuaikan password database Anda jika ada
$nama_database = getenv('MYSQLDATABASE') ?: "db_sar_aceh";
$port          = getenv('MYSQLPORT') ?: 3306;

$db = mysqli_connect($server, $user, $password, $nama_database, (int) $port);

if (!$db) {
    die("Gagal terhubung dengan database: " . mysqli_connect_error());
}

mysqli_set_charset($db, "utf8mb4");
?>