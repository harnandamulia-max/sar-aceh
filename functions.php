<?php
/*
    ===== RUMUS SAR (sesuai materi "MENENTUKAN AREA PENCARIAN") =====

    1. Possible Search Area
       r1 (km)   = kecepatan korban (km/hari) x jumlah hari hilang
       Luas1     = π x r1^2

    2. Probable Search Area (hasil analisa/survey = 1/8 dari Possible)
       Luas2     = Luas1 x 1/8   ->   r2 = r1 / √8

    3. Radius di atas peta (cm)
       radius_cm = (radius_km x 100.000) / skala peta

    4. Luas Area Pencarian Akhir (bentuk persegi mengelilingi Probable Search Area)
       sisi      = 2 x r2
       LuasAkhir = sisi x sisi
*/

function hitungSAR($lat, $lng, $base_speed, $jumlah_hari, $scale)
{
    $r1 = $base_speed * $jumlah_hari;
    $area1 = M_PI * pow($r1, 2);
    $radius1_cm = ($r1 * 100000) / $scale;

    $r2 = $r1 / sqrt(8);
    $area2 = $area1 / 8;
    $radius2_cm = ($r2 * 100000) / $scale;

    $sideBox = 2 * $r2;
    $finalArea = pow($sideBox, 2);

    $kmPerLat = 111.0;
    $kmPerLng = 111.0 * cos(deg2rad($lat));
    $halfSide = $sideBox / 2;

    $deltaLat = $halfSide / $kmPerLat;
    $deltaLng = $halfSide / $kmPerLng;

    return [
        'r1' => $r1,
        'area1' => $area1,
        'radius1_cm' => $radius1_cm,
        'r2' => $r2,
        'area2' => $area2,
        'radius2_cm' => $radius2_cm,
        'sideBox' => $sideBox,
        'finalArea' => $finalArea,
        'latMin' => $lat - $deltaLat,
        'latMax' => $lat + $deltaLat,
        'lngMin' => $lng - $deltaLng,
        'lngMax' => $lng + $deltaLng,
    ];
}

/*
    ===== KEAMANAN: PROTEKSI CSRF =====
    Dipakai di semua form yang mengubah data (login, register, hapus
    riwayat) supaya form tidak bisa dipicu dari situs lain.
*/

function csrf_token()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = $_POST['csrf_token'] ?? '';
    if ($token === '' || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Permintaan ditolak (token keamanan tidak cocok atau kedaluwarsa). Silakan kembali dan coba lagi.');
    }
}

/*
    ===== VALIDASI ANGKA INPUT KALKULATOR =====
    Menolak kecepatan/waktu/skala nol atau negatif (supaya tidak
    menghasilkan radius 0 atau perhitungan yang tidak masuk akal),
    dan menolak koordinat di luar rentang lat/lng yang valid.

    Sebelumnya fungsi ini didefinisikan tapi tidak pernah dipanggil --
    proses_simpan.php punya validasi manualnya sendiri yang terpisah
    (dan tidak memvalidasi koordinat sama sekali). Sekarang
    proses_simpan.php memanggil fungsi ini.
*/
function validasiInputKalkulator($lat, $lng, $base_speed, $time_val, $scale)
{
    if ($lat < -90 || $lat > 90) {
        return false;
    }
    if ($lng < -180 || $lng > 180) {
        return false;
    }
    return $base_speed > 0 && $time_val > 0 && $scale > 0;
}
?>