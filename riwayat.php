<?php
include("auth_check.php");
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/functions.php";
$halaman_aktif = 'riwayat';

// Hapus data (sekarang lewat POST + token CSRF, bukan link GET,
// dan dibatasi WHERE user_id = ... supaya user hanya bisa menghapus
// riwayatnya sendiri).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    csrf_verify();
    $id_hapus = (int) $_POST['hapus_id'];
    $stmt = mysqli_prepare($db, "DELETE FROM riwayat_pencarian WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id_hapus, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: riwayat.php");
    exit;
}

// ================== FILTER PERIODE ==================
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$dari   = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : '';

// Semua query di bawah selalu dibatasi ke user_id milik sesi yang
// sedang login -- riwayat tidak lagi bisa dilihat/dihapus lintas akun.
$whereSql = "WHERE user_id = ?";
$params = [$_SESSION['user_id']];
$types = "i";

switch ($filter) {
    case 'week':
        $whereSql .= " AND YEARWEEK(tanggal, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $whereSql .= " AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
        break;
    case 'year':
        $whereSql .= " AND YEAR(tanggal) = YEAR(CURDATE())";
        break;
    case 'custom':
        if ($dari && $sampai) {
            $whereSql .= " AND DATE(tanggal) BETWEEN ? AND ?";
            $params[] = $dari;
            $params[] = $sampai;
            $types .= "ss";
        }
        break;
    default:
        $filter = 'all';
}

// ================== HITUNG TOTAL UNTUK PAGINATION ==================
$countSql = "SELECT COUNT(*) AS total FROM riwayat_pencarian $whereSql";
$stmtCount = mysqli_prepare($db, $countSql);
mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$countResult = mysqli_stmt_get_result($stmtCount);
$totalData = (int) mysqli_fetch_assoc($countResult)['total'];
mysqli_stmt_close($stmtCount);

$perPage = 15;
$totalPages = max(1, (int) ceil($totalData / $perPage));
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// ================== AMBIL DATA HALAMAN INI ==================
$sql = "SELECT * FROM riwayat_pencarian $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($db, $sql);
$typesLimit = $types . "ii";
$paramsLimit = array_merge($params, [$perPage, $offset]);
mysqli_stmt_bind_param($stmt, $typesLimit, ...$paramsLimit);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$riwayat = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $riwayat[] = $row;
    }
}
mysqli_stmt_close($stmt);

// Label periode untuk judul cetak
$label_periode = [
    'all'    => 'Semua Data',
    'week'   => 'Minggu Ini',
    'month'  => 'Bulan Ini',
    'year'   => 'Tahun Ini',
    'custom' => ($dari && $sampai) ? "Periode $dari s/d $sampai" : 'Semua Data',
][$filter];

function tautanHalaman($p, $filter, $dari, $sampai)
{
    $qs = ['filter' => $filter, 'page' => $p];
    if ($filter === 'custom') {
        $qs['dari'] = $dari;
        $qs['sampai'] = $sampai;
    }
    return 'riwayat.php?' . http_build_query($qs);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <?php include __DIR__ . "/theme_init.php"; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pencarian SAR - BASARNAS Aceh</title>
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
$judul_halaman = 'Riwayat Pencarian';
$subjudul_halaman = 'Rekap data pencarian tersimpan';
include("header.php");
include("nav.php");
?>

<div class="container">

    <div class="top-actions">
        <div class="filter-bar" style="margin-bottom:0;">
            <a class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>" href="riwayat.php?filter=all">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Semua
            </a>
            <a class="filter-tab <?php echo $filter === 'week' ? 'active' : ''; ?>" href="riwayat.php?filter=week">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Minggu Ini
            </a>
            <a class="filter-tab <?php echo $filter === 'month' ? 'active' : ''; ?>" href="riwayat.php?filter=month">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><circle cx="8" cy="15" r="1.3" fill="currentColor" stroke="none"/><circle cx="12" cy="15" r="1.3" fill="currentColor" stroke="none"/></svg>
                Bulan Ini
            </a>
            <a class="filter-tab <?php echo $filter === 'year' ? 'active' : ''; ?>" href="riwayat.php?filter=year">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="13" y2="18"/></svg>
                Tahun Ini
            </a>
        </div>
        <button type="button" class="btn-print" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak
        </button>
    </div>

    <form method="GET" action="riwayat.php" class="date-range-card">
        <input type="hidden" name="filter" value="custom">
        <div class="form-group">
            <label for="dari">Dari</label>
            <input type="date" id="dari" name="dari" value="<?php echo htmlspecialchars($dari); ?>" required>
        </div>
        <div class="form-group">
            <label for="sampai">Sampai</label>
            <input type="date" id="sampai" name="sampai" value="<?php echo htmlspecialchars($sampai); ?>" required>
        </div>
        <button type="submit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Terapkan Rentang
        </button>
    </form>

    <div class="print-only">
        <strong>Riwayat Pencarian SAR - BASARNAS Aceh</strong><br>
        Periode: <?php echo htmlspecialchars($label_periode); ?><br>
        Dicetak pada: <?php echo date('d-m-Y H:i'); ?> WIB
    </div>

    <?php if (empty($riwayat)): ?>
        <div class="empty-msg">
            <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
            <span>Tidak ada data untuk periode ini.</span>
        </div>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Kecepatan (km/hari)</th>
                <th>Jumlah Hari</th>
                <th>Skala Peta</th>
                <th>Tanggal Disimpan</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($riwayat as $r): ?>
            <tr>
                <td data-label="ID Riwayat"><span class="id-badge">#<?php echo htmlspecialchars($r['id']); ?></span></td>
                <td data-label="Latitude" class="coord-mono"><?php echo htmlspecialchars($r['lkp_lat']); ?></td>
                <td data-label="Longitude" class="coord-mono"><?php echo htmlspecialchars($r['lkp_lng']); ?></td>
                <td data-label="Kecepatan (km/hari)"><?php echo htmlspecialchars($r['base_speed'] ?? '-'); ?></td>
                <td data-label="Jumlah Hari"><span class="day-chip"><?php echo htmlspecialchars($r['jumlah_hari']); ?> hari</span></td>
                <td data-label="Skala Peta">1:<?php echo htmlspecialchars($r['skala_peta']); ?></td>
                <td data-label="Tanggal Disimpan"><?php echo htmlspecialchars($r['tanggal']); ?></td>
                <td data-label="Aksi" class="cell-aksi">
                    <a class="small-btn view" href="hasil.php?id=<?php echo $r['id']; ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Lihat
                    </a>
                    <form method="POST" action="riwayat.php" style="display:inline-block;" onsubmit="return confirm('Hapus data ini?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="hapus_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" class="small-btn del" style="width:auto; display:inline-flex; margin-top:0;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <div class="top-actions" style="margin-top:14px;">
            <p class="data-count">
                Menampilkan <b><?php echo count($riwayat); ?></b> dari <b><?php echo $totalData; ?></b> data
                &nbsp;(halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>)
            </p>
            <div class="filter-bar print-hide" style="margin-bottom:0;">
                <?php if ($page > 1): ?>
                    <a class="filter-tab" href="<?php echo tautanHalaman($page - 1, $filter, $dari, $sampai); ?>">&laquo; Sebelumnya</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a class="filter-tab" href="<?php echo tautanHalaman($page + 1, $filter, $dari, $sampai); ?>">Berikutnya &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>