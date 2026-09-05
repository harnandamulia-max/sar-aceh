<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/functions.php";

// Kalau sudah login, langsung ke kalkulator
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$sukses_daftar = isset($_GET['daftar']) && $_GET['daftar'] === 'sukses';
$akun_hilang   = isset($_GET['akun']) && $_GET['akun'] === 'hilang';

const MAX_PERCOBAAN = 5;
const DURASI_KUNCI_MENIT = 15;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $identitas = trim($_POST['identitas'] ?? '');
    $password  = $_POST['password'] ?? '';

    if ($identitas === '' || $password === '') {
        $error = "Username/email dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($db, "SELECT id, nama, username, password, failed_attempts, locked_until FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $identitas, $identitas);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $terkunci = $user && $user['locked_until'] && strtotime($user['locked_until']) > time();

        if ($terkunci) {
            $jam_buka = date('H:i', strtotime($user['locked_until']));
            $error = "Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi setelah $jam_buka WIB.";
        } elseif ($user && password_verify($password, $user['password'])) {
            // Login sukses: reset counter percobaan gagal
            $reset = mysqli_prepare($db, "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
            mysqli_stmt_bind_param($reset, "i", $user['id']);
            mysqli_stmt_execute($reset);
            mysqli_stmt_close($reset);

            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Username/email atau password salah.";

            if ($user) {
                $percobaan = (int) $user['failed_attempts'] + 1;
                if ($percobaan >= MAX_PERCOBAAN) {
                    $locked_until = date('Y-m-d H:i:s', time() + DURASI_KUNCI_MENIT * 60);
                    $upd = mysqli_prepare($db, "UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
                    mysqli_stmt_bind_param($upd, "isi", $percobaan, $locked_until, $user['id']);
                    $error = "Terlalu banyak percobaan gagal. Akun dikunci selama " . DURASI_KUNCI_MENIT . " menit.";
                } else {
                    $upd = mysqli_prepare($db, "UPDATE users SET failed_attempts = ? WHERE id = ?");
                    mysqli_stmt_bind_param($upd, "ii", $percobaan, $user['id']);
                }
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            }
            // Kalau user tidak ditemukan, jangan bocorkan info itu -- pesan error tetap generik.
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <?php include __DIR__ . "/theme_init.php"; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Kalkulator Area Pencarian SAR BASARNAS Aceh</title>
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-page">

<?php $theme_toggle_variant = 'floating'; include __DIR__ . "/theme_toggle.php"; ?>

<div class="auth-wrap">
    <?php include("auth_visual.php"); ?>

    <div class="auth-card">
        <h1>Selamat Datang</h1>
        <p class="auth-desc">Masuk untuk mengakses Kalkulator Area Pencarian SAR.</p>

        <?php if ($sukses_daftar): ?>
            <div class="alert alert-sukses">Pendaftaran berhasil! Silakan masuk.</div>
        <?php endif; ?>
        <?php if ($akun_hilang): ?>
            <div class="alert alert-gagal">Sesi Anda tidak valid lagi. Silakan masuk kembali.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-gagal"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="identitas">Username atau Email</label>
                <input type="text" id="identitas" name="identitas" value="<?php echo htmlspecialchars($_POST['identitas'] ?? ''); ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Masuk</button>
        </form>

        <div class="auth-switch">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</div>

</body>
</html>