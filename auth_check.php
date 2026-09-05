<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verifikasi ulang ke database bahwa akun ini masih ada.
// Mencegah sesi lama tetap "valid" kalau akun sudah dihapus.
if (!isset($db)) {
    include_once __DIR__ . "/config.php";
}

$stmt = mysqli_prepare($db, "SELECT id FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$akun_masih_ada = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$akun_masih_ada) {
    session_unset();
    session_destroy();
    header("Location: login.php?akun=hilang");
    exit;
}
?>