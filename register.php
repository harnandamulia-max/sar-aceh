<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/functions.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || $username === '' || $email === '' || $password === '') {
        $error = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Kata sandi minimal 6 karakter.";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi kata sandi tidak sama.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($db, "INSERT INTO users (nama, username, email, password) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $email, $hash);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: login.php?daftar=sukses");
            exit;
        } else {
            $error = (mysqli_errno($db) === 1062)
                ? "Username atau email sudah terdaftar. Silakan pakai yang lain."
                : "Gagal mendaftar: " . mysqli_error($db);
            mysqli_stmt_close($stmt);
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
    <title>Daftar Akun - Kalkulator Area Pencarian SAR BASARNAS Aceh</title>
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
        <h1>Buat Akun Baru</h1>
        <p class="auth-desc">Daftar untuk mulai menggunakan Kalkulator Area Pencarian SAR.</p>

        <?php if ($error): ?>
            <div class="alert alert-gagal"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small class="hint">Minimal 6 karakter.</small>
            </div>
            <div class="form-group">
                <label for="konfirmasi">Konfirmasi Kata Sandi</label>
                <input type="password" id="konfirmasi" name="konfirmasi" required minlength="6">
            </div>
            <button type="submit">Daftar</button>
        </form>

        <div class="auth-switch">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>
</div>

</body>
</html>