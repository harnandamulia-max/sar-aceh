<?php
$halaman_aktif = isset($halaman_aktif) ? $halaman_aktif : '';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="topbar">
    <div class="topbar-inner">
        <a href="index.php" class="brand">
            <img class="brand-emblem" src="logo-basarnas.png" alt="" width="29" height="36">
            <span class="brand-text">
                <strong>BASARNAS Aceh</strong>
                <small>Kalkulator Area Pencarian SAR</small>
            </span>
        </a>

        <div class="topbar-right">
        <?php include __DIR__ . "/theme_toggle.php"; ?>

        <button type="button" class="nav-toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="mainNav">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <a href="index.php" class="nav-link <?php echo $halaman_aktif === 'kalkulator' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                    <line x1="8" y1="6" x2="16" y2="6"/>
                    <line x1="8" y1="11" x2="8.01" y2="11"/>
                    <line x1="12" y1="11" x2="12.01" y2="11"/>
                    <line x1="16" y1="11" x2="16.01" y2="11"/>
                    <line x1="8" y1="15" x2="8.01" y2="15"/>
                    <line x1="12" y1="15" x2="12.01" y2="15"/>
                    <line x1="16" y1="15" x2="16.01" y2="15"/>
                </svg>
                Kalkulator
            </a>
            <a href="riwayat.php" class="nav-link <?php echo $halaman_aktif === 'riwayat' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <polyline points="12 7 12 12 15 15"/>
                </svg>
                Riwayat Pencarian
            </a>
            <?php if (isset($_SESSION['nama'])): ?>
            <a href="logout.php" class="nav-link" onclick="return confirm('Keluar dari akun?');" style="opacity:.9;">
                <svg class="nav-icon" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Keluar (<?php echo htmlspecialchars($_SESSION['nama']); ?>)
            </a>
            <?php endif; ?>
        </nav>
        </div>
    </div>
</header>

<script>
(function () {
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
        var isOpen = nav.classList.toggle('open');
        toggle.classList.toggle('active', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            nav.classList.remove('open');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
})();
</script>