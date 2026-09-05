<?php
// ============================================================
// KONFIGURASI DATABASE — Kompatibel untuk lokal MAUPUN Railway
//
// Kalau dijalankan di Railway, kredensial otomatis diambil dari
// Environment Variables yang disediakan plugin MySQL Railway
// (MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT).
//
// Kalau dijalankan di laptop sendiri (localhost), environment
// variable itu tidak ada, jadi otomatis pakai nilai default di
// bawah (punya XAMPP/Laragon biasa).
// ============================================================

$server        = getenv('MYSQLHOST') ?: 'localhost';
$user          = getenv('MYSQLUSER') ?: 'root';
$password      = getenv('MYSQLPASSWORD') ?: '';
$nama_database = getenv('MYSQLDATABASE') ?: 'db_sar_aceh';
$port          = getenv('MYSQLPORT') ?: 3306;

$db = mysqli_connect($server, $user, $password, $nama_database, $port);

if (!$db) {
    die("Gagal terhubung dengan database: " . mysqli_connect_error());
}

mysqli_set_charset($db, "utf8mb4");
?>
