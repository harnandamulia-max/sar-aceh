<?php
// ==========================================================================
// KONFIGURASI DATABASE
// Otomatis pakai environment variable Railway jika tersedia,
// kalau tidak (misal saat dev lokal di XAMPP), pakai default localhost.
// ==========================================================================

$server       = getenv('MYSQLHOST') ?: 'localhost';
$user         = getenv('MYSQLUSER') ?: 'root';
$password     = getenv('MYSQLPASSWORD') ?: '';
$nama_database = getenv('MYSQLDATABASE') ?: 'db_sar_aceh';
$port         = getenv('MYSQLPORT') ?: 3306;

$db = mysqli_connect($server, $user, $password, $nama_database, $port);

if (!$db) {
    die("Gagal terhubung dengan database: " . mysqli_connect_error());
}

mysqli_set_charset($db, "utf8mb4");
?>