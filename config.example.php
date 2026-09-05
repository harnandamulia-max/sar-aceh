<?php
// ============================================================
// TEMPLATE KONFIGURASI DATABASE
// Salin file ini menjadi "config.php", lalu sesuaikan nilainya
// dengan kredensial database Anda. config.php TIDAK akan
// ikut ter-commit ke Git (lihat .gitignore).
// ============================================================

$server = "localhost";
$user = "root";
$password = ""; // Sesuaikan password database Anda jika ada
$nama_database = "db_sar_aceh";

$db = mysqli_connect($server, $user, $password, $nama_database);

if( !$db ){
    die("Gagal terhubung dengan database: " . mysqli_connect_error());
}

mysqli_set_charset($db, "utf8mb4");
?>
