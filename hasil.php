<?php
include("auth_check.php");
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/functions.php";
$halaman_aktif = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$data = null;

if ($id > 0) {
    // Hanya boleh melihat riwayat milik akun sendiri (cegah IDOR: user
    // A tidak bisa melihat hasil perhitungan user B lewat tebak-tebak id).
    $stmt = mysqli_prepare($db, "SELECT * FROM riwayat_pencarian WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <?php include __DIR__ . "/theme_init.php"; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Perhitungan SAR - BASARNAS Aceh</title>
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

<?php
$judul_halaman = 'Hasil Perhitungan';
$subjudul_halaman = 'Detail area pencarian & peta lokasi';
include("header.php");
include("nav.php");
?>

<div class="container">

    <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
        <div class="alert alert-sukses">Sukses menyimpan data ke database!</div>
    <?php endif; ?>

    <?php if (!$data): ?>
        <div class="empty-msg">
            <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            <span>Data tidak ditemukan.</span>
            <a href="index.php" class="btn" style="max-width:220px; margin:6px auto 0;">Hitung Ulang</a>
        </div>
    <?php else: ?>
        <?php
        $lat = (float) $data['lkp_lat'];
        $lng = (float) $data['lkp_lng'];
        $base_speed = (float) ($data['base_speed'] ?? 1);
        $jumlah_hari = (float) $data['jumlah_hari'];
        $scale = (int) $data['skala_peta'];

        $h = hitungSAR($lat, $lng, $base_speed, $jumlah_hari, $scale);
        ?>

        <div class="results">
            <h3>Hasil Analisis &amp; Perhitungan SAR</h3>

            <div class="results-lkp">
                <div class="lkp-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="lkp-text">
                    <div class="lkp-label">Titik LKP (Last Known Position)</div>
                    <div class="lkp-coord"><?php echo number_format($lat, 5); ?>, <?php echo number_format($lng, 5); ?></div>
                </div>
                <div class="lkp-meta">
                    <span>Waktu Berlalu: <b><?php echo number_format($jumlah_hari, 4); ?> hari</b></span>
                    <span>Kecepatan Korban: <b><?php echo number_format($base_speed, 4); ?> km/hari</b></span>
                    <span>Skala Peta: <b>1:<?php echo $scale; ?></b></span>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card accent-possible">
                    <div class="stat-card-head">
                        <div class="stat-card-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/></svg>
                        </div>
                        <div class="stat-card-title">Possible Search Area</div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($h['area1'], 2); ?> km²</div>
                    <div class="stat-card-formula">
                        r1 = <code><?php echo number_format($h['r1'], 2); ?> km</code> &nbsp;·&nbsp; Luas = π × r1²<br>
                        Radius di peta = <code><?php echo number_format($h['radius1_cm'], 2); ?> cm</code>
                    </div>
                </div>

                <div class="stat-card accent-probable">
                    <div class="stat-card-head">
                        <div class="stat-card-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4" fill="currentColor" stroke="none"/></svg>
                        </div>
                        <div class="stat-card-title">Probable Search Area</div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($h['area2'], 2); ?> km²</div>
                    <div class="stat-card-formula">
                        r2 = r1/√8 = <code><?php echo number_format($h['r2'], 2); ?> km</code> &nbsp;·&nbsp; Luas = Luas1 × 1/8<br>
                        Radius di peta = <code><?php echo number_format($h['radius2_cm'], 2); ?> cm</code>
                    </div>
                </div>

                <div class="stat-card accent-final">
                    <div class="stat-card-head">
                        <div class="stat-card-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
                        </div>
                        <div class="stat-card-title">Luas Area Akhir (Persegi)</div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($h['finalArea'], 2); ?> km²</div>
                    <div class="stat-card-formula">
                        Sisi = 2 × r2 = <code><?php echo number_format($h['sideBox'], 2); ?> km</code>
                    </div>
                </div>
            </div>

            <h4>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 3v18"/></svg>
                Titik Koordinat Kotak Batas Area (A, B, C, D)
            </h4>
            <div class="coord-compass">
                <div class="coord-pin">
                    <div class="coord-pin-badge">NW</div>
                    <div>
                        <div class="coord-pin-label">North-West</div>
                        <div class="coord-pin-value"><?php echo number_format($h['latMax'], 5); ?>, <?php echo number_format($h['lngMin'], 5); ?></div>
                    </div>
                </div>
                <div class="coord-pin">
                    <div class="coord-pin-badge">NE</div>
                    <div>
                        <div class="coord-pin-label">North-East</div>
                        <div class="coord-pin-value"><?php echo number_format($h['latMax'], 5); ?>, <?php echo number_format($h['lngMax'], 5); ?></div>
                    </div>
                </div>
                <div class="coord-pin">
                    <div class="coord-pin-badge">SW</div>
                    <div>
                        <div class="coord-pin-label">South-West</div>
                        <div class="coord-pin-value"><?php echo number_format($h['latMin'], 5); ?>, <?php echo number_format($h['lngMin'], 5); ?></div>
                    </div>
                </div>
                <div class="coord-pin">
                    <div class="coord-pin-badge">SE</div>
                    <div>
                        <div class="coord-pin-label">South-East</div>
                        <div class="coord-pin-value"><?php echo number_format($h['latMin'], 5); ?>, <?php echo number_format($h['lngMax'], 5); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="map"></div>

        <a href="index.php" class="btn" style="margin-top:20px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            Hitung Lagi
        </a>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            var lat = <?php echo $lat; ?>;
            var lng = <?php echo $lng; ?>;
            var r1Meter = <?php echo $h['r1'] * 1000; ?>;
            var r2Meter = <?php echo $h['r2'] * 1000; ?>;
            var latMin = <?php echo $h['latMin']; ?>;
            var latMax = <?php echo $h['latMax']; ?>;
            var lngMin = <?php echo $h['lngMin']; ?>;
            var lngMax = <?php echo $h['lngMax']; ?>;

            var map = L.map('map').setView([lat, lng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Titik LKP (Last Known Position)
            L.marker([lat, lng]).addTo(map).bindPopup('<b>LKP</b><br>Titik terakhir korban terlihat').openPopup();

            // Possible Search Area (lingkaran luar)
            L.circle([lat, lng], {
                radius: r1Meter,
                color: '#337ab7',
                weight: 2,
                fill: false
            }).addTo(map).bindPopup('Possible Search Area (r1 = ' + (r1Meter/1000).toFixed(2) + ' km)');

            // Probable Search Area (lingkaran dalam)
            L.circle([lat, lng], {
                radius: r2Meter,
                color: '#d9534f',
                weight: 2,
                fillColor: '#d9534f',
                fillOpacity: 0.15
            }).addTo(map).bindPopup('Probable Search Area (r2 = ' + (r2Meter/1000).toFixed(2) + ' km)');

            // Kotak batas area pencarian akhir (persegi)
            var bounds = [[latMin, lngMin], [latMax, lngMax]];
            L.rectangle(bounds, {
                color: '#5cb85c',
                weight: 2,
                fill: false,
                dashArray: '6, 6'
            }).addTo(map).bindPopup('Luas Area Akhir (Persegi)');

            // Zoom otomatis agar seluruh area (termasuk kotak) terlihat
            map.fitBounds(bounds.concat([[lat, lng]]), { padding: [30, 30] });
        </script>
    <?php endif; ?>
</div>

</body>
</html>