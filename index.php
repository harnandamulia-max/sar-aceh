<?php
include("auth_check.php");
include_once __DIR__ . "/functions.php";

$halaman_aktif = 'kalkulator';

// Nilai default form
$lat        = 5.5483;
$lng        = 95.3238;
$base_speed = 1;
$time_val   = 4;
$time_unit  = 'days';
$scale      = 25000;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <?php include __DIR__ . "/theme_init.php"; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Area Pencarian SAR - BASARNAS Aceh</title>
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
$judul_halaman = 'Kalkulator Area Pencarian';
$subjudul_halaman = 'Kantor Pencarian dan Pertolongan Aceh';
include("header.php");
include("nav.php");
?>

<div class="container">

    <?php if (isset($_GET['status']) && $_GET['status'] === 'invalid'): ?>
        <div class="alert alert-gagal">Input tidak valid. Pastikan kecepatan, lama waktu, dan skala lebih besar dari 0, serta koordinat berada dalam rentang yang benar (latitude -90 s/d 90, longitude -180 s/d 180).</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'gagal'): ?>
        <div class="alert alert-gagal">Gagal menyimpan data ke database. Silakan coba lagi.</div>
    <?php endif; ?>

    <form id="sarForm" method="POST" action="proses_simpan.php">
        <?php echo csrf_field(); ?>
        <div class="form-grid">
            <div class="form-section">
                <div class="form-section-title">
                    <div class="form-section-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h3>Titik &amp; Kecepatan</h3>
                        <span>Lokasi terakhir korban terlihat (LKP)</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="lat">LKP Latitude (Derajat)</label>
                    <div class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><path d="M3 6h18M3 18h18"/></svg>
                        <input type="number" step="any" id="lat" name="lat" value="<?php echo htmlspecialchars($lat); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lng">LKP Longitude (Derajat)</label>
                    <div class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="3" x2="12" y2="21"/><path d="M3 12c0-5 3.5-9 9-9s9 4 9 9-3.5 9-9 9-9-4-9-9z"/></svg>
                        <input type="number" step="any" id="lng" name="lng" value="<?php echo htmlspecialchars($lng); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="base_speed">Kecepatan korban (km/<span id="speedUnitLabel"><?php echo $time_unit === 'hours' ? 'jam' : 'hari'; ?></span>)</label>
                    <div class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                        <input type="number" step="any" id="base_speed" name="base_speed" value="<?php echo htmlspecialchars($base_speed); ?>" required>
                    </div>
                    <small class="hint">Asumsi jarak yang bisa ditempuh korban per satuan waktu di bawah.</small>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <div class="form-section-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
                    </div>
                    <div>
                        <h3>Waktu &amp; Skala Peta</h3>
                        <span>Durasi hilang dan skala peta cetak</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="time_val">Lama korban hilang</label>
                    <div class="field-pair">
                        <input type="number" step="any" id="time_val" name="time_val" value="<?php echo htmlspecialchars($time_val); ?>" required>
                        <select id="time_unit" name="time_unit" onchange="ubahSatuanKecepatan()">
                            <option value="days" <?php if ($time_unit == 'days') echo 'selected'; ?>>Hari</option>
                            <option value="hours" <?php if ($time_unit == 'hours') echo 'selected'; ?>>Jam</option>
                        </select>
                    </div>
                    <small class="hint">Dihitung sejak korban terakhir terlihat (LKP) sampai operasi SAR dimulai.</small>
                </div>
                <div class="form-group">
                    <label for="scale">Skala Peta (1 : ...)</label>
                    <div class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="8" rx="1"/><line x1="7" y1="8" x2="7" y2="12"/><line x1="11" y1="8" x2="11" y2="12"/><line x1="15" y1="8" x2="15" y2="12"/></svg>
                        <input type="number" id="scale" name="scale" value="<?php echo htmlspecialchars($scale); ?>" required>
                    </div>
                </div>

                <button type="submit">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="11" x2="8.01" y2="11"/><line x1="12" y1="11" x2="12.01" y2="11"/><line x1="16" y1="11" x2="16.01" y2="11"/><line x1="8" y1="15" x2="8.01" y2="15"/><line x1="12" y1="15" x2="12.01" y2="15"/></svg>
                    Hitung &amp; Simpan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function ubahSatuanKecepatan() {
    let unit = document.getElementById('time_unit').value;
    document.getElementById('speedUnitLabel').innerText = (unit === 'hours') ? 'jam' : 'hari';
}
</script>

</body>
</html>