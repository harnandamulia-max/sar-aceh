<?php
include("auth_check.php");
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Form kalkulator sebelumnya tidak dilindungi CSRF sama sekali,
// padahal form login/register/hapus riwayat sudah. Sekarang
// disamakan dengan yang lain.
csrf_verify();

$lat        = isset($_POST['lat']) ? (float) $_POST['lat'] : 0;
$lng        = isset($_POST['lng']) ? (float) $_POST['lng'] : 0;
$base_speed = isset($_POST['base_speed']) ? (float) $_POST['base_speed'] : 1;
$time_val   = isset($_POST['time_val']) ? (float) $_POST['time_val'] : 1;
$time_unit  = isset($_POST['time_unit']) ? $_POST['time_unit'] : 'days';
$scale      = isset($_POST['scale']) ? (int) $_POST['scale'] : 25000;

// Validasi terpusat (lihat functions.php): kecepatan/waktu/skala harus
// lebih besar dari 0, dan koordinat harus berada dalam rentang yang valid.
if (!validasiInputKalkulator($lat, $lng, $base_speed, $time_val, $scale)) {
    header("Location: index.php?status=invalid");
    exit;
}

// Kolom di database (base_speed, jumlah_hari) selalu disimpan dalam
// satuan per HARI. Kalau user memasukkan dalam satuan JAM, kedua
// nilai (waktu maupun kecepatan) harus ikut dikonversi ke hari --
// sebelumnya hanya jumlah_hari yang dikonversi sehingga hasil
// perhitungan salah saat satuan "Jam" dipilih.
if ($time_unit === 'hours') {
    $jumlah_hari         = $time_val / 24;
    $base_speed_per_hari = $base_speed * 24; // km/jam -> km/hari
} else {
    $jumlah_hari         = $time_val;
    $base_speed_per_hari = $base_speed;
}

$sql = "INSERT INTO riwayat_pencarian (user_id, lkp_lat, lkp_lng, base_speed, jumlah_hari, skala_peta)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $sql);

if ($stmt) {
    // PENTING: jumlah_hari harus di-bind sebagai "d" (double), bukan "i".
    // Sebelumnya pakai "i" di sini, jadi mysqli membulatkan jumlah_hari
    // ke integer SEBELUM disimpan -- akibatnya kalau satuan waktu "Jam"
    // dipilih dan totalnya kurang dari 24 jam (jumlah_hari < 1), nilainya
    // selalu tersimpan sebagai 0. Itu membuat r1 = 0 dan seluruh area
    // pencarian ikut jadi 0.
    mysqli_stmt_bind_param(
        $stmt,
        "idddd" . "i",
        $_SESSION['user_id'],
        $lat,
        $lng,
        $base_speed_per_hari,
        $jumlah_hari,
        $scale
    );
    if (mysqli_stmt_execute($stmt)) {
        $id = mysqli_insert_id($db);
        mysqli_stmt_close($stmt);
        header("Location: hasil.php?id=" . $id . "&status=sukses");
        exit;
    } else {
        mysqli_stmt_close($stmt);
        header("Location: index.php?status=gagal");
        exit;
    }
} else {
    header("Location: index.php?status=gagal");
    exit;
}
?>